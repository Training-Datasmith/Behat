<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Gherkin\Cli;

use Behat\Gherkin\Filter\Name_Filter;
use Behat\Gherkin\Filter\Narrative_Filter;
use Behat\Gherkin\Filter\Role_Filter;
use Behat\Gherkin\Filter\Tag_Filter;
use Behat\Gherkin\Gherkin;
use Behat\Testwork\Cli\Controller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Configures default Gherkin filters.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Filter_Controller implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(private Gherkin $gherkin)
    {
    }
    /**
     * Configures command to be executable by the controller.
     */
    public function configure(Command $command): void
    {
        $command->add_option('--name', null, Input_Option::VALUE_REQUIRED | Input_Option::VALUE_IS_ARRAY, 'Only execute the feature elements which match part' . PHP_EOL . 'of the given name or regex.')->add_option('--tags', null, Input_Option::VALUE_REQUIRED | Input_Option::VALUE_IS_ARRAY, 'Only execute the features or scenarios with tags' . PHP_EOL . 'matching tag filter expression.')->add_option('--role', null, Input_Option::VALUE_REQUIRED, 'Only execute the features with actor role matching' . PHP_EOL . 'a wildcard.')->add_option('--narrative', null, Input_Option::VALUE_REQUIRED, 'Only execute the features with actor description' . PHP_EOL . 'matching a regex.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): null
    {
        $filters = [];
        foreach ($input->get_option('name') as $name) {
            $filters[] = new Name_Filter($name);
        }
        foreach ($input->get_option('tags') as $tags) {
            $filters[] = new Tag_Filter($tags);
        }
        if ($role = $input->get_option('role')) {
            $filters[] = new Role_Filter($role);
        }
        if ($narrative = $input->get_option('narrative')) {
            $filters[] = new Narrative_Filter($narrative);
        }
        if (count($filters)) {
            $this->gherkin->set_filters($filters);
        }
        return null;
    }
}