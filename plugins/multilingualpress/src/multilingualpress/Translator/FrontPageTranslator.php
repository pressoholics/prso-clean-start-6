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

namespace Inpsyde\MultilingualPress\Translator;

use Inpsyde\MultilingualPress\Framework\Api\TranslationSearchArgs;
use Inpsyde\MultilingualPress\Framework\Factory\UrlFactory;
use Inpsyde\MultilingualPress\Framework\Api\Translation;
use Inpsyde\MultilingualPress\Framework\Translator\Translator;

/**
 * Translator implementation for front-page requests.
 */
final class FrontPageTranslator implements Translator
{
    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * @param UrlFactory $urlFactory
     */
    public function __construct(UrlFactory $urlFactory)
    {
        $this->urlFactory = $urlFactory;
    }

    /**
     * @inheritdoc
     */
    public function translationFor(int $siteId, TranslationSearchArgs $args): Translation
    {
        $url = get_home_url($siteId, '/');

        return (new Translation())->withRemoteUrl($this->urlFactory->create([$url]));
    }
}
