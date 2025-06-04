# A11y Companion

A11y Companion is a TYPO3 extension that helps editors and administrators improve and monitor the accessibility of TYPO3 websites.

## Main Features

### 1. Detect images without alt text
The extension scans all images used in the system and lists those missing an alternative text (alt attribute). Missing alt texts are a common accessibility issue and make content less accessible, especially for screen reader users. The extension allows you to quickly identify and fix these images. Images can also be marked as "decorative" if they do not provide meaningful content.

### 2. Detect links without clear purpose
The extension analyzes all links in the system and checks whether their purpose is clearly recognizable to users. Unclear or generic link texts (such as "click here") are listed so they can be replaced with meaningful alternatives. This improves orientation and usability for all users.

### 3. Support for large data sets
Thanks to performant sliding window pagination, even with hundreds of images or links, only the relevant page links are shown. This keeps the backend clear and fast.

### 4. TYPO3 standards and extensibility
The extension uses only official TYPO3 APIs and is compatible with TYPO3 v13. It is inspired by [require-alt-text](https://github.com/plan2net/require-alt-text/tree/1.0.0) and [z7-semantilizer](https://github.com/zeroseven/z7_semantilizer), but extends these approaches with a modern, maintainable architecture and better TYPO3 backend integration.

## Benefits
- Quickly identify and fix accessibility issues
- Ensure legal compliance (e.g. BITV, WCAG)
- Improved user experience for all visitors
- Clear and performant backend modules

## Installation
Install the extension via Composer:

```bash
composer require cru/a11y-companion
```

Then activate it in the TYPO3 backend.

## License
GPL-2.0-or-later

---

Inspired by:
- [require-alt-text](https://github.com/plan2net/require-alt-text/tree/1.0.0)
- [zeroseven/z7-semantilizer](https://github.com/zeroseven/z7_semantilizer)
