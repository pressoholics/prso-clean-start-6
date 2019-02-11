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

namespace Inpsyde\MultilingualPress\Cache;

use Inpsyde\MultilingualPress\Framework\Cache\CacheFactory;
use Inpsyde\MultilingualPress\Framework\Cache\Driver\WpObjectCacheDriver;
use Inpsyde\MultilingualPress\Framework\Cache\Server\Server;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\PluginProperties;
use Inpsyde\MultilingualPress\Framework\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;

/**
 * Service provider for all cache objects.
 */
final class ServiceProvider implements BootstrappableServiceProvider
{

    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        $container->share(
            CacheFactory::class,
            function (Container $container): CacheFactory {
                $version = $container[PluginProperties::class]->version();

                return new CacheFactory("mlp_{$version}_");
            }
        );

        $container->share(
            Server::class,
            function (Container $container): Server {
                return new Server(
                    $container[CacheFactory::class],
                    new WpObjectCacheDriver(),
                    new WpObjectCacheDriver(WpObjectCacheDriver::FOR_NETWORK),
                    $container[ServerRequest::class]
                );
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function bootstrap(Container $container)
    {
        $container[Server::class]->listenSpawn();
    }
}
