<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Suite\Setup;

use Behat\Testwork\Filesystem\Filesystem_Logger;
use Behat\Testwork\Suite\Setup\Suite_Setup;
use Behat\Testwork\Suite\Suite;

/**
 * Sets up gherkin suite in the filesystem (creates feature folders).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Suite_With_Paths_Setup implements Suite_Setup
{
    /**
     * Initializes setup.
     */
    public function __construct(private $base_path, private readonly ?Filesystem_Logger $logger = null)
    {
    }

    public function supports_suite(Suite $suite): bool
    {
        return $suite->has_setting('paths') && is_array($suite->get_setting('paths'));
    }

    public function setup_suite(Suite $suite): void
    {
        foreach ($suite->get_setting('paths') as $locator) {
            if (!str_starts_with((string) $locator, '@') && !is_dir($path = $this->locate_path($locator))) {
                $this->create_feature_directory($path);
            }
        }
    }

    /**
     * Creates feature directory.
     *
     * @param string $path
     */
    private function create_feature_directory($path): void
    {
        mkdir($path, 0777, true);
        if ($this->logger instanceof Filesystem_Logger) {
            $this->logger->directory_created($path, 'place your *.feature files here');
        }
    }

    /**
     * Locates path from a relative one.
     */
    private function locate_path(string $path): string
    {
        if ($this->is_absolute_path($path)) {
            return $path;
        }

        return $this->base_path . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     */
    private function is_absolute_path(string $file): bool
    {
        return $file[0] == '/' || $file[0] == '\\' || strlen($file) > 3 && ctype_alpha($file[0]) && $file[1] == ':' && ($file[2] == '\\' || $file[2] == '/') || null !== parse_url($file, PHP_URL_SCHEME);
    }
}
