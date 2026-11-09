<?php

declare(strict_types=1);
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
use Behat\Behat\Output\Statistics\Scenario_Stat;
use Behat\Behat\Output\Statistics\Statistics;
use Behat\Testwork\Event\Event;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Node\Event_Listener\Event_Listener;

/**
 * Listens and records scenario events to the statistics.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Scenario_Stats_Listener implements Event_Listener
{
    private ?string $current_feature_path = null;

    public function __construct(private readonly Statistics $statistics)
    {
    }

    public function listen_event(Formatter $formatter, Event $event, $event_name): void
    {
        $this->capture_current_feature_path_on_before_feature_event($event);
        $this->forget_current_feature_path_on_after_feature_event($event);
        $this->capture_scenario_or_example_stats_on_after_event($event);
    }

    /**
     * Captures current feature file path to the ivar on feature BEFORE event.
     */
    private function capture_current_feature_path_on_before_feature_event(Event $event): void
    {
        if (!$event instanceof Before_Feature_Tested) {
            return;
        }
        $this->current_feature_path = $event->get_feature()->getFile();
    }

    /**
     * Removes current feature file path from the ivar on feature AFTER event.
     */
    private function forget_current_feature_path_on_after_feature_event(Event $event): void
    {
        if (!$event instanceof After_Feature_Tested) {
            return;
        }
        $this->current_feature_path = null;
    }

    /**
     * Captures scenario or example stats on their AFTER event.
     */
    private function capture_scenario_or_example_stats_on_after_event(Event $event): void
    {
        if (!$event instanceof After_Scenario_Tested) {
            return;
        }
        $scenario = $event->get_scenario();
        $title = $scenario->getTitle();
        $path = sprintf('%s:%d', $this->current_feature_path, $scenario->getLine());
        $result_code = $event->get_test_result()->get_result_code();
        $stat = new Scenario_Stat($title, $path, $result_code);
        $this->statistics->register_scenario_stat($stat);
    }
}
