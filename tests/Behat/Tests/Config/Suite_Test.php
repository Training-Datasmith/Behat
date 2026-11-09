<?php

declare(strict_types=1);

namespace Behat\Tests\Config;

use Behat\Config\Filter\Name_Filter;
use Behat\Config\Filter\Tag_Filter;
use Behat\Config\Suite;
use Behat\Testwork\Service_Container\Exception\Configuration_Loading_Exception;
use PHPUnit\Framework\TestCase;

final class Suite_Test extends TestCase
{
    public function testSuiteCanBeConvertedIntoAnArray(): void
    {
        $suite = new Suite('first');

        $this->assertIsArray($suite->to_array());
    }

    public function testItReturnsSettings(): void
    {
        $settings = [
            'contexts' => [
                'First_Context',
            ],
        ];

        $suite = new Suite('first', $settings);

        $this->assertEquals($settings, $suite->to_array());
    }

    public function testAddingContexts(): void
    {
        $config = new Suite('first');
        $config->with_contexts('First_Context', 'Second_Context');
        $config->add_context('Third_Context', ['http://localhost:8080', '/var/tmp']);

        $this->assertEquals([
            'contexts' => [
                'First_Context',
                'Second_Context',
                [
                    'Third_Context' => [
                        'http://localhost:8080',
                        '/var/tmp',
                    ],
                ],
            ],
        ], $config->to_array());
    }

    public function testAddingPaths(): void
    {
        $config = new Suite('first');
        $config->with_paths('features/admin/first', 'features/front/first');
        $config->with_paths('features/api/first');

        $this->assertEquals([
            'paths' => [
                'features/admin/first',
                'features/front/first',
                'features/api/first',
            ],
        ], $config->to_array());
    }

    public function testAddingFilters(): void
    {
        $config = new Suite('first');
        $config->with_filter(new Tag_Filter('tag1'));
        $config->with_filter(new Name_Filter('name1'));

        $this->assertEquals([
            'filters' => [
                'tags' => 'tag1',
                'name' => 'name1',
            ],
        ], $config->to_array());
    }

    public function testItThrowsAnExceptionWhenAddingExistingFilter(): void
    {
        $suite = new Suite('first');

        $suite->with_filter(new Tag_Filter('tag1'));

        $this->expectException(Configuration_Loading_Exception::class);
        $this->expectExceptionMessage('The filter "tags" already exists.');

        $suite->with_filter(new Tag_Filter('tag1'));
    }
}
