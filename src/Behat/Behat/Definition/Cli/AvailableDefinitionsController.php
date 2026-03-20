<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Definition\Cli;

use Behat\Behat\Definition\Definition_Writer;
use Behat\Behat\Definition\Printer\Console_Definition_Information_Printer;
use Behat\Behat\Definition\Printer\Console_Definition_List_Printer;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Suite\Suite_Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Shows all currently available definitions to the user.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Available_Definitions_Controller implements Controller
{
    /**
     * Initializes controller.
     */
    public function __construct(private Suite_Repository $suite_repository, private Definition_Writer $writer, private Console_Definition_List_Printer $list_printer, private Console_Definition_Information_Printer $info_printer)
    {
    }
    public function configure(Command $command): void
    {
        $command->add_option('--definitions', '-d', Input_Option::VALUE_REQUIRED, 'Print all available step definitions:' . PHP_EOL . '- use <info>--definitions l</info> to just list definition expressions.' . PHP_EOL . '- use <info>--definitions i</info> to show definitions with extended info.' . PHP_EOL . "- use <info>--definitions 'needle'</info> to find specific definitions." . PHP_EOL . 'Use <info>--lang</info> to see definitions in specific language.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): ?int
    {
        if (null === $argument = $input->get_option('definitions')) {
            return null;
        }
        $printer = $this->get_definition_printer($argument);
        foreach ($this->suite_repository->get_suites() as $suite) {
            $this->writer->print_suite_definitions($printer, $suite);
        }
        return 0;
    }
    /**
     * Returns definition printer for provided option argument.
     *
     * @param string $argument
     */
    private function get_definition_printer($argument): Console_Definition_List_Printer|Console_Definition_Information_Printer
    {
        if ('l' === $argument) {
            return $this->list_printer;
        }
        if ('i' !== $argument) {
            $this->info_printer->set_search_criterion($argument);
        }
        return $this->info_printer;
    }
}