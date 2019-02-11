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

namespace Inpsyde\MultilingualPress\Core\Admin\LanguageManager;

use Inpsyde\MultilingualPress\Framework\Database\Table;
use Inpsyde\MultilingualPress\Framework\Factory\LanguageFactory;
use Inpsyde\MultilingualPress\Framework\Language\Language;
use function Inpsyde\MultilingualPress\settingsErrors;

/**
 * MultilingualPress Language Manager Updater
 */
class Updater
{
    /**
     * @var Db
     */
    private $db;

    /**
     * @var Table
     */
    private $table;

    private $manager;

    private $languageFactory;

    /**
     * Updater constructor.
     * @param Db $db
     * @param Table $table
     */
    public function __construct(
        Db $db,
        Table $table,
        LanguageFactory $languageFactory,
        LanguageInstaller $manager
    ) {

        $this->db = $db;
        $this->table = $table;
        $this->languageFactory = $languageFactory;
        $this->manager = $manager;
    }

    /**
     * @param array $languages
     * @return bool
     */
    public function updateLanguages(array $languages): bool
    {
        $this->excludeEmptyLanguageData($languages);
        $this->excludeMalformedLanguageData($languages);
        $this->ensureKeys($languages);

        if (!$languages) {
            return false;
        }

        $this->splitLanguages($languages);

        $errors = array_merge(
            $this->db->create($languages['toInsert']),
            $this->db->update($languages['toUpdate']),
            $this->db->delete($languages['toDelete'])
        );

        settingsErrors($errors, 'language-manager', 'error');

        !$errors and $this->installLanguages($languages['toInsert'] + $languages['toUpdate']);

        return true;
    }

    /**
     * @param array $languages
     */
    // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
    // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
    // phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
    private function splitLanguages(array &$languages)
    {
        $splittedLanguages = [
            'toInsert' => [],
            'toUpdate' => [],
            'toDelete' => [],
        ];
        $indexes = array_map(function ($item) {
            return $item->id();
        }, $this->db->read());
        // phpcs:enable

        foreach ($indexes as $index) {
            $isNegative = array_key_exists(0 - $index, $languages);
            $index = $isNegative ? 0 - $index : $index;
            $current = $languages[$index];

            unset($languages[$index]);

            if ($isNegative) {
                $splittedLanguages['toDelete'][absint($index)] = $current;
                continue;
            }

            $splittedLanguages['toUpdate'][$index] = $current;
        }

        $splittedLanguages['toInsert'] = $languages;

        $languages = $splittedLanguages;
    }

    /**
     * @param array $languages
     */
    // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
    private function excludeEmptyLanguageData(array &$languages)
    {
        // phpcs:enable
        foreach ($languages as &$language) {
            if (!array_filter($language)) {
                $language = [];
            }
        }

        $languages = array_filter($languages);
    }

    /**
     * @param array $languages
     */
    // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
    private function excludeMalformedLanguageData(array &$languages)
    {
        // phpcs:enable
        foreach ($languages as &$language) {
            if (!$language['native_name']
                || !$language['english_name']
                || !$language['locale']
            ) {
                $language = [];
            }
        }

        $languages = array_filter($languages);
    }

    /**
     * @param array $languages
     */
    // phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
    private function ensureKeys(array &$languages)
    {
        // phpcs enable
        $tableColumns = $this->table->schema();
        unset(
            $tableColumns['ID'],
            $tableColumns['custom_name']
        );

        foreach ($languages as $key => $language) {
            if (array_diff(array_keys($language), array_keys($tableColumns))) {
                $languages = [];
                break;
            }
        }

        $languages = array_filter($languages);
    }

    /**
     * @param array $languages
     * @return array
     */
    private function installLanguages(array $languages): array
    {
        $response = [];

        foreach ($languages as $language) {
            if (!array_key_exists('locale', $language)) {
                continue;
            }

            $response[$language['locale']] = $this->manager->install(
                $this->languageFactory->create([$language])
            );
        }

        return $response;
    }
}
