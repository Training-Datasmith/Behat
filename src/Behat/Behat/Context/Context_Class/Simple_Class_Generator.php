<?php

declare(strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Context_Class;

use Behat\Testwork\Suite\Suite;

/**
 * Generates basic PHP 5.3+ class with an optional namespace.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Simple_Class_Generator implements Class_Generator
{
    private static string $template = <<<'PHP'
    <?php
    
    {namespace}use Behat\Behat\Context\Context;
    use Behat\Gherkin\Node\PyStringNode;
    use Behat\Gherkin\Node\TableNode;
    
    /**
     * Defines application features from the specific context.
     */
    class {className} implements Context
    {
        /**
         * Initializes context.
         *
         * Every scenario gets its own context instance.
         * You can also pass arbitrary arguments to the
         * context constructor through behat.yml.
         */
        public function __construct()
        {
        }
    }
    
    PHP;

    public function supports_suite_and_class(Suite $suite, $context_class): bool
    {
        return true;
    }

    public function generate_class(Suite $suite, $context_class): string
    {
        $fqn = $context_class;
        $namespace = '';
        if (false !== $pos = strrpos((string) $fqn, '\\')) {
            $namespace = 'namespace ' . substr((string) $fqn, 0, $pos) . ";\n\n";
            $context_class = substr((string) $fqn, $pos + 1);
        }

        return strtr(self::$template, ['{namespace}' => $namespace, '{className}' => $context_class]);
    }
}
