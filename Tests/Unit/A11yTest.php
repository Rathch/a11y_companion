<?php

declare(strict_types=1);

namespace Cru\A11yCompanion\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(A11yTest::class)]
final class A11yTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function testExample(): void 
    {
        self::assertTrue(true);
    }
}