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

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Core\License;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;

class LicenseSettingsUpdater
{
    /**
     * @var Nonce
     */
    private $nonce;

    /**
     * @var License\Activator
     */
    private $licenseActivator;

    /**
     * @param License\Activator $licenseActivator
     * @param Nonce $nonce
     */
    public function __construct(License\Activator $licenseActivator, Nonce $nonce)
    {
        $this->licenseActivator = $licenseActivator;
        $this->nonce = $nonce;
    }

    /**
     * Updates the license settings.
     * @param Request $request
     * @return bool
     */
    public function updateSettings(Request $request): bool
    {
        if (!$this->nonce->isValid()) {
            return false;
        }

        list($license, $requestFor) = $this->licenseFromRequest($request);

        list($response, $status) = (License\Settings::STATUS['active'] === $requestFor
            ? $this->set($license)
            : $this->unset($license));

        $this->ensureStatus($response);

        return License\Settings::update($license, $status);
    }

    /**
     * @param License\Value $license
     * @return array
     */
    private function set(License\Value $license): array
    {
        $response = $this->licenseActivator->activate($license);
        $status = License\Settings::STATUS['active'] === $response['status']
            ? License\Settings::STATUS['active']
            : License\Settings::STATUS['inactive'];

        return [$response, $status];
    }

    /**
     * @param License\Value $license
     * @return array
     */
    private function unset(License\Value $license): array
    {
        $response = $this->licenseActivator->deactivate($license);
        $status = License\Settings::STATUS['inactive'] === $response['status']
            ? License\Settings::STATUS['inactive']
            : License\Settings::STATUS['unknown'];

        return [$response, $status];
    }

    /**
     * @param array $response
     */
    private function ensureStatus(array $response)
    {
        if ($response['status'] === 'error' && isset($response['message'])) {
            $this->setError($response['message']);
        }
    }

    /**
     * @param string $message
     */
    private function setError(string $message)
    {
        $messages[License\Settings::OPTION_KEY] = [
            'setting' => License\Settings::OPTION_KEY,
            'code' => License\Settings::OPTION_KEY,
            'message' => sanitize_text_field($message),
            'type' => 'error',
        ];

        set_transient('settings_errors', $messages);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function licenseFromRequest(Request $request): array
    {
        $settings = (array)$request->bodyValue(
            License\Settings::OPTION_KEY,
            INPUT_POST,
            FILTER_DEFAULT
        );

        $settings = filter_var_array(
            $settings,
            [
                License\Value::EMAIL_KEY => [
                    'options' => ['default' => ''],
                    'filter' => FILTER_VALIDATE_EMAIL,
                ],
                License\Value::API_KEY_KEY => FILTER_SANITIZE_STRING,
                License\Value::INSTANCE_KEY => FILTER_SANITIZE_STRING,
                'deactivate' => [
                    'options' => ['default' => ''],
                    'filter' => FILTER_VALIDATE_BOOLEAN,
                ],
            ]
        );

        $requestFor = $settings['deactivate']
            ? License\Settings::STATUS['inactive']
            : License\Settings::STATUS['active'];

        return [
            new License\Value(
                $settings[License\Value::EMAIL_KEY],
                $settings[License\Value::API_KEY_KEY],
                $settings[License\Value::INSTANCE_KEY],
                'unknown'
            ),
            $requestFor,
        ];
    }
}
