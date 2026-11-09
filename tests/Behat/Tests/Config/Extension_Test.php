<?php

declare(strict_types=1);

namespace Behat\Tests\Config;

use Behat\Config\Extension;
use PHPUnit\Framework\TestCase;

final class Extension_Test extends TestCase
{
    public function testExtensionCanBeConvertedIntoAnArray(): void
    {
        $extension = new Extension('custom_extension');

        $this->assertIsArray($extension->to_array());
    }

    public function testItReturnsSettings(): void
    {
        $settings = [
            'base_url' => 'https://127.0.0.1:8080',
        ];

        $extension = new Extension('Behat\MinkExtension', $settings);

        $this->assertEquals($settings, $extension->to_array());
    }
}
