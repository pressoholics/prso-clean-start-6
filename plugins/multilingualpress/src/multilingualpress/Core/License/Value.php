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
 * Class Value
 */
class Value
{
    const EMAIL_KEY = 'license_email';
    const API_KEY_KEY = 'api_key';
    const INSTANCE_KEY = 'instance_key';

    /**
     * @var array
     */
    private $data;

    /**
     * Value constructor.
     * @param string $email
     * @param string $key
     * @param string $instance
     * @param string $status
     */
    public function __construct(
        string $email,
        string $key,
        string $instance,
        string $status = Settings::STATUS['inactive']
    ) {

        $this->data = [
            self::EMAIL_KEY => $email,
            self::API_KEY_KEY => $key,
            self::INSTANCE_KEY => $instance,
            'status' => $status,
        ];
    }

    /**
     * @return string
     */
    public function email(): string
    {
        return $this->data[self::EMAIL_KEY] ?: '';
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return $this->data[self::API_KEY_KEY] ?: '';
    }

    /**
     * @return string
     */
    public function instanceKey(): string
    {
        return $this->data[self::INSTANCE_KEY] ?: '';
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return Settings::STATUS['active'] === $this->data['status'];
    }
}
