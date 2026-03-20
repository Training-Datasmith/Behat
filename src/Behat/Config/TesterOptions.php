<?php

declare (strict_types=1);
namespace Behat\Config;

use Behat\Config\Converter\Config_Converter_Tools;
use Php_Parser\Node\Expr;
final class Tester_Options implements Config_Converter_Interface
{
    private const TESTERS_SETTINGS_GROUP = 'testers';
    private const CALLS_SETTINGS_GROUP = 'calls';
    private const DEPRECATIONS_SETTINGS_GROUP = 'deprecations';
    private const STRICT_SETTING = 'strict';
    private const STOP_ON_FAILURE_SETTING = 'stop_on_failure';
    private const SKIP_SETTING = 'skip';
    private const ERROR_REPORTING_SETTING = 'error_reporting';
    private const PRINT_BEHAT_DEPRECATIONS_SETTING = 'print_behat_deprecations';
    private const FAIL_ON_BEHAT_DEPRECATIONS_SETTING = 'fail_on_behat_deprecations';
    private const FUNCTION_NAMES_PER_SETTING = [self::CALLS_SETTINGS_GROUP => [self::ERROR_REPORTING_SETTING => 'withErrorReporting'], self::TESTERS_SETTINGS_GROUP => [self::STOP_ON_FAILURE_SETTING => 'withStopOnFailure', self::SKIP_SETTING => 'withSkipAllTests', self::STRICT_SETTING => 'withStrictResultInterpretation'], self::DEPRECATIONS_SETTINGS_GROUP => [self::PRINT_BEHAT_DEPRECATIONS_SETTING => 'withPrintBehatDeprecations', self::FAIL_ON_BEHAT_DEPRECATIONS_SETTING => 'withFailOnBehatDeprecations']];
    /**
     * Used when converting config to PHP, to move relevant Profile settings into this object.
     *
     * @internal
     */
    public static function consume_settings_from_profile(array &$profile_settings): ?self
    {
        // These settings live under multiple array keys. We don't want to expose/couple these details to the Profile,
        // but we need the Profile to be able to detect whether there *are* any TesterOptions to convert.
        // Build an array of relevant settings groups. Remove any that are found from the settings array in the Profile
        // object, so that the config converter recognises they have been handled.
        $settings = [];
        foreach ([self::TESTERS_SETTINGS_GROUP, self::CALLS_SETTINGS_GROUP, self::DEPRECATIONS_SETTINGS_GROUP] as $group) {
            if (isset($profile_settings[$group])) {
                $settings[$group] = $profile_settings[$group];
                unset($profile_settings[$group]);
            }
        }
        if ($settings === []) {
            // There are no relevant settings, so the config does not need to call ->withTesterOptions on the Profile
            return null;
        }
        return new self($settings);
    }
    public function __construct(private array $settings = [])
    {
    }
    public function to_array(): array
    {
        return $this->settings;
    }
    /**
     * Behat will convert PHP warnings / errors during steps to exceptions if they match this error_reporting level.
     */
    public function with_error_reporting(int $error_reporting): self
    {
        $this->settings[self::CALLS_SETTINGS_GROUP][self::ERROR_REPORTING_SETTING] = $error_reporting;
        return $this;
    }
    /**
     * Control whether Behat should fail on undefined or pending steps (equivalent to the `--strict` CLI flag).
     */
    public function with_strict_result_interpretation(bool $strict = true): self
    {
        $this->settings[self::TESTERS_SETTINGS_GROUP][self::STRICT_SETTING] = $strict;
        return $this;
    }
    /**
     * Control whether Behat should actually execute steps (equivalent to the `--dry-run` CLI flag).
     */
    public function with_skip_all_tests(bool $skip = true): self
    {
        $this->settings[self::TESTERS_SETTINGS_GROUP][self::SKIP_SETTING] = $skip;
        return $this;
    }
    /**
     * Control whether Behat should stop after the first failing scenario (equivalent to `--stop-on-failure` on CLI).
     */
    public function with_stop_on_failure(bool $stop_on_failure = true): self
    {
        $this->settings[self::TESTERS_SETTINGS_GROUP][self::STOP_ON_FAILURE_SETTING] = $stop_on_failure;
        return $this;
    }
    /**
     * Control whether Behat should print deprecation warnings at the end of the test run.
     */
    public function with_print_behat_deprecations(bool $print_deprecations = true): self
    {
        $this->settings[self::DEPRECATIONS_SETTINGS_GROUP][self::PRINT_BEHAT_DEPRECATIONS_SETTING] = $print_deprecations;
        return $this;
    }
    /**
     * Control whether Behat should exit with an error code if deprecations were triggered.
     */
    public function with_fail_on_behat_deprecations(bool $fail_on_deprecations = true): self
    {
        $this->settings[self::DEPRECATIONS_SETTINGS_GROUP][self::FAIL_ON_BEHAT_DEPRECATIONS_SETTING] = $fail_on_deprecations;
        return $this;
    }
    public function to_php_expr(): Expr
    {
        $expr = $options_object = Config_Converter_Tools::create_object(self::class);
        // Convert recognised settings to their setter functions
        foreach ($this->settings as $group => $group_settings) {
            foreach ($group_settings as $setting_name => $setting) {
                $function_name = self::FUNCTION_NAMES_PER_SETTING[$group][$setting_name] ?? null;
                if ($setting_name === self::ERROR_REPORTING_SETTING) {
                    $setting = Config_Converter_Tools::error_reporting_to_constants($setting);
                }
                if ($function_name) {
                    $expr = Config_Converter_Tools::add_method_call(self::class, $function_name, [$setting], $expr);
                    unset($this->settings[$group][$setting_name]);
                }
            }
        }
        // Remove any now-empty groups (e.g. 'testers' => []) from the settings array
        // Then if necessary provide any remaining settings to the object constructor. These are likely not used by
        // Behat, but we want them in the generated config for the user to review.
        $this->settings = array_filter($this->settings, static fn($g): bool => $g !== []);
        if ($this->settings !== []) {
            Config_Converter_Tools::add_arguments_to_constructor([$this->settings], $options_object);
        }
        return $expr;
    }
}