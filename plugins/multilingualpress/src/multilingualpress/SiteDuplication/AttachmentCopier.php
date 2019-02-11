<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\SiteDuplication;

use Inpsyde\MultilingualPress\Framework\BasePathAdapter;
use Inpsyde\MultilingualPress\Framework\Database\TableStringReplacer;

class AttachmentCopier
{
    const FILTER_COPY_PATHS = 'multilingualpress.attachments_to_copy_paths';

    /**
     * @var BasePathAdapter
     */
    private $basePathAdapter;

    /**
     * @var bool
     */
    private $filesCopied;

    /**
     * @var \wpdb
     */
    private $wpdb;

    /**
     * @var TableStringReplacer
     */
    private $tableStringReplacer;

    /**
     * @param \wpdb $wpdb
     * @param BasePathAdapter $basePathAdapter
     * @param TableStringReplacer $tableStringReplacer
     */
    public function __construct(
        \wpdb $wpdb,
        BasePathAdapter $basePathAdapter,
        TableStringReplacer $tableStringReplacer
    ) {

        $this->wpdb = $wpdb;
        $this->basePathAdapter = $basePathAdapter;
        $this->tableStringReplacer = $tableStringReplacer;
    }

    /**
     * Copies all attachment files of the site with given ID to the current site.
     *
     * @param int $sourceSiteId
     * @return bool
     */
    public function copyAttachments(int $sourceSiteId): bool
    {
        switch_to_blog($sourceSiteId);
        $sourceDir = trailingslashit($this->basePathAdapter->basedir());
        $sourceUrl = $this->basePathAdapter->baseurl();
        restore_current_blog();

        if (!(is_dir($sourceDir) && is_readable($sourceDir))) {
            return false;
        }

        $destinationDir = trailingslashit($this->basePathAdapter->basedir());

        $attachmentPaths = apply_filters(
            self::FILTER_COPY_PATHS,
            $this->attachmentPaths(),
            $sourceSiteId,
            $sourceDir,
            $destinationDir
        );

        if (!$attachmentPaths || !is_array($attachmentPaths)) {
            return false;
        }

        $this->filesCopied = false;

        foreach ($attachmentPaths as $dir => $attachmentFiles) {
            if (is_string($dir) && is_array($attachmentFiles)) {
                $this->copyDir($sourceDir . $dir, $attachmentFiles, $destinationDir . $dir);
            }
        }

        if ($this->filesCopied) {
            $this->updateAttachmentUrls($sourceUrl, $this->basePathAdapter->baseurl());
        }

        return $this->filesCopied;
    }

    /**
     * Extracts all registered attachment paths from the database as an array with directories
     * relative to uploads as keys, and arrays of file paths as values.
     *
     * Only files referenced in the database are trustworthy, and will therefore get copied.
     *
     * @return string[]
     */
    private function attachmentPaths(): array
    {
        $sql = "SELECT meta_value FROM {$this->wpdb->postmeta} WHERE meta_key = %s";
        /** @var \stdClass[] $metadata */
        $metadata = $this->wpdb->get_results($this->wpdb->prepare($sql, '_wp_attachment_metadata'));

        if (!$metadata) {
            return [];
        }

        $paths = [];
        foreach ($metadata as $metadataValue) {
            list($dir, $files) = $this->attachmentPathFiles($metadataValue);
            if ($dir && $files) {
                array_key_exists($dir, $paths)
                    ? $paths[$dir] = array_merge($paths[$dir], $files)
                    : $paths[$dir] = $files;
            }
        }

        return $paths;
    }

    /**
     * @param \stdClass $metadata
     * @return array
     */
    private function attachmentPathFiles(\stdClass $metadata): array
    {
        $metaValue = maybe_unserialize($metadata->meta_value);
        $file = is_array($metaValue) ? ($metaValue['file'] ?? '') : '';
        $dir = $file ? dirname($file) : null;
        if (!$dir) {
            return [null, null];
        }

        $sizes = $metaValue['sizes'] ?? [];
        $files = $sizes && is_array($sizes) ? array_column($sizes, 'file') : [];
        array_unshift($files, basename($file));

        return [$dir, array_filter($files)];
    }

    /**
     * Copies all given files from one site to another.
     *
     * @param string $sourceDir
     * @param array $filepaths
     * @param string $destinationDir
     */
    private function copyDir(string $sourceDir, array $filepaths, string $destinationDir)
    {
        if (!is_dir($sourceDir) || (!is_dir($destinationDir) && !wp_mkdir_p($destinationDir))) {
            return;
        }

        foreach ($filepaths as $filepath) {
            $source = trailingslashit($sourceDir) . $filepath;
            $destination = trailingslashit($destinationDir) . $filepath;

            if (file_exists($source) && !file_exists($destination) && copy($source, $destination)) {
                $this->filesCopied = true;
            }
        }
    }

    /**
     * Updates attachment URLs according to the given arguments.
     *
     * @param string $sourceUrl
     * @param string $destinationUrl
     */
    private function updateAttachmentUrls(string $sourceUrl, string $destinationUrl)
    {
        $tables = [
            $this->wpdb->comments => [
                'comment_content',
            ],
            $this->wpdb->posts => [
                'guid',
                'post_content',
                'post_content_filtered',
                'post_excerpt',
            ],
            $this->wpdb->term_taxonomy => [
                'description',
            ],
        ];

        foreach ($tables as $table => $columns) {
            $this->tableStringReplacer->replace($table, $columns, $sourceUrl, $destinationUrl);
        }
    }
}
