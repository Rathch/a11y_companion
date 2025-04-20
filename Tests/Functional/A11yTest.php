<?php

declare(strict_types=1);

namespace Cru\A11yCompanion\Tests\Functional;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use DOMDocument;

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

    protected function loadHtmlFixture(string $path): DOMDocument
    {
        $dom = new DOMDocument();
        $dom->loadHTMLFile($path);
        return $dom;
    }

    #[Test]
    public function testImagesHaveAltText(): void
    {
        $dom = $this->loadHtmlFixture($this->htmlFixturePath);
        $images = $dom->getElementsByTagName('img');
        
        $imagesWithoutAlt = 0;
        foreach ($images as $img) {
            if (!$img->hasAttribute('alt')) {
                $imagesWithoutAlt++;
            }
        }
        
        self::assertEquals(1, $imagesWithoutAlt, 'Expected exactly one image without alt text in test fixture');
    }

    #[Test]
    public function testButtonsHaveContent(): void
    {
        $dom = $this->loadHtmlFixture($this->htmlFixturePath);
        $buttons = $dom->getElementsByTagName('button');
        
        $emptyButtons = 0;
        foreach ($buttons as $button) {
            if (empty($button->textContent) && !$button->hasAttribute('aria-label')) {
                $emptyButtons++;
            }
        }
        
        self::assertEquals(1, $emptyButtons, 'Expected exactly one empty button in test fixture');
    }

    #[Test]
    public function testHeadingStructure(): void
    {
        $dom = $this->loadHtmlFixture($this->htmlFixturePath);
        $headings = $dom->getElementsByTagName('h3');
        
        $improperHeadings = 0;
        foreach ($headings as $h3) {
            $prevSibling = $h3->previousSibling;
            while ($prevSibling && !($prevSibling instanceof \DOMElement && preg_match('/^h[1-6]$/', $prevSibling->tagName))) {
                $prevSibling = $prevSibling->previousSibling;
            }
            
            if (!$prevSibling || $prevSibling->tagName !== 'h2') {
                $improperHeadings++;
            }
        }
        
        self::assertEquals(1, $improperHeadings, 'Expected exactly one improper heading structure in test fixture');
    }

    #[Test]
    public function testFormInputsHaveLabels(): void
    {
        $dom = $this->loadHtmlFixture($this->formFixturePath);
        $inputs = $dom->getElementsByTagName('input');
        
        $inputsWithoutLabels = 0;
        foreach ($inputs as $input) {
            if ($input->getAttribute('type') !== 'submit' && !$this->hasAssociatedLabel($input)) {
                $inputsWithoutLabels++;
            }
        }
        
        self::assertEquals(2, $inputsWithoutLabels, 'Expected exactly two inputs without labels in invalid form');
    }

    #[Test]
    public function testAriaLandmarksPresent(): void
    {
        $dom = $this->loadHtmlFixture($this->formFixturePath);
        $landmarks = [
            'banner' => false,
            'navigation' => false,
            'main' => false,
            'complementary' => false
        ];
        
        $elements = $dom->getElementsByTagName('*');
        foreach ($elements as $element) {
            $role = $element->getAttribute('role');
            if (isset($landmarks[$role])) {
                $landmarks[$role] = true;
            }
        }
        
        self::assertTrue($landmarks['banner'], 'Banner landmark not found');
        self::assertTrue($landmarks['navigation'], 'Navigation landmark not found');
        self::assertTrue($landmarks['main'], 'Main landmark not found');
        self::assertTrue($landmarks['complementary'], 'Complementary landmark not found');
    }

    private function hasAssociatedLabel(\DOMElement $input): bool
    {
        $id = $input->getAttribute('id');
        if (empty($id)) {
            return false;
        }
        
        $labels = $input->ownerDocument->getElementsByTagName('label');
        foreach ($labels as $label) {
            if ($label->getAttribute('for') === $id) {
                return true;
            }
        }
        
        return false;
    }
}