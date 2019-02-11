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

namespace Inpsyde\MultilingualPress\Framework\Admin\Metabox;

class Entity
{
    /**
     * @var \WP_Post|\WP_Term|Entity|null
     */
    private $entity;

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @param \WP_Post|\WP_Term|Entity $object
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function __construct($object)
    {
        // phpcs:enable

        if (!is_object($object)) {
            return;
        }

        while ($object instanceof Entity) {
            $object = $object->expose();
        }

        switch (true) {
            case ($object instanceof \WP_Post):
                $this->entity = $object;
                $this->id = (int)$object->ID;
                break;
            case ($object instanceof \WP_Term):
                $this->entity = $object;
                $this->id = (int)$object->term_id;
                break;
        }
    }

    /**
     * @param string $var
     * @return mixed
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function __get(string $var)
    {
        // phpcs:enable

        return $this->prop($var);
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->entity && $this->id() > 0;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function is(string $type): bool
    {
        return $this->valid() && is_a($this->entity, $type);
    }

    /**
     * @param string $prop
     * @param null $default
     * @return mixed
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function prop(string $prop, $default = null)
    {
        // phpcs:enable

        if (!$this->valid()) {
            return $default;
        }

        if (is_callable([$this->entity, 'to_array'])) {
            return $this->entity->to_array()[$prop] ?? $default;
        }

        return $default;
    }

    /**
     * @return \WP_Post|\WP_Term|null
     *
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration
     */
    public function expose()
    {
        //phpcs:enable

        return $this->valid() ? clone $this->entity : null;
    }
}
