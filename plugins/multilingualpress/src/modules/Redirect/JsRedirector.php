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

namespace Inpsyde\MultilingualPress\Module\Redirect;

use Inpsyde\MultilingualPress\Framework\Api\TranslationSearchArgs;
use Inpsyde\MultilingualPress\Framework\Asset\AssetManager;

use function Inpsyde\MultilingualPress\currentSiteLocale;

final class JsRedirector implements Redirector
{
    const FILTER_UPDATE_INTERVAL = 'multilingualpress.noredirect_update_interval';
    const SCRIPT_HANDLE = 'multilingualpress-redirect';

    /**
     * @var AssetManager
     */
    private $assetManager;

    /**
     * @var LanguageNegotiator
     */
    private $negotiator;

    /**
     * @param LanguageNegotiator $negotiator
     * @param AssetManager $assetManager
     */
    public function __construct(LanguageNegotiator $negotiator, AssetManager $assetManager)
    {
        $this->negotiator = $negotiator;
        $this->assetManager = $assetManager;
    }

    /**
     * @inheritdoc
     */
    public function redirect(): bool
    {
        $urls = $this->languageUrls();
        if (!$urls) {
            return false;
        }

        /**
         * Filters the lifetime, in seconds, for data in the noredirect storage.
         *
         * @param int $lifetime
         */
        $lifetime = (int)apply_filters(
            NoRedirectStorage::FILTER_LIFETIME,
            NoRedirectStorage::LIFETIME_IN_SECONDS
        );

        /**
         * Filters the update interval, in seconds, for the timestamp of noredirect storage data.
         *
         * @param int $updateInterval
         */
        $updateInterval = (int)apply_filters(self::FILTER_UPDATE_INTERVAL, MINUTE_IN_SECONDS);

        $this->assetManager->enqueueScriptWithData(
            self::SCRIPT_HANDLE,
            'MultilingualPressRedirectorSettings',
            [
                'currentLanguage' => currentSiteLocale(),
                'noredirectKey' => NoredirectPermalinkFilter::QUERY_ARGUMENT,
                'storageLifetime' => absint($lifetime * 1000),
                'updateTimestampInterval' => absint($updateInterval * 1000),
                'urls' => $urls,
            ],
            false
        );

        return true;
    }

    /**
     * Returns an array with language codes as keys and URLs as values.
     *
     * @return string[]
     */
    private function languageUrls(): array
    {
        $args = (new TranslationSearchArgs)->makeNotStrictSearch();
        $targets = $this->negotiator->redirectTargets($args);
        if (!$targets) {
            return [];
        }

        $urls = [];
        foreach ($targets as $target) {
            $urls[$target->language()] = $target->url();
        }

        return $urls;
    }
}
