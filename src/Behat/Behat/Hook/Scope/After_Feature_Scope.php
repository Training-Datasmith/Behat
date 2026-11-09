<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Scope;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Scope\After_Test_Scope;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\Test_Result;

/**
 * Represents an After_Feature hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class After_Feature_Scope implements Feature_Scope, After_Test_Scope
{
    /**
     * Initializes scope.
     */
    public function __construct(private Environment $environment, private FeatureNode $feature, private Test_Result $result)
    {
    }

    /**
     * Returns hook scope name.
     */
    public function get_name(): string
    {
        return self::AFTER;
    }

    /**
     * Returns hook suite.
     *
     * @return Suite
     */
    public function get_suite()
    {
        return $this->environment->get_suite();
    }

    /**
     * Returns hook environment.
     */
    public function get_environment(): Environment
    {
        return $this->environment;
    }

    /**
     * Returns scope feature.
     */
    public function get_feature(): FeatureNode
    {
        return $this->feature;
    }

    /**
     * Returns test result.
     */
    public function get_test_result(): Test_Result
    {
        return $this->result;
    }
}
