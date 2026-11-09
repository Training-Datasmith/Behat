<?php

declare(strict_types=1);

namespace Behat\Tests\Definition\Pattern;

use Behat\Behat\Definition\Exception\Unknown_Pattern_Exception;
use Behat\Behat\Definition\Pattern\Pattern_Transformer;
use Behat\Behat\Definition\Pattern\Policy\Pattern_Policy;
use PHPUnit\Framework\TestCase;

/**
 * @author Julien Deniau <julien.deniau@mapado.com>
 */
class Pattern_Transformer_Test extends TestCase
{
    public function testTransformPatternToRegexCache(): void
    {
        $policy = $this->createStub(Pattern_Policy::class);
        $policy->method('supportsPattern')
            ->willReturn(true);
        $policy->method('transformPatternToRegex')
            ->will($this->returnValueMap([
                ['hello world', '/hello world/'],
                ['hi world', '/hi world/'],
            ]));

        $testedInstance = new Pattern_Transformer();
        $testedInstance->register_pattern_policy($policy);

        $regex = $testedInstance->transform_pattern_to_regex('hello world');
        $regex2 = $testedInstance->transform_pattern_to_regex('hello world');
        $regex3 = $testedInstance->transform_pattern_to_regex('hi world');

        $this->assertEquals('/hello world/', $regex);
        $this->assertEquals('/hello world/', $regex2);
        $this->assertEquals('/hi world/', $regex3);
    }

    public function testTransformPatternToRegexCacheAndRegisterNewPolicy(): void
    {
        // first pattern
        $policy1 = $this->createMock(Pattern_Policy::class);
        $policy1->method('supportsPattern')->willReturn(true);
        $policy1->expects($this->exactly(2))
            ->method('transformPatternToRegex')
            ->with($this->equalTo('hello world'))
            ->willReturn('/hello world/');

        // second pattern
        $policy2 = $this->createMock(Pattern_Policy::class);
        $policy2->expects($this->never())->method('supportsPattern');
        $policy2->expects($this->never())->method('transformPatternToRegex');

        $testedInstance = new Pattern_Transformer();
        $testedInstance->register_pattern_policy($policy1);
        $regex = $testedInstance->transform_pattern_to_regex('hello world');
        $regex2 = $testedInstance->transform_pattern_to_regex('hello world');

        $testedInstance->register_pattern_policy($policy2);
        $regex3 = $testedInstance->transform_pattern_to_regex('hello world');

        $this->assertEquals('/hello world/', $regex);
        $this->assertEquals('/hello world/', $regex2);
        $this->assertEquals('/hello world/', $regex3);
    }

    public function testTransformPatternToRegexNoMatch(): void
    {
        $policy = $this->createMock(Pattern_Policy::class);
        $policy->method('supportsPattern')
            ->with($this->equalTo('hello world'))
            ->willReturn(false);
        $policy->expects($this->never())
            ->method('transformPatternToRegex');

        $testedInstance = new Pattern_Transformer();
        $testedInstance->register_pattern_policy($policy);

        $this->expectException(Unknown_Pattern_Exception::class);
        $this->expectExceptionMessage('Can not find policy for a pattern `hello world`.');
        $regex = $testedInstance->transform_pattern_to_regex('hello world');
    }
}
