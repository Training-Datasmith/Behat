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

use Behat\Behat\Event_Dispatcher\Event\After_Step_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Feature_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Scenario_Tested;
use Behat\Behat\Event_Dispatcher\Event\Feature_Tested;
use Behat\Behat\Event_Dispatcher\Event\Scenario_Tested;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Behat\Output\Statistics\Step_Stat_V2;
use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Behat\Tester\Result\Defined_Step_Result;
use Behat\Behat\Tester\Result\Executed_Step_Result;
use Behat\Behat\Tester\Result\Step_Result;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Exception\Exception_Presenter;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;
use Behat\Testwork\Tester\Result\Exception_Result;
use Throwable;
/**
 * Listens and records step events to statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Step_Stats_Listener implements Event_Listener
{
    private ?string $current_feature_path = null;
    private ?string $scenario_title = null;
    private ?string $scenario_path = null;
    public function __construct(private readonly Statistics $statistics, private readonly Exception_Presenter $exception_presenter)
    {
    }
    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->capture_current_feature_path_on_before_feature_event($event);
        $this->forget_current_feature_path_on_after_feature_event($event_name);
        $this->capture_scenario_on_before_feature_event($event);
        $this->forget_scenario_on_after_feature_event($event_name);
        $this->capture_step_stats_on_after_event($event);
    }
    /**
     * Captures current feature file path to the ivar on feature BEFORE event.
     */
    private function capture_current_feature_path_on_before_feature_event(Event $event): void
    {
        if (!$event instanceof Before_Feature_Tested) {
            return;
        }
        $this->current_feature_path = $event->get_feature()->get_file();
    }
    /**
     * Removes current feature file path from the ivar on feature AFTER event.
     *
     * @param string $eventName
     */
    private function forget_current_feature_path_on_after_feature_event($event_name): void
    {
        if (Feature_Tested::AFTER !== $event_name) {
            return;
        }
        $this->current_feature_path = null;
    }
    /**
     * Captures current scenario title and path on scenario BEFORE event.
     */
    private function capture_scenario_on_before_feature_event(Event $event): void
    {
        if (!$event instanceof Before_Scenario_Tested) {
            return;
        }
        $this->scenario_title = sprintf('%s: %s', $event->get_scenario()->get_keyword(), $event->get_scenario()->get_title());
        $this->scenario_path = sprintf('%s:%s', $this->current_feature_path, $event->get_scenario()->get_line());
    }
    private function forget_scenario_on_after_feature_event($event_name): void
    {
        if (Scenario_Tested::AFTER !== $event_name) {
            return;
        }
        $this->scenario_title = $this->scenario_path = null;
    }
    /**
     * Captures step stats on step AFTER event.
     */
    private function capture_step_stats_on_after_event(Event $event): void
    {
        if (!$event instanceof After_Step_Tested) {
            return;
        }
        $result = $event->get_test_result();
        $step = $event->get_step();
        $text = sprintf('%s %s', $step->get_keyword(), $step->get_text());
        $exception = $this->get_step_exception($result);
        $path = $this->get_step_path($event, $exception);
        $error = $exception instanceof Throwable ? $this->exception_presenter->present_exception($exception) : null;
        $std_out = $result instanceof Executed_Step_Result ? $result->get_call_result()->get_std_out() : null;
        $result_code = $result->get_result_code();
        $stat = new Step_Stat_V2($this->scenario_title, $this->scenario_path, $text, $path, $result_code, $error, $std_out);
        $this->statistics->register_step_stat($stat);
    }
    /**
     * Gets exception from the step test results.
     */
    private function get_step_exception(Step_Result $result): ?Throwable
    {
        if ($result instanceof Exception_Result) {
            return $result->get_exception();
        }
        return null;
    }
    /**
     * Gets step path from the AFTER test event and exception.
     */
    private function get_step_path(After_Step_Tested $event, ?Throwable $exception = null): string
    {
        $path = sprintf('%s:%d', $this->current_feature_path, $event->get_step()->get_line());
        if ($exception && $exception instanceof Pending_Exception) {
            $test_result = $event->get_test_result();
            assert($test_result instanceof Defined_Step_Result);
            $path = $test_result->get_step_definition()->get_path();
        }
        return $path;
    }
}