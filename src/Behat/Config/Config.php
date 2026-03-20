<?php

declare (strict_types=1);
namespace Behat\Config;

use Behat\Config\Converter\Config_Converter_Tools;
use Behat\Testwork\Service_Container\Exception\Configuration_Loading_Exception;
use function is_string;
use Php_Parser\Node\Expr;
final class Config implements Config_Interface, Config_Converter_Interface
{
    public const IMPORTS_SETTING = 'imports';
    private const PREFERRED_PROFILE_NAME_SETTING = 'preferredProfileName';
    private const IMPORT_FUNCTION = 'import';
    private const PROFILE_FUNCTION = 'withProfile';
    private const PREFERRED_PROFILE_FUNCTION = 'withPreferredProfile';
    public function __construct(private array $settings = [])
    {
    }
    /** @param string|string[] $resource **/
    public function import(string|array $resource): self
    {
        $resources = is_string($resource) ? [$resource] : $resource;
        foreach ($resources as $resource) {
            $this->settings[self::IMPORTS_SETTING][] = $resource;
        }
        return $this;
    }
    public function with_profile(Profile $profile): self
    {
        if (array_key_exists($profile->name(), $this->settings)) {
            throw new Configuration_Loading_Exception(sprintf('The profile "%s" already exists.', $profile->name()));
        }
        $this->settings[$profile->name()] = $profile->to_array();
        return $this;
    }
    public function with_preferred_profile(string $profile_name): self
    {
        $this->settings[self::PREFERRED_PROFILE_NAME_SETTING] = $profile_name;
        return $this;
    }
    public function to_array(): array
    {
        return $this->settings;
    }
    /**
     * @internal
     */
    public function to_php_expr(): Expr
    {
        $config_object = Config_Converter_Tools::create_object(self::class);
        $expr = $config_object;
        foreach ($this->settings as $settings_name => $settings) {
            if ($settings_name === self::PREFERRED_PROFILE_NAME_SETTING) {
                $expr = Config_Converter_Tools::add_method_call(self::class, self::PREFERRED_PROFILE_FUNCTION, [$settings], $expr);
            } elseif ($settings_name === self::IMPORTS_SETTING) {
                $arguments = count($settings) === 1 ? [$settings[0]] : [$settings];
                $expr = Config_Converter_Tools::add_method_call(self::class, self::IMPORT_FUNCTION, $arguments, $expr);
            } else {
                $profile = new Profile($settings_name, $settings ?? []);
                $expr = Config_Converter_Tools::add_method_call(self::class, self::PROFILE_FUNCTION, [$profile->to_php_expr()], $expr);
            }
            unset($this->settings[$settings_name]);
        }
        return $expr;
    }
}