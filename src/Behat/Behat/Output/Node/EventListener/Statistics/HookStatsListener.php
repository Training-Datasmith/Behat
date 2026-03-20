<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Output\Node\Event_Listener\Statistics;

use Behat\Behat\Output\Statistics\Hook_Stat;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Call\Call_Result;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Event_Dispatcher\Event\After_Setup;
use Behat\Testwork\Event_Dispatcher\Event\After_Tested;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Hook\Call\Hook_Call;
use Behat\Testwork\Hook\Call\Runtime_Hook;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Setup;
use Behat\Testwork\Hook\Tester\Setup\Hooked_Teardown;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;
use Exception;
/**
 * Listens and records hook stats.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Hook_Stats_Listener implements Event_Listener
{
    /**
     * Initializes listener.
     */
    public function __construct(private Statistics $statistics, private Exception_Presenter $exception_presenter)
    {
    }
    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->capture_hook_stats_on_event($event);
    }
    /**
     * Captures hook stats on hooked event.
     */
    private function capture_hook_stats_on_event(Event $event): void
    {
        if ($event instanceof After_Setup && $event->get_setup() instanceof Hooked_Setup) {
            $this->capture_before_hook_stats($event->get_setup());
        }
        if ($event instanceof After_Tested && $event->get_teardown() instanceof Hooked_Teardown) {
            $this->capture_after_hook_stats($event->get_teardown());
        }
    }
    /**
     * Captures before hook stats.
     */
    private function capture_before_hook_stats(Hooked_Setup $setup): void
    {
        $hook_call_results = $setup->get_hook_call_results();
        foreach ($hook_call_results as $hook_call_result) {
            $this->capture_hook_stat($hook_call_result);
        }
    }
    /**
     * Captures before hook stats.
     */
    private function capture_after_hook_stats(Hooked_Teardown $teardown): void
    {
        $hook_call_results = $teardown->get_hook_call_results();
        foreach ($hook_call_results as $hook_call_result) {
            $this->capture_hook_stat($hook_call_result);
        }
    }
    /**
     * Captures hook call result.
     */
    private function capture_hook_stat(Call_Result $hook_call_result): void
    {
        $call = $hook_call_result->get_call();
        assert($call instanceof Hook_Call);
        $callee = $call->get_callee();
        $scope = $call->get_scope();
        $path = $callee->get_path();
        $std_out = $hook_call_result->get_std_out();
        $error = $hook_call_result->get_exception() instanceof Exception ? $this->exception_presenter->present_exception($hook_call_result->get_exception()) : null;
        assert($callee instanceof Runtime_Hook);
        $stat = new Hook_Stat((string) $callee, $path, $error, $std_out);
        if (!$stat->is_successful()) {
            $stat->set_scope($scope);
        }
        $this->statistics->register_hook_stat($stat);
    }
}