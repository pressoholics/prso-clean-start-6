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

use Inpsyde\MultilingualPress\Framework\Http\Request;

use function Inpsyde\MultilingualPress\callExit;

final class PhpRedirector implements Redirector
{

    /**
     * @var LanguageNegotiator
     */
    private $negotiator;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var NoRedirectStorage
     */
    private $storage;

    /**
     * @param LanguageNegotiator $negotiator
     * @param NoRedirectStorage $storage
     * @param Request $request
     */
    public function __construct(
        LanguageNegotiator $negotiator,
        NoRedirectStorage $storage,
        Request $request
    ) {

        $this->negotiator = $negotiator;
        $this->storage = $storage;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function redirect(): bool
    {
        $value = (string)$this->request->bodyValue(
            NoredirectPermalinkFilter::QUERY_ARGUMENT,
            INPUT_GET,
            FILTER_SANITIZE_STRING
        );

        if ($value !== '') {
            $this->storage->addLanguage($value);

            return false;
        }

        add_action(
            'template_redirect',
            function () {
                $target = $this->negotiator->redirectTarget();
                if (!$target->url() || $target->siteId() === get_current_blog_id()) {
                    return;
                }

                $this->storage->addLanguage($target->language());

                wp_redirect($target->url());
                callExit();
            },
            1
        );

        return true;
    }
}
