<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JSON;

use Behat\Behat\Hook\Call\After_Feature;
use Behat\Behat\Hook\Call\Before_Feature;
use Behat\Behat\Hook\Scope\Step_Scope;
use Behat\Behat\Output\Node\Printer\Setup_Printer;
use Behat\Testwork\Call\Call_Results;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Hook\Call\After_Suite;
use Behat\Testwork\Hook\Call\Before_Suite;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Setup;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Teardown;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSON_Output_Printer;
use Behat\Testwork\Tester\Setup\Setup;
use Behat\Testwork\Tester\Setup\Teardown;

final readonly class JSON_Setup_Printer implements Setup_Printer
{
    public function __construct(private Exception_Presenter $exception_presenter)
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
                assert($output_printer instanceof JSON_Output_Printer);
                $callee = $call->get_callee();
                $message = $callee->get_name();
                if ($scope instanceof Step_Scope) {
                    $message .= ': ' . $scope->get_step()->getKeyword() . ' ' . $scope->get_step()->getText();
                }
                $message .= ': ' . $this->exception_presenter->present_exception($hook_call_result->get_exception(), apply_editor_url: false);
                $attributes = ['message' => $message, 'type' => $message_type];
                if ($callee instanceof Before_Suite || $callee instanceof After_Suite) {
                    $output_printer->add_suite_child('failures', $attributes);
                } elseif ($callee instanceof Before_Feature || $callee instanceof After_Feature) {
                    $output_printer->add_feature_child('failures', $attributes);
                } else {
                    $output_printer->add_scenario_child('failures', $attributes);
                }
            }
        }
    }
}
