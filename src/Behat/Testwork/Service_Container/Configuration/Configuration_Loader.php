<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Service_Container\Configuration;

use Behat\Config\Config_Interface;
use Behat\Testwork\Service_Container\Exception\Configuration_Loading_Exception;
use Closure;
use Symfony\Component\Yaml\Yaml;

use function str_ends_with;

/**
 * Loads configuration from different sources.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Configuration_Loader
{
    private ?bool $profile_found = null;
    /**
     * @var array
     */
    private $debug_information = ['environment_variable_name' => 'none', 'environment_variable_content' => 'none', 'configuration_file_path' => 'none'];

    /**
     * Constructs reader.
     */
    public function __construct(private $environment_variable = null, private $configuration_path = null)
    {
    }

    /**
     * Sets environment variable name.
     *
     * @param string|null $variable
     */
    public function set_environment_variable_name($variable): void
    {
        $this->environment_variable = $variable;
    }

    /**
     * Returns environment variable name.
     *
     * @return string|null
     */
    public function get_environment_variable_name()
    {
        return $this->environment_variable;
    }

    /**
     * Sets configuration file path.
     *
     * @param string|null $path
     */
    public function set_configuration_file_path($path): void
    {
        $this->configuration_path = $path;
    }

    /**
     * Returns configuration file path.
     *
     * @return string|null
     */
    public function get_configuration_file_path()
    {
        return $this->configuration_path;
    }

    /**
     * Reads configuration sequence for specific profile.
     *
     * @param string $profile Profile name
     *
     * @return list<mixed>
     *
     * @throws Configuration_Loading_Exception
     */
    public function load_configuration($profile = 'default'): array
    {
        $this->profile_found = false;
        $configs = $this->load_environment_configuration();
        // second is file configuration (if there is some)
        if ($this->configuration_path) {
            $this->debug_information['configuration_file_path'] = $this->configuration_path;
            foreach ($this->load_file_configuration($this->configuration_path, $profile) as $config) {
                $configs[] = $config;
            }
        }
        // if specific profile has not been found
        if ('default' !== $profile && !$this->profile_found) {
            throw new Configuration_Loading_Exception(sprintf('Can not find configuration for `%s` profile.', $profile));
        }

        return $configs;
    }

    /**
     * Returns array with configuration debug information.
     *
     * @return array
     */
    public function debug_information()
    {
        return $this->debug_information;
    }

    /**
     * Loads information from environment variable.
     *
     * @return list<mixed>
     *
     * @throws Configuration_Loading_Exception If environment variable environment var is set to invalid JSON
     */
    private function load_environment_configuration(): array
    {
        $configs = [];
        if (!$this->environment_variable) {
            return $configs;
        }
        $this->debug_information['environment_variable_name'] = $this->environment_variable;
        if ($env_config = getenv($this->environment_variable)) {
            $config = @json_decode($env_config, true);
            $this->debug_information['environment_variable_content'] = $env_config;
            if (!$config) {
                throw new Configuration_Loading_Exception(sprintf('Environment variable `%s` should contain a valid JSON, but it is set to `%s`.', $this->environment_variable, $env_config));
            }
            $configs[] = $config;
        }

        return $configs;
    }

    /**
     * Loads information from YAML configuration file.
     *
     * @param string $profile    Profile name
     *
     * @throws Configuration_Loading_Exception If config file is not found
     *
     * @phpstan-impure
     */
    private function load_file_configuration($config_path, $profile): array
    {
        if (!is_file($config_path) || !is_readable($config_path)) {
            throw new Configuration_Loading_Exception(sprintf('Configuration file `%s` not found.', $config_path));
        }
        $base_path = rtrim(dirname($config_path), DIRECTORY_SEPARATOR);
        if (str_ends_with($config_path, '.php')) {
            $php_config = $this->get_php_config_object_closure($config_path)();
            if (!$php_config instanceof Config_Interface) {
                throw new Configuration_Loading_Exception(sprintf('Configuration file `%s` must return an instance of `%s`.', $config_path, Config_Interface::class));
            }
            $config = $php_config->to_array();
        } else {
            $config = (array) Yaml::parse(file_get_contents($config_path));
        }

        return $this->load_configs($base_path, $config, $profile);
    }

    /**
     * Scope isolated include.
     *
     * Prevents access to $this/self from included files.
     */
    private function get_php_config_object_closure(string $config_path): Closure
    {
        return Closure::bind(fn (): mixed => require $config_path, null, null);
    }

    /**
     * Loads configs for provided config and profile.
     *
     * @param string $profile
     */
    private function load_configs(string $base_path, array $config, $profile): array
    {
        $configs = [];
        $profile = $this->get_profile_name($config, $profile);
        // first load default profile from current config, but only if custom profile requested
        if ('default' !== $profile && isset($config['default'])) {
            $configs[] = $config['default'];
        }
        // then recursively load profiles from imports
        if (isset($config['imports']) && is_array($config['imports'])) {
            $configs = array_merge($configs, $this->load_imports($base_path, $config['imports'], $profile));
        }
        // then load specific profile from current config
        if (isset($config[$profile])) {
            $configs[] = $config[$profile];
            $this->profile_found = true;
        }
        if (!$this->profile_found && isset($config['preferredProfileName'])) {
            throw new Configuration_Loading_Exception(sprintf('Can not find configuration for `%s` profile.', $config['preferredProfileName']));
        }

        return $configs;
    }

    /**
     * Get the name of the requested profile, after considering any preferred profile name.
     */
    private function get_profile_name(array $config, string $profile): string
    {
        if (isset($config['preferredProfileName']) && 'default' === $profile) {
            return $config['preferredProfileName'];
        }

        return $profile;
    }

    /**
     * Loads all provided imports.
     */
    private function load_imports(string $base_path, array $paths, string $profile): array
    {
        $configs = [];
        foreach ($paths as $path) {
            foreach ($this->parse_import($base_path, $path, $profile) as $import_config) {
                $configs[] = $import_config;
            }
        }

        return $configs;
    }

    /**
     * Parses import.
     *
     * @param string $path
     *
     * @throws Configuration_Loading_Exception If import file not found
     */
    private function parse_import(string $base_path, $path, string $profile): array
    {
        if (!file_exists($path) && file_exists($base_path . DIRECTORY_SEPARATOR . $path)) {
            $path = $base_path . DIRECTORY_SEPARATOR . $path;
        }
        if (!file_exists($path)) {
            throw new Configuration_Loading_Exception(sprintf('Can not import `%s` configuration file. File not found.', $path));
        }

        return $this->load_file_configuration($path, $profile);
    }
}
