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

namespace Inpsyde\MultilingualPress\Framework\Admin;

/**
 * Pointers Repository.
 */
class PointersRepository
{
    /**
     * @var array
     */
    private $pointers;

    /**
     * @var array
     */
    private $actions;

    /**
     * @param string $screen
     * @param string $key
     * @param string $target
     * @param string $next
     * @param array $nextTrigger
     * @param array $options
     * @return $this
     */
    public function registerForScreen(
        string $screen,
        string $key,
        string $target,
        string $next,
        array $nextTrigger,
        array $options
    ): PointersRepository {

        $this->pointers[$screen][$key] = [
            'target' => $target,
            'next' => $next,
            'next_trigger' => $nextTrigger,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * @param string $screen
     * @return array
     */
    public function pointersForScreen(string $screen): array
    {
        return [
            $this->pointers[$screen] ?? [],
            $this->actions[$screen] ?? '',
        ];
    }

    /**
     * @param string $screen
     * @param string $action
     * @return $this
     */
    public function registerActionForScreen(string $screen, string $action): PointersRepository
    {
        $this->actions[$screen] = $action;

        return $this;
    }
}
