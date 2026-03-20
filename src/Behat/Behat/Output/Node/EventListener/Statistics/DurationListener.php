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

use Behat\Behat\Event_Dispatcher\Event\After_Feature_Tested;
use Behat\Behat\Event_Dispatcher\Event\After_Scenario_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Feature_Tested;
use Behat\Behat\Event_Dispatcher\Event\Before_Scenario_Tested;
use Behat\Gherkin\Node\Feature_Node;
use Behat\Gherkin\Node\Scenario_Like_Interface;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;
class Duration_Listener implements Event_Listener
{
    /**
     * @var array<string, Timer>
     */
    private array $scenario_timer_store = [];
    /**
     * @var array<string, Timer>
     */
    private array $feature_timer_store = [];
    /**
     * @var array<string, float>
     */
    private array $result_store = [];
    /**
     * @var array<string, float>
     */
    private array $feature_result_store = [];
    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->capture_before_scenario_event($event);
        $this->capture_before_feature_tested($event);
        $this->capture_after_scenario_event($event);
        $this->capture_after_feature_event($event);
    }
    public function get_duration(Scenario_Like_Interface $scenario): string
    {
        $key = $this->get_hash($scenario);
        return array_key_exists($key, $this->result_store) ? number_format($this->result_store[$key], 3, '.', '') : '';
    }
    public function get_feature_duration(Feature_Node $feature): string
    {
        $key = $this->get_hash($feature);
        return array_key_exists($key, $this->feature_result_store) ? number_format($this->feature_result_store[$key], 3, '.', '') : '';
    }
    private function capture_before_feature_tested(Event $event): void
    {
        if (!$event instanceof Before_Feature_Tested) {
            return;
        }
        $this->feature_timer_store[$this->get_hash($event->get_feature())] = $this->start_timer();
    }
    private function capture_before_scenario_event(Event $event): void
    {
        if (!$event instanceof Before_Scenario_Tested) {
            return;
        }
        $this->scenario_timer_store[$this->get_hash($event->get_scenario())] = $this->start_timer();
    }
    private function capture_after_scenario_event(Event $event): void
    {
        if (!$event instanceof After_Scenario_Tested) {
            return;
        }
        $key = $this->get_hash($event->get_scenario());
        if (isset($this->scenario_timer_store[$key])) {
            $timer = $this->scenario_timer_store[$key];
            $timer->stop();
            $this->result_store[$key] = $timer->get_time();
            unset($this->scenario_timer_store[$key]);
        }
    }
    private function capture_after_feature_event(Event $event): void
    {
        if (!$event instanceof After_Feature_Tested) {
            return;
        }
        $key = $this->get_hash($event->get_feature());
        if (isset($this->feature_timer_store[$key])) {
            $timer = $this->feature_timer_store[$key];
            $timer->stop();
            $this->feature_result_store[$key] = $timer->get_time();
            unset($this->feature_timer_store[$key]);
        }
    }
    protected function get_hash(object $node): string
    {
        return spl_object_hash($node);
    }
    protected function start_timer(): Timer
    {
        $timer = new Timer();
        $timer->start();
        return $timer;
    }
}