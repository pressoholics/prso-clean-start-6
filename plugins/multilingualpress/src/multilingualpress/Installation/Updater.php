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

namespace Inpsyde\MultilingualPress\Installation;

use Inpsyde\MultilingualPress\Framework\SemanticVersionNumber;

/**
 * Updates any installed plugin data to the current version.
 */
class Updater
{
    /**
     * Updates any installed plugin data to the current version.
     *
     * @param SemanticVersionNumber $installedVersion
     */
    public function update(SemanticVersionNumber $installedVersion)
    {
        if (SemanticVersionNumber::FALLBACK_VERSION === (string)$installedVersion) {
            /* TODO: Check what is needed to be done here. */
            return;
        }
    }
}
