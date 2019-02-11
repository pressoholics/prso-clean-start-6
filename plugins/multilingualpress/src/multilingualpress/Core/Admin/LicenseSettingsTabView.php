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
use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;

use function Inpsyde\MultilingualPress\printNonceField;

class LicenseSettingsTabView implements SettingsPageView
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
     * @inheritdoc
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function render()
    {
        // phpcs:enable

        $status = License\Settings::STATUS['inactive'];
        $license = License\Settings::read();

        if ($license->isActive()) {
            $status = License\Settings::STATUS['active'];
        }

        $instanceKey = $license->isActive()
            ? $license->instanceKey()
            : wp_generate_password(12, false);

        printNonceField($this->nonce);
        ?>
        <table class="form-table widefat mlp-settings-table mlp-license-settings">
            <tbody>
            <tr>
                <th scope="row">
                    <?php esc_html_e('API Key Status', 'multilingualpress'); ?>
                </th>
                <td class="mlp-licence-api-key-status"
                    data-status="<?php echo esc_attr(sanitize_key($status)) ?>"
                >
                    <?php echo esc_html(ucfirst($status)); ?>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="<?php $this->inputNameAttr('key') ?>">
                        <?php esc_html_e('API Key', 'multilingualpress'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                           id="<?php $this->inputNameAttr('key') ?>"
                           name="<?php $this->inputNameAttr('key') ?>"
                           value="<?php echo esc_attr($license->key()); ?>"
                    />
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="<?php $this->inputNameAttr('email') ?>">
                        <?php esc_html_e('API Email', 'multilingualpress'); ?>
                    </label>
                </th>
                <td>
                    <input type="email"
                           id="<?php $this->inputNameAttr('email') ?>"
                           name="<?php $this->inputNameAttr('email') ?>"
                           value="<?php echo esc_attr($license->email()); ?>"
                    />
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="<?php $this->inputNameAttr('deactivate') ?>">
                        <?php esc_html_e('Deactivate license', 'multilingualpress') ?>
                    </label>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                               id="<?php $this->inputNameAttr('deactivate') ?>"
                               name="<?php $this->inputNameAttr('deactivate') ?>"
                               value="1"
                        />
                    </label>
                </td>
            </tr>
            </tbody>
        </table>

        <input type="hidden"
               name="<?php $this->inputNameAttr('instance') ?>"
               value="<?php echo esc_attr($instanceKey); ?>"
        />
        <?php
    }

    /**
     * @param string $attr
     * @return void Echo the attribute
     */
    private function inputNameAttr(string $attr)
    {
        $settingKey = License\Settings::OPTION_KEY;
        $emailKey = License\Value::EMAIL_KEY;
        $keyKey = License\Value::API_KEY_KEY;
        $instanceKey = License\Value::INSTANCE_KEY;

        switch ($attr) {
            case 'email':
                $attrValue = "{$settingKey}[{$emailKey}]";
                break;
            case 'key':
                $attrValue = "{$settingKey}[{$keyKey}]";
                break;
            case 'instance':
                $attrValue = "{$settingKey}[{$instanceKey}]";
                break;
            case 'deactivate':
                $attrValue = "{$settingKey}[deactivate]";
                break;
            default:
                $attrValue = '';
        }

        echo esc_attr($attrValue);
    }
}
