<?php

declare(strict_types=1);

namespace Behat\Tests\Config;

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Testwork\Service_Container\Exception\Configuration_Loading_Exception;
use PHPUnit\Framework\TestCase;

final class Config_Test extends TestCase
{
    public function testConfigCanBeConvertedIntoAnArray(): void
    {
        $config = new Config();

        $this->assertIsArray($config->to_array());
    }

    public function testItReturnsSettings(): void
    {
        $settings = [
            'default' => [
                'gherkin' => [
                    'filters' => [
                        'tags' => '~@test',
                    ],
                ],
            ],
        ];

        $config = new Config($settings);

        $this->assertEquals($settings, $config->to_array());
    }

    public function testAddingImports(): void
    {
        $config = new Config();
        $config
            ->import('config/first_suite.php')
            ->import('config/second_suite.php')
        ;

        $this->assertEquals([
            'imports' => [
                'config/first_suite.php',
                'config/second_suite.php',
            ],
        ], $config->to_array());
    }

    public function testAddingProfile(): void
    {
        $config = new Config();

        $config->with_profile(new Profile('default', [
            'extensions' => [
                'some_extension' => [],
            ],
        ]));

        $this->assertEquals([
            'default' => [
                'extensions' => [
                    'some_extension' => [],
                ],
            ],
        ], $config->to_array());
    }

    public function testItThrowsAnExceptionWhenAddingExistingProfile(): void
    {
        $config = new Config();

        $config->with_profile(new Profile('default'));

        $this->expectException(Configuration_Loading_Exception::class);
        $this->expectExceptionMessage('The profile "default" already exists.');

        $config->with_profile(new Profile('default', [
            'extensions' => [
                'some_extension' => [],
            ],
        ]));
    }
}
