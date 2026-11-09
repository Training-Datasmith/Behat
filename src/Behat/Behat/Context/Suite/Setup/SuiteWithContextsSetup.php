<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context\Suite\Setup;

use Behat\Behat\Context\Context_Class\Class_Generator;
use Behat\Behat\Context\Exception\Context_Not_Found_Exception;
use Behat\Testwork\Filesystem\Filesystem_Logger;
use Behat\Testwork\Suite\Exception\Suite_Configuration_Exception;
use Behat\Testwork\Suite\Setup\Suite_Setup;
use Behat\Testwork\Suite\Suite;
use Composer\Autoload\Class_Loader;
/**
 * Generates classes for all contexts in the suite using autoloader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Suite_With_Contexts_Setup implements Suite_Setup
{
    /**
     * @var ClassGenerator[]
     */
    private array $class_generators = [];
    /**
     * Initializes setup.
     */
    public function __construct(private readonly Class_Loader $autoloader, private readonly ?Filesystem_Logger $logger = null)
    {
    }
    /**
     * Registers class generator.
     */
    public function register_class_generator(Class_Generator $generator): void
    {
        $this->class_generators[] = $generator;
    }
    public function supports_suite(Suite $suite)
    {
        return $suite->has_setting('contexts');
    }
    public function setup_suite(Suite $suite): void
    {
        foreach ($this->get_normalized_context_classes($suite) as $class) {
            if (class_exists($class)) {
                continue;
            }
            $this->ensure_context_directory($path = $this->find_class_file($class));
            if ($content = $this->generate_class($suite, $class)) {
                $this->create_context_file($path, $content);
            }
        }
    }
    /**
     * Returns normalized context classes.
     *
     * @return string[]
     */
    private function get_normalized_context_classes(Suite $suite): array
    {
        return array_map(fn(array|string $context): mixed => is_array($context) ? current(array_keys($context)) : $context, $this->get_suite_contexts($suite));
    }
    /**
     * Returns array of context classes configured for the provided suite.
     *
     * @return array<string|array>
     *
     * @throws SuiteConfigurationException If `contexts` setting is not an array
     */
    private function get_suite_contexts(Suite $suite)
    {
        $contexts = $suite->get_setting('contexts');
        if (!is_array($contexts)) {
            throw new Suite_Configuration_Exception(sprintf('`contexts` setting of the "%s" suite is expected to be an array, `%s` given.', $suite->get_name(), gettype($contexts)), $suite->get_name());
        }
        return $contexts;
    }
    /**
     * Creates context directory in the filesystem.
     */
    private function create_context_directory(string $path): void
    {
        mkdir($path, 0777, true);
        if ($this->logger instanceof Filesystem_Logger) {
            $this->logger->directory_created($path, 'place your context classes here');
        }
    }
    /**
     * Creates context class file in the filesystem.
     *
     * @param string $content
     */
    private function create_context_file(string $path, $content): void
    {
        file_put_contents($path, $content);
        if ($this->logger instanceof Filesystem_Logger) {
            $this->logger->file_created($path, 'place your definitions, transformations and hooks here');
        }
    }
    /**
     * Finds file to store a class.
     *
     * @param string $class
     *
     * @throws ContextNotFoundException If class file could not be determined
     */
    private function find_class_file($class): string
    {
        [$classpath, $classname] = $this->find_classpath_and_class($class);
        $classpath .= str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';
        foreach ($this->autoloader->get_prefixes() as $prefix => $dirs) {
            if (str_starts_with($class, $prefix)) {
                return current($dirs) . DIRECTORY_SEPARATOR . $classpath;
            }
        }
        if ($dirs = $this->autoloader->get_fallback_dirs()) {
            return current($dirs) . DIRECTORY_SEPARATOR . $classpath;
        }
        throw new Context_Not_Found_Exception(sprintf('Could not find where to put "%s" class. Have you configured autoloader properly?', $class), $class);
    }
    /**
     * Generates class using registered class generators.
     *
     * @param string $class
     *
     * @return string|null
     */
    private function generate_class(Suite $suite, $class)
    {
        $content = null;
        foreach ($this->class_generators as $generator) {
            if ($generator->supports_suite_and_class($suite, $class)) {
                $content = $generator->generate_class($suite, $class);
            }
        }
        return $content;
    }
    /**
     * Ensures that directory for a classpath exists.
     */
    private function ensure_context_directory(string $classpath): void
    {
        if (!is_dir(dirname($classpath))) {
            $this->create_context_directory(dirname($classpath));
        }
    }
    /**
     * Finds classpath and classname from class.
     *
     * @param string $class
     *
     * @return array{?string, string}
     */
    private function find_classpath_and_class($class): array
    {
        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $classpath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
            $classname = substr($class, $pos + 1);
            return [$classpath, $classname];
        }
        // PEAR-like class name
        $classpath = null;
        $classname = $class;
        return [$classpath, $classname];
    }
}