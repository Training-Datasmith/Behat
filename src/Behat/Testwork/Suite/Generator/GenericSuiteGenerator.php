<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Suite\Generator;

use Behat\Testwork\Suite\Generic_Suite;
use Behat\Testwork\Suite\Suite;
/**
 * Generates generic test suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Generic_Suite_Generator implements Suite_Generator
{
    /**
     * Initializes suite generator.
     */
    public function __construct(private array $default_settings = [])
    {
    }
    public function supports_type_and_settings($type, array $settings): bool
    {
        return null === $type;
    }
    public function generate_suite($suite_name, array $settings): Suite
    {
        return new Generic_Suite($suite_name, $this->merge_default_settings($settings));
    }
    /**
     * Merges provided settings into default ones.
     */
    private function merge_default_settings(array $settings): array
    {
        return array_merge($this->default_settings, $settings);
    }
}