<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet;

use Behat\Behat\Context\Snippet\Generator\Cannot_Generate_Step_Pattern_Exception;
use Behat\Behat\Snippet\Generator\Snippet_Generator;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Acts like a snippet repository by producing snippets from registered undefined steps using snippet generators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Snippet_Registry implements Snippet_Repository
{
    /**
     * @var Snippet_Generator[]
     */
    private array $generators = [];
    /**
     * @var Undefined_Step[]
     */
    private array $undefined_steps = [];
    /**
     * @var Aggregate_Snippet[]
     */
    private array $snippets = [];
    private bool $snippets_generated = false;
    /**
     * @var list<Cannot_Generate_Step_Pattern_Exception>
     */
    private array $exceptions = [];

    /**
     * Registers snippet generator.
     */
    public function register_snippet_generator(Snippet_Generator $generator): void
    {
        $this->generators[] = $generator;
        $this->snippets_generated = false;
    }

    /**
     * Generates and registers snippet.
     */
    public function register_undefined_step(Environment $environment, StepNode $step): void
    {
        $this->undefined_steps[] = new Undefined_Step($environment, $step);
        $this->snippets_generated = false;
    }

    /**
     * Returns all generated snippets.
     *
     * @return Aggregate_Snippet[]
     */
    public function get_snippets()
    {
        $this->generate_snippets();

        return $this->snippets;
    }

    /**
     * Returns steps for which there was no snippet generated.
     *
     * @return Undefined_Step[]
     */
    public function get_undefined_steps()
    {
        $this->generate_snippets();

        return $this->undefined_steps;
    }

    /**
     * @return list<Cannot_Generate_Step_Pattern_Exception>
     */
    public function get_generation_failures(): array
    {
        $this->generate_snippets();

        return $this->exceptions;
    }

    /**
     * Generates snippets for undefined steps.
     */
    private function generate_snippets(): void
    {
        if ($this->snippets_generated) {
            return;
        }
        $snippets_set = [];
        foreach ($this->undefined_steps as $i => $undefined_step) {
            try {
                $snippet = $this->generate_snippet($undefined_step->get_environment(), $undefined_step->get_step());
            } catch (Cannot_Generate_Step_Pattern_Exception $e) {
                $this->exceptions[] = $e;
                unset($this->undefined_steps[$i]);
                continue;
            }
            if (!$snippet) {
                continue;
            }
            if (!isset($snippets_set[$snippet->get_hash()])) {
                $snippets_set[$snippet->get_hash()] = [];
            }
            $snippets_set[$snippet->get_hash()][] = $snippet;
            unset($this->undefined_steps[$i]);
        }
        $this->snippets = array_values(array_map(fn (array $snippets): Aggregate_Snippet => new Aggregate_Snippet($snippets), $snippets_set));
        $this->undefined_steps = array_values($this->undefined_steps);
        $this->snippets_generated = true;
    }

    /**
     * @return Snippet|null
     */
    private function generate_snippet(Environment $environment, StepNode $step)
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports_environment_and_step($environment, $step)) {
                return $generator->generate_snippet($environment, $step);
            }
        }

        return null;
    }
}
