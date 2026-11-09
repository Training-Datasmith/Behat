<?php

declare (strict_types=1);
namespace Behat\Behat\Output\Node\Printer\J_Unit;

use Behat\Behat\Hook\Scope\Step_Scope;
use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Testwork\Call\Call_Results;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Setup;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Teardown;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\J_Unit_Output_Printer;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;
/**
 * @author: Jakob Erdmann <jakob.erdmann@rocket-internet.com>
 */
class J_Unit_Setup_Printer implements Setup_Printer
{
    public function __construct(private readonly Exception_Presenter $exception_presenter)
    {
    }
    public function print_setup(Formatter $formatter, Setup $setup): void
    {
        if (!$setup->is_successful() && $setup instanceof Hooked_Setup) {
            $this->handle_hook_calls($formatter, $setup->get_hook_call_results(), 'setup');
        }
    }
    public function print_teardown(Formatter $formatter, Teardown $teardown): void
    {
        if (!$teardown->is_successful() && $teardown instanceof Hooked_Teardown) {
            $this->handle_hook_calls($formatter, $teardown->get_hook_call_results(), 'teardown');
        }
    }
    private function handle_hook_calls(Formatter $formatter, Call_Results $results, string $message_type): void
    {
        foreach ($results as $hook_call_result) {
            if ($hook_call_result->has_exception()) {
                $call = $hook_call_result->get_call();
                $scope = $call->get_scope();
                $output_printer = $formatter->get_output_printer();
                assert($output_printer instanceof J_Unit_Output_Printer);
                $callee = $call->get_callee();
                $message = $callee->get_name();
                if ($scope instanceof Step_Scope) {
                    $message .= ': ' . $scope->get_step()->get_keyword() . ' ' . $scope->get_step()->get_text();
                }
                $message .= ': ' . $this->exception_presenter->present_exception($hook_call_result->get_exception(), applyEditorUrl: false);
                $attributes = ['message' => $message, 'type' => $message_type];
                $output_printer->add_testcase_child('failure', $attributes);
            }
        }
    }
}