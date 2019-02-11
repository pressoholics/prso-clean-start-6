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

namespace Inpsyde\MultilingualPress\Core\License;

use Inpsyde\MultilingualPress\Framework\PluginProperties;

class Updater
{
    const WC_API = 'upgrade-api';
    const PRODUCT_ID = 'MultilingualPress+3';

    /**
     * @var PluginProperties
     */
    private $pluginProperties;

    /**
     * @param PluginProperties $pluginProperties
     */
    public function __construct(PluginProperties $pluginProperties)
    {
        $this->pluginProperties = $pluginProperties;
    }

    /**
     * @param \stdClass $transient
     * @return \stdClass $transient
     */
    public function updateCheck(\stdClass $transient): \stdClass
    {
        $license = Settings::read();
        if (!$license->isActive()) {
            return $transient;
        }

        $currentVersion = $this->pluginProperties->version();
        $request = $this->doRequest($license, $currentVersion, 'pluginupdatecheck');

        if (is_wp_error($request)) {
            return $transient;
        }

        // phpcs:ignore
        $responseBody = unserialize(wp_remote_retrieve_body($request));
        if (isset($responseBody->error)) {
            return $transient;
        }

        if (isset($responseBody->new_version)) {
            if (version_compare($responseBody->new_version, $currentVersion, '>')) {
                $pluginBaseName = $this->pluginProperties->basename();
                $transient->response[$pluginBaseName] = $responseBody;
            }
        }

        return $transient;
    }

    /**
     * @param bool $result
     * @return mixed
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     */
    public function pluginInformation(bool $result)
    {
        // phpcs:enable

        $license = Settings::read();
        if (!$license->isActive()) {
            return false;
        }

        $currentVersion = $this->pluginProperties->version();
        $request = $this->doRequest($license, $currentVersion, 'plugininformation');

        if (is_wp_error($request)) {
            return false;
        }

        // phpcs:ignore
        $responseBody = unserialize(wp_remote_retrieve_body($request));
        if (isset($responseBody->error)) {
            return false;
        }

        return $responseBody;
    }

    /**
     * @param Value $license
     * @param string $currentVersion
     * @param string $request
     * @return mixed array or \WP_Error in case of failure
     *
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     */
    private function doRequest(Value $license, string $currentVersion, string $request)
    {
        // phpcs:enable

        $args = [
            'wc-api' => self::WC_API,
            'request' => $request,
            'plugin_name' =>  $this->pluginProperties->basename(),
            'version' => $currentVersion,
            'software_version' => $currentVersion,
            'product_id' => self::PRODUCT_ID,
            'domain' => str_ireplace(['http://', 'https://'], '', home_url()),
            'api_key' => $license->key(),
            'activation_email' => $license->email(),
            'instance' => $license->instanceKey(),
        ];

        $url = add_query_arg($args, MULTILINGUALPRESS_LICENSE_API_URL ?? '');

        return wp_safe_remote_get($url);
    }
}
