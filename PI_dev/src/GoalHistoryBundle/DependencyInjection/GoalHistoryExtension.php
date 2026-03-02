<?php

namespace App\GoalHistoryBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Loads the GoalHistoryBundle service configuration into the DI container.
 */
class GoalHistoryExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');
    }

    /**
     * The alias is used in config files (e.g. config/packages/goal_history.yaml).
     * Extension class name already follows the convention, but we make it explicit.
     */
    public function getAlias(): string
    {
        return 'goal_history';
    }
}
