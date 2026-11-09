<?php

declare(strict_types=1);

namespace Behat\Tests\Config;

use Behat\Config\Extension;
use Behat\Config\Filter\Name_Filter;
use Behat\Config\Filter\Tag_Filter;
use Behat\Config\Formatter\J_Unit_Formatter;
use Behat\Config\Formatter\Pretty_Formatter;
use Behat\Config\Formatter\Progress_Formatter;
use Behat\Config\Formatter\Show_Output_Option;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Testwork\Output\Printer\Factory\Output_Factory;
use Behat\Testwork\Service_Container\Exception\Configuration_Loading_Exception;
use PHPUnit\Framework\TestCase;

final class Profile_Test extends TestCase
{
    public function testProfileCanBeConvertedIntoAnArray(): void
    {
        $profile = new Profile('default');

        $this->assertIsArray($profile->to_array());
    }

    public function testItReturnsSettings(): void
    {
        $settings = [
            'extensions' => [
                'some_extension' => [],
            ],
        ];

        $profile = new Profile('default', $settings);

        $this->assertEquals($settings, $profile->to_array());
    }

    public function testAddingExtensions(): void
    {
        $profile = new Profile('default');
        $profile->with_extension(new Extension('Behat\MinkExtension', ['base_url' => 'https://127.0.0.1:8080']));
        $profile->with_extension(new Extension('FriendsOfBehat\MinkDebugExtension', ['directory' => 'etc/build']));

        $this->assertEquals([
            'extensions' => [
                'Behat\MinkExtension' => [
                    'base_url' => 'https://127.0.0.1:8080',
                ],
                'FriendsOfBehat\MinkDebugExtension' => [
                    'directory' => 'etc/build',
                ],
            ],
        ], $profile->to_array());
    }

    public function testItThrowsAnExceptionWhenAddingExistingExtension(): void
    {
        $profile = new Profile('default');

        $profile->with_extension(new Extension('custom_extension'));

        $this->expectException(Configuration_Loading_Exception::class);
        $this->expectExceptionMessage('The extension "custom_extension" already exists.');

        $profile->with_extension(new Extension('custom_extension'));
    }

    public function testAddingSuites(): void
    {
        $profile = new Profile('default');
        $profile
            ->with_suite(new Suite('admin_dashboard'))
            ->with_suite(new Suite('managing_administrators'))
        ;

        $this->assertEquals([
            'suites' => [
                'admin_dashboard' => [],
                'managing_administrators' => [],
            ],
        ], $profile->to_array());
    }

    public function testItThrowsAnExceptionWhenAddingExistingSuite(): void
    {
        $profile = new Profile('default');

        $profile->with_suite(new Suite('admin_dashboard'));

        $this->expectException(Configuration_Loading_Exception::class);
        $this->expectExceptionMessage('The suite "admin_dashboard" already exists.');

        $profile->with_suite(new Suite('admin_dashboard'));
    }

    public function testAddingFilters(): void
    {
        $profile = new Profile('default');
        $profile
            ->with_filter(new Tag_Filter('admin'))
            ->with_filter(new Name_Filter('Managing administrators'))
        ;

        $this->assertEquals([
            'gherkin' => [
                'filters' => [
                    'tags' => 'admin',
                    'name' => 'Managing administrators',
                ],
            ],
        ], $profile->to_array());
    }

    public function testItThrowsAnExceptionWhenAddingExistingFilter(): void
    {
        $profile = new Profile('default');

        $profile->with_filter(new Tag_Filter('tag1'));

        $this->expectException(Configuration_Loading_Exception::class);
        $this->expectExceptionMessage('The filter "tags" already exists.');

        $profile->with_filter(new Tag_Filter('tag1'));
    }

    public function testAddingFormatters(): void
    {
        $profile = new Profile('default');

        $profile
            ->with_formatter((new Pretty_Formatter(expand: true, paths: false))->with_output_verbosity(Output_Factory::VERBOSITY_VERBOSE))
            ->with_formatter(new Progress_Formatter(timer: false))
            ->with_formatter(new J_Unit_Formatter())
        ;

        $this->assertEquals([
            'formatters' => [
                'pretty' => [
                    'timer' => true,
                    'expand' => true,
                    'paths' => false,
                    'multiline' => true,
                    'output_verbosity' => 2,
                    'show_output' => 'yes',
                    'short_summary' => true,
                    'print_skipped_steps' => true,
                ],
                'progress' => [
                    'timer' => false,
                    'show_output' => 'in-summary',
                    'short_summary' => false,
                ],
                'junit' => [
                    'timer' => true,
                ],
            ],
        ], $profile->to_array());
    }

    public function testDisablingFormatters(): void
    {
        $profile = new Profile('default');

        $profile->disable_formatter(Pretty_Formatter::NAME);
        $profile->disable_formatter(Progress_Formatter::NAME);
        $profile->disable_formatter(J_Unit_Formatter::NAME);

        $this->assertEquals([
            'formatters' => [
                'pretty' => false,
                'progress' => false,
                'junit' => false,
            ],
        ], $profile->to_array());
    }

    public function testSettingShowOutputOption(): void
    {
        $profile = new Profile('default');

        $profile->disable_formatter(Pretty_Formatter::NAME);
        $profile->disable_formatter(J_Unit_Formatter::NAME);
        $profile->with_formatter(new Progress_Formatter(showOutput: Show_Output_Option::Yes));

        $this->assertEquals([
            'formatters' => [
                'pretty' => false,
                'progress' => [
                    'timer' => true,
                    'show_output' => 'yes',
                    'short_summary' => false,
                ],
                'junit' => false,
            ],
        ], $profile->to_array());
    }
}
