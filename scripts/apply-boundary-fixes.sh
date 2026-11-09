#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

perl -pi -e '
  s/\$definition->getType\(\)/\$definition->get_type()/g;
  s/\$snippet->getSteps\(\)/\$snippet->get_steps()/g;
  s/return \$this->output->is_verbose\(\)/return \$this->output->isVerbose()/g;
' \
  src/Behat/Behat/Definition/Printer/Console_Definition_Printer.php \
  src/Behat/Behat/Context/Snippet/Appender/Context_Snippet_Appender.php

perl -pi -e '
  s/\$node->setAttribute\(\$name, \$value \?\? '"'"''"'"'\)/\$node->setAttribute(\$name, \$value === null ? '"'"''"'"' : (string) \$value)/g;
' src/Behat/Testwork/Output/Printer/J_Unit_Output_Printer.php

perl -pi -e '
  s/Container_Exception_Interface/ContainerExceptionInterface/g;
  s/Not_Found_Exception_Interface/NotFoundExceptionInterface/g;
  s/^use InvalidArgumentException;/use InvalidArgumentException;\nuse Psr\\Container\\NotFoundExceptionInterface;/ if $.==1..1;
' src/Behat/Behat/Helper_Container/Exception/Service_Not_Found_Exception.php

perl -pi -e '
  s/interface Helper_Container_Exception extends Container_Exception_Interface/interface Helper_Container_Exception extends ContainerExceptionInterface/;
' src/Behat/Behat/Helper_Container/Exception/Helper_Container_Exception.php

grep -q 'use Psr\\Container\\ContainerExceptionInterface' src/Behat/Behat/Helper_Container/Exception/Helper_Container_Exception.php || \
  perl -pi -e 's/use Behat\\Testwork\\Environment\\Exception\\Environment_Exception;/use Behat\\Testwork\\Environment\\Exception\\Environment_Exception;\nuse Psr\\Container\\ContainerExceptionInterface;/' \
    src/Behat/Behat/Helper_Container/Exception/Helper_Container_Exception.php

