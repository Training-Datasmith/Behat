<?php

declare (strict_types=1);
namespace Behat\Config;

use Behat\Config\Converter\Config_Converter_Tools;
use Behat\Testwork\Service_Container\Extension_Manager;
final readonly class Extension implements Extension_Config_Interface
{
    public function __construct(private string $name, private array $settings = [])
    {
    }
    public function name(): string
    {
        return $this->name;
    }
    public function to_array(): array
    {
        return $this->settings;
    }
    public function to_php_expr(): \Php_Parser\Node\Expr\New_
    {
        $extension_object = Config_Converter_Tools::create_object(self::class);
        $name = $this->name;
        if (!class_exists($name)) {
            // It might be a shorthand reference to the extension - attempt to convert to an FQCN
            $full_name = Extension_Manager::guess_full_extension_class_name($name);
            if (class_exists($full_name)) {
                $name = $full_name;
            }
        }
        $arguments = $this->settings === [] ? [$name] : [$name, $this->settings];
        Config_Converter_Tools::add_arguments_to_constructor($arguments, $extension_object);
        return $extension_object;
    }
}