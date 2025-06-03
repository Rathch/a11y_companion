<?php

declare(strict_types=1);

namespace Cru\A11yCompanion\Tests\Functional;

use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(A11yTest::class)]
final class A11yTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['cru/a11y-companion'];
    protected string $htmlFixturePath;
    protected string $formFixturePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->htmlFixturePath = __DIR__ . '/Fixtures/Html/basic-content.html';
        $this->formFixturePath = __DIR__ . '/Fixtures/Html/form-content.html';
    }

    protected function loadHtmlFixture(string $path): \DOMDocument
    {
        $dom = new \DOMDocument();
        $dom->loadHTMLFile($path);
        return $dom;
    }

    #[Test]
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
