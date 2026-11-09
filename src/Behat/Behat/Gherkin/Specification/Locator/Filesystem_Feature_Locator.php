<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Specification\Locator;

use Behat\Behat\Gherkin\Specification\Lazy_Feature_Iterator;
use Behat\Gherkin\Filter\PathsFilter;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Specification\Locator\Specification_Locator;
use Behat\Testwork\Specification\No_Specifications_Iterator;
use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Suite\Exception\Suite_Configuration_Exception;
use Behat\Testwork\Suite\Suite;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Loads gherkin features from the filesystem using gherkin parser.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements Specification_Locator<FeatureNode>
 */
final class Filesystem_Feature_Locator implements Specification_Locator
{
    /**
     * Initializes loader.
     */
    public function __construct(private readonly Gherkin $gherkin, private $base_path)
    {
    }

    public function get_locator_examples(): array
    {
        return ['a dir <comment>(features/)</comment>', 'a feature <comment>(*.feature)</comment>', 'a scenario at specific line <comment>(*.feature:10)</comment>.', 'all scenarios at or after a specific line <comment>(*.feature:10-*)</comment>.', 'all scenarios at a line within a specific range <comment>(*.feature:10-20)</comment>.'];
    }

    public function locate_specifications(Suite $suite, $locator): Specification_Iterator
    {
        if (!$suite->has_setting('paths')) {
            return new No_Specifications_Iterator($suite);
        }
        $suite_locators = $this->get_suite_paths($suite);
        if ($locator) {
            $filters = [new PathsFilter($suite_locators)];

            return new Lazy_Feature_Iterator($suite, $this->gherkin, $this->find_feature_files($locator), $filters);
        }
        $feature_paths = [];
        foreach ($suite_locators as $suite_locator) {
            $feature_paths = array_merge($feature_paths, $this->find_feature_files($suite_locator));
        }

        return new Lazy_Feature_Iterator($suite, $this->gherkin, $feature_paths);
    }

    /**
     * Returns array of feature paths configured for the provided suite.
     *
     * @return string[]
     *
     * @throws Suite_Configuration_Exception If `paths` setting is not an array
     */
    private function get_suite_paths(Suite $suite)
    {
        if (!is_array($suite->get_setting('paths'))) {
            throw new Suite_Configuration_Exception(sprintf('`paths` setting of the "%s" suite is expected to be an array, %s given.', $suite->get_name(), gettype($suite->get_setting('paths'))), $suite->get_name());
        }

        return $suite->get_setting('paths');
    }

    /**
     * Loads feature files paths from provided path.
     *
     * @return list<string>
     */
    private function find_feature_files(string $path): array
    {
        $absolute_path = $this->find_absolute_path($path);
        if (!$absolute_path) {
            return [$path];
        }
        if (is_file($absolute_path)) {
            return [$absolute_path];
        }
        $iterator = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolute_path, RecursiveDirectoryIterator::FOLLOW_SYMLINKS | RecursiveDirectoryIterator::SKIP_DOTS)), '/^.+\.feature$/i', RegexIterator::MATCH);
        $paths = array_map(strval(...), iterator_to_array($iterator));
        uasort($paths, strnatcasecmp(...));

        return $paths;
    }

    /**
     * Finds absolute path for provided relative (relative to base features path).
     */
    private function find_absolute_path(string $path): string|false
    {
        if (is_file($path) || is_dir($path)) {
            return realpath($path);
        }
        if (null === $this->base_path) {
            return false;
        }
        if (is_file($this->base_path . DIRECTORY_SEPARATOR . $path) || is_dir($this->base_path . DIRECTORY_SEPARATOR . $path)) {
            return realpath($this->base_path . DIRECTORY_SEPARATOR . $path);
        }

        return false;
    }
}
