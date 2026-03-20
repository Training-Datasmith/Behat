<?php

declare (strict_types=1);
/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Behat;

use Behat\Behat\Context\Service_Container\Context_Extension;
use Behat\Behat\Definition\Service_Container\Definition_Extension;
use Behat\Behat\Event_Dispatcher\Service_Container\Event_Dispatcher_Extension;
use Behat\Behat\Gherkin\Service_Container\Gherkin_Extension;
use Behat\Behat\Helper_Container\Service_Container\Helper_Container_Extension;
use Behat\Behat\Hook\Service_Container\Hook_Extension;
use Behat\Behat\Output\Service_Container\Formatter\Json_Formatter_Factory;
use Behat\Behat\Output\Service_Container\Formatter\J_Unit_Formatter_Factory;
use Behat\Behat\Output\Service_Container\Formatter\Pretty_Formatter_Factory;
use Behat\Behat\Output\Service_Container\Formatter\Progress_Formatter_Factory;
use Behat\Behat\Snippet\Service_Container\Snippet_Extension;
use Behat\Behat\Tester\Service_Container\Tester_Extension;
use Behat\Behat\Transformation\Service_Container\Transformation_Extension;
use Behat\Behat\Translator\Service_Container\Gherkin_Translations_Extension;
use Behat\Testwork\Application_Factory as BaseFactory;
use Behat\Testwork\Argument\Service_Container\Argument_Extension;
use Behat\Testwork\Autoloader\Service_Container\Autoloader_Extension;
use Behat\Testwork\Call\Service_Container\Call_Extension;
use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Deprecation\Service_Container\Deprecation_Extension;
use Behat\Testwork\Environment\Service_Container\Environment_Extension;
use Behat\Testwork\Exception\Service_Container\Exception_Extension;
use Behat\Testwork\Filesystem\Service_Container\Filesystem_Extension;
use Behat\Testwork\Ordering\Service_Container\Ordering_Extension;
use Behat\Testwork\Output\Service_Container\Formatter\Formatter_Factory;
use Behat\Testwork\Output\Service_Container\Output_Extension;
use Behat\Testwork\Path_Options\Service_Container\Path_Options_Extension;
use Behat\Testwork\Service_Container\Service_Processor;
use Behat\Testwork\Specification\Service_Container\Specification_Extension;
use Behat\Testwork\Suite\Service_Container\Suite_Extension;
use Behat\Testwork\Translator\Service_Container\Translator_Extension;
use Composer\Installed_Versions;
/**
 * Defines the way behat is created.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Application_Factory extends Base_Factory
{
    /**
     * @deprecated this constant will not be updated for releases after 3.13.0 and will be removed in the next major.
     * You can use composer's runtime API to get the behat version if you need it - see getVersion() in this class for
     * an example. Note that composer's versions will not always be simple numeric values.
     */
    public const VERSION = '3.13.0';
    protected function get_name(): string
    {
        return 'behat';
    }
    protected function get_version()
    {
        // Get the currently installed behat version from composer's runtime API
        return Installed_Versions::get_version('behat/behat');
    }
    protected function get_default_extensions(): array
    {
        $processor = new Service_Processor();
        return [new Argument_Extension(), new Autoloader_Extension(['' => '%paths.base%/features/bootstrap']), new Suite_Extension($processor), new Output_Extension('pretty', $this->get_default_formatter_factories($processor), $processor), new Path_Options_Extension(), new Exception_Extension($processor), new Gherkin_Extension($processor), new Call_Extension($processor), new Translator_Extension(), new Gherkin_Translations_Extension(), new Tester_Extension($processor), new Cli_Extension($processor), new Environment_Extension($processor), new Specification_Extension($processor), new Filesystem_Extension(), new Context_Extension($processor), new Snippet_Extension($processor), new Definition_Extension($processor), new Event_Dispatcher_Extension($processor), new Hook_Extension(), new Transformation_Extension($processor), new Ordering_Extension($processor), new Helper_Container_Extension($processor), new Deprecation_Extension()];
    }
    protected function get_environment_variable_name(): string
    {
        return 'BEHAT_PARAMS';
    }
    protected function get_config_path(): ?string
    {
        $cwd = rtrim(getcwd(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $config_dir = $cwd . 'config' . DIRECTORY_SEPARATOR;
        $paths = [$cwd . 'behat.yaml', $cwd . 'behat.yml', $cwd . 'behat.yaml.dist', $cwd . 'behat.yml.dist', $cwd . 'behat.dist.yaml', $cwd . 'behat.dist.yml', $cwd . 'behat.php', $cwd . 'behat.dist.php', $config_dir . 'behat.yaml', $config_dir . 'behat.yml', $config_dir . 'behat.yaml.dist', $config_dir . 'behat.yml.dist', $config_dir . 'behat.dist.yaml', $config_dir . 'behat.dist.yml', $config_dir . 'behat.php', $config_dir . 'behat.dist.php'];
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return null;
    }
    /**
     * Returns default formatter factories.
     *
     * @return list<FormatterFactory>
     */
    private function get_default_formatter_factories(Service_Processor $processor): array
    {
        return [new Pretty_Formatter_Factory($processor), new Progress_Formatter_Factory($processor), new J_Unit_Formatter_Factory(), new Json_Formatter_Factory()];
    }
}