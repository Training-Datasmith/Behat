<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Argument\Service_Container;

use Behat\Testwork\Argument\Constructor_Argument_Organiser;
use Behat\Testwork\Argument\Mixed_Argument_Organiser;
use Behat\Testwork\Argument\Preg_Match_Argument_Organiser;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Enables argument organisers for Testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Argument_Extension implements Extension
{
    /*
     * Available services
     */
    public const MIXED_ARGUMENT_ORGANISER_ID = 'argument.mixed_organiser';
    public const PREG_MATCH_ARGUMENT_ORGANISER_ID = 'argument.preg_match_organiser';
    public const CONSTRUCTOR_ARGUMENT_ORGANISER_ID = 'argument.constructor_organiser';

    public function get_config_key(): string
    {
        return 'argument';
    }

    public function initialize(Extension_Manager $extension_manager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $definition = new Definition(Mixed_Argument_Organiser::class);
        $container->setDefinition(self::MIXED_ARGUMENT_ORGANISER_ID, $definition);
        $definition = new Definition(Preg_Match_Argument_Organiser::class, [new Reference(self::MIXED_ARGUMENT_ORGANISER_ID)]);
        $container->setDefinition(self::PREG_MATCH_ARGUMENT_ORGANISER_ID, $definition);
        $definition = new Definition(Constructor_Argument_Organiser::class, [new Reference(self::MIXED_ARGUMENT_ORGANISER_ID)]);
        $container->setDefinition(self::CONSTRUCTOR_ARGUMENT_ORGANISER_ID, $definition);
    }

    public function process(ContainerBuilder $container): void
    {
    }
}
