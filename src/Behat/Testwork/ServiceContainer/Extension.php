<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Service_Container;

use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Dependency_Injection\Compiler\Compiler_Pass_Interface;
use Symfony\Component\Dependency_Injection\Container_Builder;
/**
 * Represents Testwork extension mechanism.
 *
 * Extensions are the core entities in Testwork. Almost all framework functionality in Testwork and its different
 * implementations is provided through extensions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Extension extends Compiler_Pass_Interface
{
    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function get_config_key();
    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     */
    public function initialize(Extension_Manager $extension_manager);
    /**
     * Setups configuration for the extension.
     *
     * NOTE: If your extension uses the ArrayNodeDefinition passed to this method, your composer.json should declare
     * a direct dependency on the version(s) of symfony/config that you support.
     */
    public function configure(Array_Node_Definition $builder);
    /**
     * Loads extension services into temporary container.
     *
     * NOTE: If your extension uses the ContainerBuilder passed to this method, your composer.json should declare
     * a direct dependency on the version(s) of symfony/dependency-injection that you support.
     *
     * @param array<string, mixed> $config
     */
    public function load(Container_Builder $container, array $config);
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * NOTE: If your extension uses the ContainerBuilder passed to this method, your composer.json should declare
     *  a direct dependency on the version(s) of symfony/dependency-injection that you support.
     *
     * @return void
     */
    public function process(Container_Builder $container);
}