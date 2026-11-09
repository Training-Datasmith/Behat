<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Tester\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Tester\Result\Interpretation\Strict_Interpretation;
use Behat\Testwork\Tester\Result\Result_Interpreter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Configures Testwork to interpret test results strictly.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Strict_Controller implements Controller
{
    /**
     * Initializes controller.
     *
     * @param bool           $strict
     */
    public function __construct(private readonly Result_Interpreter $result_interpreter, private $strict = false)
    {
    }
    public function configure(Command $command): void
    {
        $command->add_option('--strict', null, Input_Option::VALUE_NONE, 'Passes only if all tests are explicitly passing.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): null
    {
        if (!$this->strict && !$input->get_option('strict')) {
            return null;
        }
        $this->result_interpreter->register_result_interpretation(new Strict_Interpretation());
        return null;
    }
}