<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook;

use Behat\Testwork\Call\Call_Center;
use Behat\Testwork\Call\Call_Results;
use Behat\Testwork\Hook\Call\Hook_Call;
use Behat\Testwork\Hook\Scope\Hook_Scope;

/**
 * Dispatches registered hooks for provided events.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final readonly class Hook_Dispatcher
{
    /**
     * Initializes hook dispatcher.
     */
    public function __construct(private Hook_Repository $repository, private Call_Center $call_center)
    {
    }

    /**
     * Dispatches hooks for a specified event.
     */
    public function dispatch_scope_hooks(Hook_Scope $scope): Call_Results
    {
        $results = [];
        foreach ($this->repository->get_scope_hooks($scope) as $hook) {
            $results[] = $this->dispatch_hook($scope, $hook);
        }

        return new Call_Results($results);
    }

    /**
     * Dispatches single event hook.
     *
     * @return Call_Result
     */
    private function dispatch_hook(Hook_Scope $scope, Hook $hook)
    {
        return $this->call_center->make_call(new Hook_Call($scope, $hook));
    }
}
