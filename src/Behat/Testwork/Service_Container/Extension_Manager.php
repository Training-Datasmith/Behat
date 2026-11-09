<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Service_Container;

use Behat\Behat\Util\Strict_Regex;
use Behat\Testwork\Service_Container\Exception\Extension_Initialization_Exception;

/**
 * Manages both default and 3rd-party extensions.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Extension_Manager
{
    /**
     * @var Extension[]
     */
    private $extensions = [];
    /**
     * @var Extension[string]
     */
    private array $located_extensions = [];
    private array $debug_information = ['extensions_list' => []];

    /**
     * Initializes manager.
     *
     * @param Extension[] $extensions     List of default extensions
     */
    public function __construct(array $extensions, private $extensions_path = null)
    {
        foreach ($extensions as $extension) {
            $this->extensions[$extension->get_config_key()] = $extension;
        }
    }

    /**
     * Sets path to directory in which manager will try to find extension files.
     *
     * @param string|null $path
     */
    public function set_extensions_path($path): void
    {
        $this->extensions_path = $path;
    }

    /**
     * Activate extension by its locator.
     *
     * @param string $locator phar file name, php file name, class name
     *
     * @return Extension
     */
    public function activate_extension(string $locator)
    {
        $extension = $this->initialize($locator);
        $this->debug_information['extensions_list'][] = $extension->get_config_key();

        return $this->extensions[$extension->get_config_key()] = $extension;
    }

    /**
     * Returns specific extension by its name.
     *
     * @param string $key
     *
     * @return Extension|null
     */
    public function get_extension($key)
    {
        return $this->extensions[$key] ?? null;
    }

    /**
     * Returns all available extensions.
     *
     * @return Extension[]
     */
    public function get_extensions()
    {
        return $this->extensions;
    }

    /**
     * Returns activated extension names.
     *
     * @return list<class-string<Extension>>
     */
    public function get_extension_classes(): array
    {
        return array_map(get_class(...), array_values($this->extensions));
    }

    /**
     * Initializes all activated and predefined extensions.
     */
    public function initialize_extensions(): void
    {
        foreach ($this->extensions as $extension) {
            $extension->initialize($this);
        }
    }

    /**
     * Returns array with extensions debug information.
     *
     * @return array
     */
    public function debug_information()
    {
        return $this->debug_information;
    }

    /**
     * Attempts to guess full extension class from relative.
     *
     * @internal
     */
    public static function guess_full_extension_class_name(string $locator): string
    {
        $parts = explode('\\', $locator);
        $name = Strict_Regex::replace('/Extension$/', '', end($parts)) . 'Extension';

        return $locator . '\Service_Container\\' . $name;
    }

    /**
     * Initializes extension by id.
     *
     * @throws Extension_Initialization_Exception
     */
    private function initialize(string $locator): Extension
    {
        if (isset($this->located_extensions[$locator])) {
            return $this->located_extensions[$locator];
        }
        $extension = $this->instantiate_extension($locator);
        $this->validate_extension_instance($extension, $locator);

        return $this->located_extensions[$locator] = $extension;
    }

    /**
     * Instantiates extension from its locator.
     *
     * @throws Extension_Initialization_Exception
     */
    private function instantiate_extension(string $locator): mixed
    {
        if (class_exists($class = $locator)) {
            return new $class();
        }
        if (class_exists($class = self::guess_full_extension_class_name($locator))) {
            return new $class();
        }
        if (file_exists($locator)) {
            return require $locator;
        }
        if (file_exists($path = $this->extensions_path . DIRECTORY_SEPARATOR . $locator)) {
            return require $path;
        }
        throw new Extension_Initialization_Exception(sprintf('`%s` extension file or class could not be located.', $locator), $locator);
    }

    /**
     * Validates extension instance.
     *
     * @throws Extension_Initialization_Exception
     */
    private function validate_extension_instance(mixed $extension, string $locator): void
    {
        if (null === $extension) {
            throw new Extension_Initialization_Exception(sprintf('`%s` extension could not be found.', $locator), $locator);
        }
        if (!is_object($extension)) {
            throw new Extension_Initialization_Exception(sprintf('`%s` extension could not be initialized.', $locator), $locator);
        }
        if (!$extension instanceof Extension) {
            throw new Extension_Initialization_Exception(sprintf('`%s` extension class should implement Testwork Extension interface.', $extension::class), $locator);
        }
    }
}
