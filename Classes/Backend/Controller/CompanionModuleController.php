<?php

declare(strict_types=1);

namespace Cru\A11yCompanion\Backend\Controller;

use Cru\A11yCompanion\Repository\ImageRepository;
use Cru\A11yCompanion\Service\ProvideParsedLinkListService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;

class CompanionModuleController extends ActionController
{
    private ImageRepository $imageRepository;

    public function __construct(
        ImageRepository $imageRepository,
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly ProvideParsedLinkListService $provideParsedLinkListService
    ) {
        $this->imageRepository = $imageRepository;
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);

        return $this->indexAction($request);
    }

    private function setUpMenu(ServerRequestInterface $request, ModuleTemplate $moduleTemplate): void
    {
        $menu = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('a11y_companion_menu');

        $menuItems = [
            'index' => [
                'controller' => 'Module',
                'action' => 'index',
                'route' => 'tx_a11y_companion_index',
                'label' => 'A11y Companion',
            ],
            'list_alt' => [
                'controller' => 'Module',
                'action' => 'listImagesWithoutAltText',
                'route' => 'tx_a11y_companion_list_alt',
                'label' => 'List Missing Alt Text',
            ],
            'list_link' => [
                'controller' => 'Module',
                'action' => 'listLinksWithoutPurpose',
                'route' => 'tx_a11y_companion_list_link',
                'label' => 'List Links without Purpose',
            ],
        ];

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        foreach ($menuItems as $menuItemConfig) {
            $currentUri = $request->getUri();
            $action = $menuItemConfig['route'];
            $uri = $uriBuilder->buildUriFromRoute($action, [$request]);
            $isActive = ($currentUri->getPath() === $uri->getPath());
            $menuItem = $menu->makeMenuItem()
                            ->setTitle($menuItemConfig['label'])
                            ->setHref($uri)
                            ->setActive($isActive);
            $menu->addMenuItem($menuItem);
        }
        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    public function indexAction(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);

        $moduleTemplate->setTitle('a11y Companion');
        return $moduleTemplate->renderResponse('Index');
    }

    public function listImagesWithoutAltTextAction(\TYPO3\CMS\Core\Http\ServerRequest $request, int $itemsPerPage = 10): ResponseInterface
    {
        
        $images = $this->imageRepository->findImagesWithoutAltText();

        $pagginationData = $this->handlePagination($request, $itemsPerPage, $images, 'tx_a11y_companion_list_alt');
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);

        $moduleTemplate->setTitle('Missing Alt Text Images');

        $moduleTemplate->assignMultiple([
            'pagination' => $pagginationData['pagination'],
            'paginator' => $pagginationData['paginator'],
            'pageLinks' => $pagginationData['pageLinks'],
            'currentPage' => $pagginationData['currentPage'],
            'firstPage' => $pagginationData['firstPage'],
            'lastPage' => $pagginationData['lastPage'],
            'pageCount' => $pagginationData['pageCount'],
            'prevLink' => $pagginationData['prevLink'],
            'nextLink' => $pagginationData['nextLink'],
            'firstLink' => $pagginationData['firstLink'],
            'lastLink' => $pagginationData['lastLink'],
        ]);

        return $moduleTemplate->renderResponse('AltText/List');
    }

    public function listLinksWithoutPurpose(\TYPO3\CMS\Core\Http\ServerRequest $request, int $itemsPerPage = 10): ResponseInterface
    {
        $result = $this->provideParsedLinkListService->getConfiguration();
        $allLinks = [];
        foreach ($result['links'] as $linksPerRecord) {
            foreach ($linksPerRecord as $link) {
                $allLinks[] = $link;
            }
        }
        $pagginationData = $this->handlePagination($request, $itemsPerPage, $allLinks);

        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        $this->setUpMenu($request, $moduleTemplate);
        $moduleTemplate->setTitle('Missing Link Purpose');

        $moduleTemplate->assignMultiple([
            'pagination' => $pagginationData['pagination'],
            'paginator' => $pagginationData['paginator'],
            'pageLinks' => $pagginationData['pageLinks'],
            'currentPage' => $pagginationData['currentPage'],
            'firstPage' => $pagginationData['firstPage'],
            'lastPage' => $pagginationData['lastPage'],
            'pageCount' => $pagginationData['pageCount'],
            'prevLink' => $pagginationData['prevLink'],
            'nextLink' => $pagginationData['nextLink'],
            'firstLink' => $pagginationData['firstLink'],
            'lastLink' => $pagginationData['lastLink'],
        ]);

        return $moduleTemplate->renderResponse('Links/List');
    }

    private function handlePagination(
        ServerRequestInterface $request,
        int $itemsPerPage,
        array $items,
        string $routeName = 'tx_a11y_companion_list_link'
    ): array {
        if (isset($request->getQueryParams()['currentPage'])) {
            $currentPage = (int)$request->getQueryParams()['currentPage'];
        } else {
            $currentPage = 1;
        }
        if ($currentPage < 1) {
            $currentPage = 1;
        }

        $paginator = new ArrayPaginator($items, $currentPage, $itemsPerPage);
        $pagination = new SlidingWindowPagination(
            $paginator,
            10 // Number of pages to show in the sliding window
        );

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $firstPage = $pagination->getFirstPageNumber();
        $lastPage = $pagination->getLastPageNumber();
        $pageCount = $paginator->getNumberOfPages();

        $pageLinks = $this->buildPageLinks($currentPage, $pageCount, 10, $uriBuilder, $routeName);

        $prevPage = $currentPage > 1 ? $currentPage - 1 : null;
        $nextPage = $currentPage < $pageCount ? $currentPage + 1 : null;
        $prevLink = $prevPage ? (string)$uriBuilder->buildUriFromRoute($routeName, ['currentPage' => $prevPage]) : null;
        $nextLink = $nextPage ? (string)$uriBuilder->buildUriFromRoute($routeName, ['currentPage' => $nextPage]) : null;
        $firstLink = $firstPage ? (string)$uriBuilder->buildUriFromRoute($routeName, ['currentPage' => $firstPage]) : null;
        $lastLink = $lastPage ? (string)$uriBuilder->buildUriFromRoute($routeName, ['currentPage' => $lastPage]) : null;

        return [
            'pagination' => $pagination,
            'paginator' => $paginator,
            'currentPage' => $currentPage,
            'firstPage' => $firstPage,
            'lastPage' => $lastPage,
            'pageCount' => $pageCount,
            'pageLinks' => $pageLinks,
            'prevLink' => $prevLink,
            'nextLink' => $nextLink,
            'firstLink' => $firstLink,
            'lastLink' => $lastLink,
        ];
    }

    /**
     * Build an array of page links for the sliding window pagination.
     *
     * @param int $currentPage
     * @param int $pageCount
     * @param int $windowSize
     * @param UriBuilder $uriBuilder
     * @param string $routeName
     * @return array
     */
    private function buildPageLinks(int $currentPage, int $pageCount, int $windowSize, $uriBuilder, string $routeName): array
    {
        $halfWindow = (int)floor($windowSize / 2);
        if ($pageCount <= $windowSize) {
            $startPage = 1;
            $endPage = $pageCount;
        } else {
            if ($currentPage <= $halfWindow) {
                $startPage = 1;
                $endPage = $windowSize;
            } elseif ($currentPage + $halfWindow > $pageCount) {
                $startPage = $pageCount - $windowSize + 1;
                $endPage = $pageCount;
            } else {
                $startPage = $currentPage - $halfWindow;
                $endPage = $currentPage + $halfWindow;
                if ($windowSize % 2 === 0) {
                    $endPage -= 1;
                }
            }
        }
        $startPage = max(1, $startPage);
        $endPage = min($pageCount, $endPage);
        $pageLinks = [];
        for ($i = $startPage; $i <= $endPage; $i++) {
            $pageLinks[$i] = (string)$uriBuilder->buildUriFromRoute(
                $routeName,
                ['currentPage' => $i]
            );
        }
        return $pageLinks;
    }
}
