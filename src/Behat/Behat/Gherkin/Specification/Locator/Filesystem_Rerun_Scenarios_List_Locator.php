<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Gherkin\Specification\Locator;

use Behat\Behat\Gherkin\Specification\Lazy_Feature_Iterator;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Testwork\Specification\Locator\Specification_Locator;
use Behat\Testwork\Specification\No_Specifications_Iterator;
use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Suite\Suite;
/**
 * Loads gherkin features using a file with the list of scenarios.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements SpecificationLocator<FeatureNode>
 */
final readonly class Filesystem_Rerun_Scenarios_List_Locator implements Specification_Locator
{
    /**
     * Initializes locator.
     */
    public function __construct(private Gherkin $gherkin)
    {
    }
    public function get_locator_examples(): array
    {
        return [];
    }
    public function locate_specifications(Suite $suite, $locator): Specification_Iterator
    {
        if (null === $locator || !is_file($locator) || 'rerun' !== pathinfo($locator, PATHINFO_EXTENSION)) {
            return new No_Specifications_Iterator($suite);
        }
        $scenarios = json_decode(trim(file_get_contents($locator)), true);
        if (empty($scenarios) || empty($scenarios[$suite->get_name()])) {
            return new No_Specifications_Iterator($suite);
        }
        return new Lazy_Feature_Iterator($suite, $this->gherkin, $scenarios[$suite->get_name()]);
    }
}