perl -pi -e '
  s/->method_call\(/->methodCall(/g;
  s/->class_const_fetch\(/->classConstFetch(/g;
  s/->const_fetch\(/->constFetch(/g;
  s/new Fully_Qualified\(/new FullyQualified(/g;
  s/use PhpParser\\Node\\Expr\\New;/use PhpParser\\Node\\Expr\\New_;/;
  s/use PhpParser\\Node\\Stmt\\Use;/use PhpParser\\Node\\Stmt\\Use_;/;
  s/->get_node\(\)/->getNode()/g;
' src/Behat/Config/Converter/Config_Converter_Tools.php

grep -q 'use PhpParser\\Node\\Name\\FullyQualified' src/Behat/Config/Converter/Config_Converter_Tools.php || \
  perl -pi -e 's/use PhpParser\\Node\\Expr\\New_;/use PhpParser\\Node\\Expr\\New_;\nuse PhpParser\\Node\\Name\\FullyQualified;/' \
    src/Behat/Config/Converter/Config_Converter_Tools.php

# find_class_file path resolution is maintained in Suite_With_Contexts_Setup.php (PSR-4 empty prefix + flat Studly_Snake files).

perl -0777 -pi -e 's/use Symfony\\\\Contracts\\\\Translation\\\\TranslatorInterface;\n\n/\n/s; s/extends TranslatorInterface/extends \\\\Symfony\\\\Contracts\\\\Translation\\\\TranslatorInterface/' \
  src/Behat/Behat/Definition/Translator/Translator_Interface.php

perl -pi -e '
  s/\$scenario->getTitle\(\)/\$scenario->getName()/g;
' \
  src/Behat/Behat/Output/Node/Printer/JSON/JSON_Scenario_Printer.php \
  src/Behat/Behat/Output/Node/Printer/J_Unit/J_Unit_Scenario_Printer.php

perl -pi -e '
  s/\@Before_Feature/\@BeforeFeature/g;
  s/\@After_Feature/\@AfterFeature/g;
  s/\@Before_Scenario/\@BeforeScenario/g;
  s/\@After_Scenario/\@AfterScenario/g;
  s/\@Before_Step/\@BeforeStep/g;
  s/\@After_Step/\@AfterStep/g;
' tests/Fixtures/Hooks/features/bootstrap/Feature_Context_Annotations.php


perl -pi -e '
  s/public function enter_node/public function enterNode/;
  s/->to_string\(\)/->toString()/g;
  s/->get_last\(\)/->getLast()/g;
  s/->get_attributes\(\)/->getAttributes()/g;
' src/Behat/Config/Converter/Used_Classes_Collector.php

cat > src/Behat/Config/Converter/Custom_Pretty_Printer.php <<'PHP'
<?php

declare(strict_types=1);

namespace Behat\Config\Converter;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\PrettyPrinter\Standard;

class Custom_Pretty_Printer extends Standard
{
    protected function pExpr_Array(Array_ $node): string
    {
        $printedArray = '[';
        $printedArray .= $this->pCommaSeparatedMultiline($node->items, true);

        return $printedArray . ($this->nl . ']');
    }

    protected function pExpr_MethodCall(MethodCall $node): string
    {
        $result = $this->pDereferenceLhs($node->var);
        $this->indent();
        $result .= $this->nl . '->' . $this->pObjectProperty($node->name) . '(';
        if (count($node->args) > 1) {
            $result .= $this->pCommaSeparatedMultiline($node->args, false) . $this->nl;
        } else {
            $result .= $this->pCommaSeparated($node->args);
        }
        $result .= ')';
        $this->outdent();

        return $result;
    }
}
PHP

cat > src/Behat/Testwork/Exception/Stringer/PHP_Unit_Exception_Stringer.php <<'PHP'
<?php

declare(strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception\Stringer;

use Exception;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Util\ThrowableToStringMapper;
use PHPUnit_Framework_Exception;
use PHPUnit_Framework_TestFailure;
use Throwable;

/**
 * Strings PHPUnit assertion exceptions.
 *
 * @see Exception_Presenter
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PHP_Unit_Exception_Stringer implements Exception_Stringer
{
    public function supports_exception(Exception $exception): bool
    {
        return $exception instanceof PHPUnit_Framework_Exception || $exception instanceof \PHPUnit\Framework\Exception;
    }

    public function string_exception(Exception $exception, $verbosity): string
    {
        try {
            if (class_exists(ThrowableToStringMapper::class)) {
                return trim(ThrowableToStringMapper::map($exception));
            }
            if (class_exists(TestFailure::class)) {
                return trim(TestFailure::exceptionToString($exception));
            }
            if (class_exists(PHPUnit_Framework_TestFailure::class)) {
                return trim(PHPUnit_Framework_TestFailure::exceptionToString($exception));
            }

            return sprintf(<<<TEXT
            %s
            !! Could not render more details of this %s.
               Behat does not support automatically formatting assertion failures for your PHPUnit version.
               See %s for details.
            TEXT, $exception->getMessage(), $exception::class, self::class);
        } catch (Throwable $phpunit_exception) {
            return sprintf(<<<TEXT
            %s
            !! There was an error trying to render more details of this %s.
               You are probably using a PHPUnit version that Behat cannot automatically display failures for.
               See %s for details of PHPUnit support.
               [%s] %s at %s:%s
            TEXT, $exception->getMessage(), $exception::class, self::class, $phpunit_exception::class, $phpunit_exception->getMessage(), $phpunit_exception->getFile(), $phpunit_exception->getLine());
        }
    }
}
PHP

find tests/Fixtures -name '*.php' -print0 | xargs -0 perl -pi -e '
  s/\bgetConfigKey\b/get_config_key/g;
  s/\binitializeContext\b/initialize_context/g;
  s/\bgetAcceptedSnippetType\b/get_accepted_snippet_type/g;
  s/\bhandleNonExistentClass\b/handle_non_existent_class/g;
  s/\bhandleNonExistentMethod\b/handle_non_existent_method/g;
  s/Behat\\Behat\\HelperContainer\\/Behat\\Behat\\Helper_Container\\/g;
'

echo "apply-boundary-fixes: done"
