<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Snippet\Generator;

use Behat\Behat\Context\Snippet\Generator\Cannot_Generate_Step_Pattern_Exception;
use Behat\Behat\Snippet\Snippet;
use Behat\Behat\Snippet\Snippet_Registry;
use Behat\Gherkin\Node\Step_Node;
use Behat\Testwork\Environment\Environment;
/**
 * Generates snippet for a specific step in a specific environment.
 *
 * @see SnippetRegistry
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Snippet_Generator
{
    /**
     * Checks if generator supports search query.
     *
     * @return bool
     */
    public function supports_environment_and_step(Environment $environment, Step_Node $step);
    /**
     * Generates snippet from search.
     *
     * @return Snippet
     *
     * @throws CannotGenerateStepPatternException if the step text cannot be automatically converted to a pattern
     */
    public function generate_snippet(Environment $environment, Step_Node $step);
}