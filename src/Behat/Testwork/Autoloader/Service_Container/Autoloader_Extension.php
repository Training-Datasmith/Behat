<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Autoloader\Service_Container;

use Behat\Testwork\Autoloader\Cli\Autoloader_Controller;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends Testwork with class-loading services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Autoloader_Extension implements Extension
{
    /*
     * Available services
     */
    public const CLASS_LOADER_ID = 'class_loader';

    /**
     * Initializes extension.
     */
    public function __construct(private array $default_paths = [])
    {
    }

    /**
     * Returns the extension config key.
     */
    public function get_config_key(): string
    {
        return 'autoload';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder = $builder->beforeNormalization()->ifString()->then(fn ($path): array => ['' => $path])->end()->defaultValue($this->default_paths)->treatTrueLike($this->default_paths)->treatNullLike([])->treatFalseLike([]);
        /** @var ArrayNodeDefinition $builder */
        $builder->prototype('scalar')->end();
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->load_autoloader($container);
        $this->load_controller($container);
        $this->set_loader_prefixes($container, $config);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->process_loader_prefixes($container);
    }

    /**
     * Loads Symfony2 autoloader.
     */
    private function load_autoloader(ContainerBuilder $container): void
    {
        $definition = new Definition(ClassLoader::class);
        $container->setDefinition(self::CLASS_LOADER_ID, $definition);
    }

    /**
     * Loads controller.
     */
    private function load_controller(ContainerBuilder $container): void
    {
        $definition = new Definition(Autoloader_Controller::class, [new Reference(self::CLASS_LOADER_ID)]);
        $definition->addTag(Cli_Extension::CONTROLLER_TAG, ['priority' => 9999]);
        $container->setDefinition(Cli_Extension::CONTROLLER_TAG . '.autoloader', $definition);
    }

    /**
     * Sets provided prefixes to container.
     */
    private function set_loader_prefixes(ContainerBuilder $container, array $prefixes): void
    {
        $container->setParameter('class_loader.prefixes', $prefixes);
    }

    /**
     * Processes container loader prefixes.
     */
    private function process_loader_prefixes(ContainerBuilder $container): void
    {
        $loader_definition = $container->getDefinition(self::CLASS_LOADER_ID);
        $prefixes = $container->getParameter('class_loader.prefixes');
        foreach ($prefixes as $prefix => $path) {
            if ($prefix === '') {
                // PSR-4 fallback: Studly_Snake context files (e.g. Feature_Context.php).
                $loader_definition->addMethodCall('addPsr4', [$prefix, $path]);
            } else {
                $loader_definition->addMethodCall('add', [$prefix, $path]);
            }
        }
    }
}
