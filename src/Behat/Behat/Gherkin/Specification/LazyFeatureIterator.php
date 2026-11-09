<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Gherkin\Specification;

use Behat\Gherkin\Filter\Feature_Filter_Interface;
use Behat\Gherkin\Filter\Name_Filter;
use Behat\Gherkin\Filter\Narrative_Filter;
use Behat\Gherkin\Filter\Role_Filter;
use Behat\Gherkin\Filter\Tag_Filter;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Suite\Exception\Suite_Configuration_Exception;
use Behat\Testwork\Suite\Suite;
/**
 * Lazily iterates (parses one-by-one) over features.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements SpecificationIterator<FeatureNode>
 */
final class Lazy_Feature_Iterator implements Specification_Iterator
{
    /**
     * @var string[]
     */
    private array $paths;
    /**
     * @var FeatureFilterInterface[]
     */
    private readonly array $filters;
    private int $position = 0;
    /**
     * @var FeatureNode[]
     */
    private $features = [];
    /**
     * @var FeatureNode
     */
    private $current_feature;
    /**
     * Initializes specifications.
     *
     * @param string[]          $paths
     * @param FeatureFilterInterface[] $filters
     */
    public function __construct(private readonly Suite $suite, private readonly Gherkin $gherkin, array $paths, array $filters = [])
    {
        $this->paths = array_values($paths);
        $this->filters = array_merge($this->get_suite_filters($this->suite), $filters);
    }
    public function get_suite(): Suite
    {
        return $this->suite;
    }
    public function rewind(): void
    {
        $this->position = 0;
        $this->move_to_next_available_feature();
    }
    public function next(): void
    {
        $this->move_to_next_available_feature();
    }
    public function valid(): bool
    {
        return null !== $this->current_feature;
    }
    public function key(): int
    {
        return $this->position;
    }
    public function current(): Feature_Node
    {
        return $this->current_feature;
    }
    /**
     * Returns list of filters from suite settings.
     *
     * @return list<FeatureFilterInterface>
     */
    private function get_suite_filters(Suite $suite): array
    {
        if (!$suite->has_setting('filters') || !is_array($suite->get_setting('filters'))) {
            return [];
        }
        $filters = [];
        foreach ($suite->get_setting('filters') as $type => $filter_string) {
            $filters[] = $this->create_filter($type, $filter_string, $suite);
        }
        return $filters;
    }
    /**
     * Creates filter of provided type.
     *
     * @param string $type
     * @param string $filterString
     *
     * @throws SuiteConfigurationException If filter type is not recognised
     */
    private function create_filter(int|string $type, $filter_string, Suite $suite): Feature_Filter_Interface
    {
        if ('role' === $type) {
            return new Role_Filter($filter_string);
        }
        if ('name' === $type) {
            return new Name_Filter($filter_string);
        }
        if ('tags' === $type) {
            return new Tag_Filter($filter_string);
        }
        if ('narrative' === $type) {
            return new Narrative_Filter($filter_string);
        }
        throw new Suite_Configuration_Exception(sprintf('`%s` filter is not supported by the `%s` suite. Supported types are `%s`.', $type, $suite->get_name(), implode('`, `', ['narrative', 'role', 'name', 'tags'])), $suite->get_name());
    }
    /**
     * Parses paths consequently.
     */
    private function move_to_next_available_feature(): void
    {
        while (!count($this->features) && $this->position < count($this->paths)) {
            $this->features = $this->parse_feature($this->paths[$this->position]);
            ++$this->position;
        }
        $this->current_feature = array_shift($this->features);
    }
    /**
     * Parses feature at path.
     *
     * @param string $path
     *
     * @return FeatureNode[]
     */
    private function parse_feature($path)
    {
        return $this->gherkin->load($path, $this->filters);
    }
}