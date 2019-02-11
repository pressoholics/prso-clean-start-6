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
 * Class Settings
 * @package Inpsyde\MultilingualPress\Core\License
 */
class Settings
{
    const OPTION_KEY = 'multilingualpress_license';
    const STATUS = [
        'inactive' => 'inactive',
        'active' => 'active',
        'unknown' => 'unknown',
    ];

    /**
     * @return Value
     */
    public static function read(): Value
    {
        $options = get_network_option(0, self::OPTION_KEY);

        return new Value(
            $options[Value::EMAIL_KEY] ?? '',
            $options[Value::API_KEY_KEY] ?? '',
            $options[Value::INSTANCE_KEY] ?? '',
            $options['status'] ?? self::STATUS['inactive']
        );
    }

    /**
     * @param Value $license
     * @param string $status
     * @return bool
     */
    public static function update(Value $license, string $status): bool
    {
        return update_network_option(0, self::OPTION_KEY, [
            Value::EMAIL_KEY => $license->email(),
            Value::API_KEY_KEY => $license->key(),
            Value::INSTANCE_KEY => $license->instanceKey(),
            'status' => $status,
        ]);
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return delete_network_option(0, self::OPTION_KEY);
    }
}
