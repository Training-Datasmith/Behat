<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork;

use Behat\Testwork\Cli\Application;
use Behat\Testwork\Service_Container\Configuration\Configuration_Loader;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Service_Container\Extension_Manager;
/**
 * Defines the way application is created.
 *
 * Extend and implement this class to create an entry point for your framework.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Application_Factory
{
    /**
     * Returns application name.
     *
     * @return string
     */
    abstract protected function get_name();
    /**
     * Returns current application version.
     *
     * @return string
     */
    abstract protected function get_version();
    /**
     * Returns list of extensions enabled by default.
     *
     * @return Extension[]
     */
    abstract protected function get_default_extensions();
    /**
     * Returns the name of configuration environment variable.
     *
     * @return string
     */
    abstract protected function get_environment_variable_name();
    /**
     * Returns user config path.
     *
     * @return string|null
     */
    abstract protected function get_config_path();
    /**
     * Creates application instance.
     *
     * @return Application
     */
    public function create_application()
    {
        $configuration_loader = $this->create_configuration_loader();
        $extension_manager = $this->create_extension_manager();
        return new Application($this->get_name(), $this->get_version(), $configuration_loader, $extension_manager);
    }
    /**
     * Creates configuration loader.
     *
     * @return ConfigurationLoader
     */
    protected function create_configuration_loader()
    {
        return new Configuration_Loader($this->get_environment_variable_name(), $this->get_config_path());
    }
    /**
     * Creates extension manager.
     *
     * @return ExtensionManager
     */
    protected function create_extension_manager()
    {
        return new Extension_Manager($this->get_default_extensions());
    }
}