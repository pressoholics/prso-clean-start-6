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

/**
 * Class Activator
 */
class Activator
{
    const WC_API = 'am-software-api';
    const PRODUCT_ID = 'MultilingualPress+3';

    /**
     * @return array
     */
    public function status(): array
    {
        $license = Settings::read();
        if (!$license->isActive()) {
            return [
                'status' => Settings::STATUS['inactive'],
            ];
        }

        $request = $this->doRequest($license, 'status');
        if (is_wp_error($request)) {
            return [
                'status' => 'error',
                'message' => $request->errors['http_request_failed'][0],
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($request));
        if (isset($body->error)) {
            return [
                'status' => 'error',
                'message' => $body->error,
            ];
        }

        return [
            'status' => $body->status_check,
        ];
    }

    /**
     * @param Value $license
     * @return array
     */
    public function activate(Value $license): array
    {
        $request = $this->doRequest($license, 'activation');
        if (is_wp_error($request)) {
            return [
                'status' => 'error',
                'message' => $request->errors['http_request_failed'][0],
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($request));
        if (isset($body->error)) {
            return [
                'status' => 'error',
                'message' => $body->error,
            ];
        }

        return [
            'status' => $body->activated
                ? Settings::STATUS['active']
                : Settings::STATUS['inactive'],
        ];
    }

    /**
     * @param Value $license
     * @return array
     */
    public function deactivate(Value $license): array
    {
        $request = $this->doRequest($license, 'deactivation');
        if (is_wp_error($request)) {
            return [
                'status' => 'error',
                'message' => $request->errors['http_request_failed'][0],
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($request));
        if (isset($body->error)) {
            return [
                'status' => 'error',
                'message' => $body->error ?: esc_html__('Unknown Error.', 'multilingualpress'),
            ];
        }

        return [
            'status' => $body->deactivated
                ? Settings::STATUS['inactive']
                : Settings::STATUS['activate'],
        ];
    }

    /**
     * @param Value $license
     * @param string $request
     * @return mixed array or \WP_Error in case of failure
     *
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     */
    private function doRequest(Value $license, string $request)
    {
        // phpcs:enable

        $args = [
            'request' => $request,
            'wc-api' => self::WC_API,
            'product_id' => self::PRODUCT_ID,
            'platform' => str_ireplace(['http://', 'https://'], '', home_url()),
            'email' => $license->email(),
            'instance' => $license->instanceKey(),
            'licence_key' => $license->key(),
        ];

        $url = add_query_arg($args, MULTILINGUALPRESS_LICENSE_API_URL ?? '');

        return wp_safe_remote_get($url);
    }
}
