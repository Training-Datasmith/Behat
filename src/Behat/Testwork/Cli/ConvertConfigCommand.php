<?php

declare (strict_types=1);
/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Behat\Testwork\Cli;

use Behat\Behat\Util\Strict_Regex;
use Behat\Config\Config;
use Behat\Config\Converter\Config_Converter_Tools;
use Behat\Config\Converter\Custom_Pretty_Printer;
use Behat\Config\Converter\Used_Classes_Collector;
use Behat\Testwork\Service_Container\Configuration\Configuration_Loader;
use Behat\Testwork\Service_Container\Exception\Configuration_Loading_Exception;
use Php_Parser\Node\Stmt\Nop;
use Php_Parser\Node\Stmt\Return_;
use Php_Parser\Node_Traverser;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Yaml\Yaml;
final class Convert_Config_Command extends Base_Command
{
    private Output_Interface $output;
    public function __construct(private readonly Configuration_Loader $configuration_loader)
    {
        parent::__construct('convert-config');
    }
    protected function execute(Input_Interface $input, Output_Interface $output): int
    {
        $this->output = $output;
        $config_path = $this->configuration_loader->get_configuration_file_path();
        if (str_ends_with((string) $config_path, '.php')) {
            throw new Configuration_Loading_Exception(sprintf('Configuration file `%s` is already in PHP format', $config_path));
        }
        $this->output->writeln(['', 'Starting conversion']);
        $this->convert_file($config_path);
        $this->output->writeln(['Conversion finished', '']);
        return 0;
    }
    private function convert_file(string $file_path): string
    {
        $this->output->writeln('Converting configuration file: ' . $file_path);
        $yaml_config = (array) Yaml::parse(file_get_contents($file_path));
        if (isset($yaml_config[Config::IMPORTS_SETTING])) {
            $imports = $yaml_config[Config::IMPORTS_SETTING];
            $base_path = rtrim(dirname($file_path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $new_imports = [];
            foreach ($imports as $import) {
                $import = $base_path . $import;
                $new_imports[] = substr($this->convert_file($import), strlen($base_path));
            }
            $yaml_config[Config::IMPORTS_SETTING] = $new_imports;
        }
        $config = new Config($yaml_config);
        $expr = $config->to_php_expr();
        $config_stms = [new Return_($expr)];
        $traverser = new Node_Traverser();
        $used_classses_collector = new Used_Classes_Collector();
        $traverser->add_visitor($used_classses_collector);
        $config_stms = $traverser->traverse($config_stms);
        $use_stmts = [];
        $used_classes = $used_classses_collector->get_used_classes();
        sort($used_classes);
        foreach ($used_classes as $used_class) {
            $use_stmts[] = Config_Converter_Tools::create_use_statement($used_class);
        }
        if ($use_stmts !== []) {
            $use_stmts[] = new Nop();
        }
        $stmts = [...$use_stmts, ...$config_stms, new Nop()];
        // Find the name of the output file
        $output_file_name = $file_path;
        // First, handle cases where `.dist` is part of the extension
        $output_file_name = Strict_Regex::replace('/(\.ya?ml)\.dist$|\.dist\.(ya?ml)$/', '.dist.php', $output_file_name);
        // Then, handle regular `.yaml` or `.yml` files
        $output_file_name = Strict_Regex::replace('/\.(ya?ml)$/', '.php', $output_file_name);
        $printer = new Custom_Pretty_Printer();
        file_put_contents($output_file_name, $printer->pretty_print_file($stmts));
        unlink($file_path);
        return $output_file_name;
    }
}