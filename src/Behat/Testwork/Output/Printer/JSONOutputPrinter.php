<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Output\Printer;

use Behat\Testwork\Output\Exception\Missing_Output_Path_Exception;
use Behat\Testwork\Output\Printer\Factory\File_Output_Factory;
use Symfony\Component\Console\Output\Output_Interface;
final class Json_Output_Printer extends Stream_Output_Printer
{
    /**
     * @var array<string, string|int|float|array>|null
     */
    private ?array $exercise = null;
    /**
     * @var array<string, string|int|float|array>[]
     */
    private array $suites = [];
    /**
     * @var array<string, string|int|float|array>[]
     */
    private array $features = [];
    /**
     * @var array<string, string|int|float|array>[]
     */
    private array $scenarios = [];
    /**
     * @var array<string, string|int|float|array>
     */
    private array $current_suite = [];
    /**
     * @var array<string, string|int|float|array>
     */
    private array $current_feature = [];
    /**
     * @var array<string, string|int|float|array>
     */
    private array $current_scenario = [];
    private int $scenarios_count = 0;
    private int $features_count = 0;
    private int $suites_count = 0;
    public function __construct(File_Output_Factory $output_factory)
    {
        parent::__construct($output_factory);
    }
    public function create_new_file(): void
    {
        $this->exercise = ['suites' => &$this->suites];
        $this->flush();
    }
    /**
     * @param array<string, string|int|float> $exerciseAttributes
     */
    public function extend_exercise_attributes(array $exercise_attributes): void
    {
        $this->add_attributes_to_node($this->exercise, $exercise_attributes, true);
    }
    /**
     * @param array<string, string|int|float> $suiteAttributes
     */
    public function add_suite(array $suite_attributes = []): void
    {
        $this->suites[] = [];
        $this->current_suite =& $this->suites[$this->suites_count++];
        $this->current_suite['features'] = [];
        $this->features =& $this->current_suite['features'];
        $this->features_count = 0;
        $this->add_attributes_to_node($this->current_suite, $suite_attributes);
    }
    /**
     * @param array<string, string|int|float> $suiteAttributes
     */
    public function extend_suite_attributes(array $suite_attributes): void
    {
        $this->add_attributes_to_node($this->current_suite, $suite_attributes, true);
    }
    /**
     * @param array<string, string|int|float> $featureAttributes
     */
    public function add_feature(array $feature_attributes = []): void
    {
        $this->features[] = [];
        $this->current_feature =& $this->features[$this->features_count++];
        $this->current_feature['scenarios'] = [];
        $this->scenarios =& $this->current_feature['scenarios'];
        $this->scenarios_count = 0;
        $this->add_attributes_to_node($this->current_feature, $feature_attributes);
    }
    /**
     * @param array<string, string|int|float> $featureAttributes
     */
    public function extend_feature_attributes(array $feature_attributes): void
    {
        $this->add_attributes_to_node($this->current_feature, $feature_attributes, true);
    }
    /**
     * @param array<string, string|int|float> $scenarioAttributes
     */
    public function add_scenario(array $scenario_attributes = []): void
    {
        $this->scenarios[] = [];
        $this->current_scenario =& $this->scenarios[$this->scenarios_count++];
        $this->add_attributes_to_node($this->current_scenario, $scenario_attributes);
    }
    /**
     * @param array<string, string|int|float> $scenarioAttributes
     */
    public function add_current_scenario_attributes(array $scenario_attributes, bool $at_start = false): void
    {
        $this->add_attributes_to_node($this->current_scenario, $scenario_attributes, $at_start);
    }
    /**
     * @param array<string, string|int|float> $nodeAttributes
     */
    public function add_suite_child(string $node_name, array $node_attributes = []): void
    {
        $child_node = [];
        if (!isset($this->current_suite[$node_name])) {
            $this->current_suite[$node_name] = [];
        }
        $this->current_suite[$node_name][] =& $child_node;
        $this->add_attributes_to_node($child_node, $node_attributes);
    }
    /**
     * @param array<string, string|int|float> $nodeAttributes
     */
    public function add_feature_child(string $node_name, array $node_attributes = []): void
    {
        $child_node = [];
        if (!isset($this->current_feature[$node_name])) {
            $this->current_feature[$node_name] = [];
        }
        $this->current_feature[$node_name][] =& $child_node;
        $this->add_attributes_to_node($child_node, $node_attributes);
    }
    /**
     * @param array<string, string|int|float> $nodeAttributes
     */
    public function add_scenario_child(string $node_name, array $node_attributes = []): void
    {
        $child_node = [];
        if (!isset($this->current_scenario[$node_name])) {
            $this->current_scenario[$node_name] = [];
        }
        $this->current_scenario[$node_name][] =& $child_node;
        $this->add_attributes_to_node($child_node, $node_attributes);
    }
    public function flush(): void
    {
        if (is_array($this->exercise)) {
            try {
                $this->get_writing_stream()->write(json_encode($this->exercise, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES), false, Output_Interface::OUTPUT_RAW);
            } catch (Missing_Output_Path_Exception) {
                throw new Missing_Output_Path_Exception('The `output_path` option must be specified for the json formatter.');
            }
        }
        parent::flush();
    }
    /**
     * @param array<string, string|int|float|array> $node
     * @param array<string, string|int|float> $attributes
     */
    private function add_attributes_to_node(array &$node, array $attributes, bool $at_start = false): void
    {
        $node = $at_start ? [...$attributes, ...$node] : [...$node, ...$attributes];
    }
}