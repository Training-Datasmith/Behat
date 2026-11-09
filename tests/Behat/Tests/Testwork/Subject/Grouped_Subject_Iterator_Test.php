<?php

declare(strict_types=1);

namespace Behat\Tests\Testwork\Subject;

use Behat\Testwork\Specification\Grouped_Specification_Iterator;
use Behat\Testwork\Specification\No_Specifications_Iterator;
use Behat\Testwork\Specification\Specification_Array_Iterator;
use Behat\Testwork\Specification\Specification_Iterator;
use Behat\Testwork\Suite\Suite;
use PHPUnit\Framework\TestCase;

class Grouped_Subject_Iterator_Test extends TestCase
{
    public function testIterationWithEmptyAtBeginning(): void
    {
        $suite = $this->createStub(Suite::class);
        $subIterator = $this->createStub(Specification_Iterator::class);

        $iterator = new Grouped_Specification_Iterator($suite, [
            new No_Specifications_Iterator($suite),
            new Specification_Array_Iterator($suite, [$subIterator]),
        ]);

        $this->assertEquals(1, iterator_count($iterator));
    }

    public function testIterationWithEmptyInMiddle(): void
    {
        $suite = $this->createStub(Suite::class);
        $subIterator = $this->createStub(Specification_Iterator::class);

        $iterator = new Grouped_Specification_Iterator($suite, [
            new Specification_Array_Iterator($suite, [$subIterator]),
            new No_Specifications_Iterator($suite),
            new Specification_Array_Iterator($suite, [$subIterator]),
        ]);

        $this->assertEquals(2, iterator_count($iterator));
    }
}
