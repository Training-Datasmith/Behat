<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat\Context\Snippet\Generator;

use Behat\Behat\Context\Environment\Context_Environment;
use Behat\Behat\Context\Snippet\Context_Snippet;
use Behat\Behat\Definition\Call as DefinitionCall;
use Behat\Behat\Definition\Pattern\Pattern_Transformer;
use Behat\Behat\Definition\Pattern\Simple_Step_Method_Name_Suggester;
use Behat\Behat\Snippet\Exception\Environment_Snippet_Generation_Exception;
use Behat\Behat\Snippet\Generator\Snippet_Generator;
use Behat\Behat\Snippet\Snippet;
use Behat\Behat\Tester\Exception\Pending_Exception;
use Behat\Behat\Util\Strict_Regex;
use Behat\Gherkin\Node\Argument_Interface;
use Behat\Gherkin\Node\Py_String_Node;
use Behat\Gherkin\Node\Step_Node;
use Behat\Gherkin\Node\Table_Node;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Behat\Testwork\Environment\Environment;
use ReflectionClass;
/**
 * Generates snippets for a context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Context_Snippet_Generator implements Snippet_Generator
{
    /**
     * @var array<string, array<string, string>>
     */
    private static array $proposed_methods = [];
    private static string $snippet_template = <<<TPL
        #[%%s('%s')]
        public function %s(%s): void
        {
            throw new PendingException();
        }
    TPL;
    private Target_Context_Identifier $context_identifier;
    private Pattern_Identifier $pattern_identifier;
    /**
     * Initializes snippet generator.
     */
    public function __construct(private readonly Pattern_Transformer $pattern_transformer)
    {
        $this->set_context_identifier(new Fixed_Context_Identifier());
        $this->set_pattern_identifier(new Fixed_Pattern_Identifier());
    }
    /**
     * Sets target context identifier.
     */
    public function set_context_identifier(Target_Context_Identifier $identifier): void
    {
        $this->context_identifier = new Cached_Context_Identifier($identifier);
    }
    /**
     * Sets target pattern type identifier.
     */
    public function set_pattern_identifier(Pattern_Identifier $identifier): void
    {
        $this->pattern_identifier = $identifier;
    }
    public function supports_environment_and_step(Environment $environment, Step_Node $step): bool
    {
        if (!$environment instanceof Context_Environment) {
            return false;
        }
        if (!$environment->has_contexts()) {
            return false;
        }
        return null !== $this->context_identifier->guess_target_context_class($environment);
    }
    public function generate_snippet(Environment $environment, Step_Node $step): Snippet
    {
        if (!$environment instanceof Context_Environment) {
            throw new Environment_Snippet_Generation_Exception(sprintf('ContextSnippetGenerator does not support `%s` environment.', $environment::class), $environment);
        }
        $context_class = $this->context_identifier->guess_target_context_class($environment);
        $pattern_type = $this->pattern_identifier->guess_pattern_type($context_class);
        $step_text = $step->get_text();
        $pattern = $this->pattern_transformer->generate_pattern($pattern_type, $step_text);
        if ($this->pattern_transformer->match_pattern($pattern->get_pattern(), $step_text) === false) {
            // The generated pattern does not actually match the step text. For example because the step
            // text contains literal characters that are treated as placeholders or similar.
            throw new Cannot_Generate_Step_Pattern_Exception($step_text);
        }
        $method_name = $this->get_unique_method_name($context_class, $pattern->get_pattern(), $pattern->get_suggested_method_name() ?: Simple_Step_Method_Name_Suggester::DEFAULT_NAME);
        $method_arguments = $this->get_method_arguments($step, $pattern->get_placeholder_count());
        $snippet_template = $this->get_snippet_template($pattern->get_pattern(), $method_name, $method_arguments);
        $used_classes = $this->get_used_classes($step);
        return new Context_Snippet($step, $snippet_template, $context_class, $used_classes);
    }
    /**
     * Returns an array of method argument names from step and token count.
     *
     * @return string[]
     */
    private function get_method_arguments(Step_Node $step, int $token_count): array
    {
        $args = [];
        for ($i = 0; $i < $token_count; ++$i) {
            $args[] = '$arg' . ($i + 1);
        }
        foreach ($step->get_arguments() as $argument) {
            $args[] = $this->get_method_argument($argument);
        }
        return $args;
    }
    /**
     * Returns an array of classes used by the snippet template.
     *
     * @return string[]
     */
    private function get_used_classes(Step_Node $step): array
    {
        $used_classes = [Pending_Exception::class];
        $keyword_type = $step->get_keyword_type();
        assert($keyword_type === Definition_Call\Given::KEYWORD || $keyword_type === Definition_Call\When::KEYWORD || $keyword_type === Definition_Call\Then::KEYWORD);
        $used_classes[] = match ($keyword_type) {
            Definition_Call\Given::KEYWORD => Given::class,
            Definition_Call\When::KEYWORD => When::class,
            Definition_Call\Then::KEYWORD => Then::class,
        };
        foreach ($step->get_arguments() as $argument) {
            $used_classes[] = $argument::class;
        }
        return $used_classes;
    }
    /**
     * Generates snippet template using regex, method name and arguments.
     *
     * @param string[] $methodArguments
     */
    private function get_snippet_template(string $pattern, string $method_name, array $method_arguments): string
    {
        return sprintf(self::$snippet_template, $this->prepare_pattern($pattern), $method_name, implode(', ', $method_arguments));
    }
    private function prepare_pattern(string $pattern): string
    {
        $pattern = str_replace('%', '%%', $pattern);
        $pattern = str_replace('\\\\', '\\\\\\\\', $pattern);
        return str_replace("'", "\\'", $pattern);
    }
    /**
     * Ensures uniqueness of the method name in the context.
     */
    private function get_unique_method_name(string $context_class, string $step_pattern, string $name): string
    {
        $reflection = new ReflectionClass($context_class);
        $number = $this->get_method_number_from_the_method_name($name);
        [$name, $number] = $this->get_method_name_not_existent_in_context($reflection, $name, $number);
        return $this->get_method_name_not_proposed_earlier($context_class, $step_pattern, $name, $number);
    }
    /**
     * Tries to deduct method number from the provided method name.
     */
    private function get_method_number_from_the_method_name(string $method_name): int
    {
        if (preg_match('/(\d+)$/', $method_name, $matches)) {
            return intval($matches[1]);
        }
        return 2;
    }
    /**
     * Tries to guess method name that is not yet defined in the context class.
     *
     * @return array{string, int}
     */
    private function get_method_name_not_existent_in_context(ReflectionClass $reflection, string $method_name, int $method_number): array
    {
        while ($reflection->has_method($method_name)) {
            $method_name = Strict_Regex::replace('/\d+$/', '', $method_name);
            $method_name .= $method_number++;
        }
        return [$method_name, $method_number];
    }
    /**
     * Tries to guess method name that is not yet proposed to the context class.
     */
    private function get_method_name_not_proposed_earlier(string $context_class, string $step_pattern, string $name, int $number): string
    {
        foreach ($this->get_already_proposed_methods($context_class) as $proposed_pattern => $proposed_method) {
            if ($proposed_pattern === $step_pattern) {
                continue;
            }
            while ($proposed_method === $name) {
                $name = Strict_Regex::replace('/\d+$/', '', $name);
                $name .= $number++;
            }
        }
        $this->mark_method_as_already_proposed($context_class, $step_pattern, $name);
        return $name;
    }
    /**
     * Returns already proposed method names.
     *
     * @return array<string, string>
     */
    private function get_already_proposed_methods(string $context_class): array
    {
        return self::$proposed_methods[$context_class] ?? [];
    }
    /**
     * Marks method as proposed one.
     */
    private function mark_method_as_already_proposed(string $context_class, string $step_pattern, string $method_name): void
    {
        self::$proposed_methods[$context_class][$step_pattern] = $method_name;
    }
    private function get_method_argument(Argument_Interface $argument): string
    {
        return match (true) {
            $argument instanceof Py_String_Node => 'PyStringNode $string',
            $argument instanceof Table_Node => 'TableNode $table',
            default => '__unknown__',
        };
    }
}