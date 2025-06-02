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

    public function listImagesWithoutAltTextAction($request, int $currentPage = 1, int $itemsPerPage = 10): ResponseInterface
    {
        $offset = ($currentPage - 1) * $itemsPerPage;
        $images = $this->imageRepository->findImagesWithoutAltText($offset, $itemsPerPage);
        $totalImages = $this->imageRepository->countImagesWithoutAltText();
        $totalPages = (int)ceil($totalImages / $itemsPerPage);

        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);

        $moduleTemplate->setTitle('Missing Alt Text Images');

        $moduleTemplate->assignMultiple([
            'images' => $images,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
        ]);

        return $moduleTemplate->renderResponse('AltText/List');
    }

    public function listLinksWithoutPurpose($request, int $currentPage = 1, int $itemsPerPage = 10): ResponseInterface
    {
        $offset = ($currentPage - 1) * $itemsPerPage;
        $images = $this->imageRepository->findImagesWithoutAltText($offset, $itemsPerPage);
        $totalImages = $this->imageRepository->countImagesWithoutAltText();
        $totalPages = (int)ceil($totalImages / $itemsPerPage);

        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $this->setUpMenu($request, $moduleTemplate);

        $moduleTemplate->setTitle('Missing Link Purpose');

        $moduleTemplate->assignMultiple([
            'links' => $this->provideParsedLinkListService->getConfiguration(),
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
        ]);

        return $moduleTemplate->renderResponse('Links/List');
    }
}
