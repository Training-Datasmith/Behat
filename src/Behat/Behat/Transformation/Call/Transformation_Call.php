<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Transformation\Call;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Transformation\Transformation;
use Behat\Testwork\Environment\Call\Environment_Call;
use Behat\Testwork\Environment\Environment;
/**
 * Call extended with transformation information.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Transformation_Call extends Environment_Call
{
    /**
     * Initializes call.
     */
    public function __construct(Environment $environment, private readonly Definition $definition, Transformation $transformation, array $arguments)
    {
        parent::__construct($environment, $transformation, $arguments);
    }
    /**
     * Returns transformed definition.
     */
    public function get_definition(): Definition
    {
        return $this->definition;
    }
}