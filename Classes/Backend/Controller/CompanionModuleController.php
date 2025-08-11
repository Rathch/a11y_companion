<?php

// SPDX-FileCopyrightText: 2025 Christian Rath-Ulrich
//
// SPDX-License-Identifier: GPL-3.0-or-later
/*
 * This file is part of the package cru/a11y-companion.
 *
 * Copyright (C) 2024 -2025 Christian Rath-Ulrich
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Cru\A11yCompanion\Backend\Controller;

use Cru\A11yCompanion\Repository\ImageRepository;
use Cru\A11yCompanion\Service\ProvideParsedLinkListService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
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
        $currentPage = $this->getValidatedCurrentPage($request);

        $paginator = new ArrayPaginator($items, $currentPage, $itemsPerPage);
        $pagination = new SlidingWindowPagination($paginator, 10);

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $paginationData = $this->buildPaginationData($currentPage, $paginator, $pagination, $uriBuilder, $routeName);

        return array_merge($paginationData, [
            'pagination' => $pagination,
            'paginator' => $paginator,
        ]);
    }

    private function getValidatedCurrentPage(ServerRequestInterface $request): int
    {
        $currentPage = $request->getQueryParams()['currentPage'] ?? 1;
        return max(1, (int)$currentPage);
    }

    private function buildPaginationData(
        int $currentPage,
        ArrayPaginator $paginator,
        SlidingWindowPagination $pagination,
        UriBuilder $uriBuilder,
        string $routeName
    ): array {
        $pageCount = $paginator->getNumberOfPages();
        $firstPage = $pagination->getFirstPageNumber();
        $lastPage = $pagination->getLastPageNumber();

        $navigationLinks = $this->buildNavigationLinks($currentPage, $pageCount, $firstPage, $lastPage, $uriBuilder, $routeName);

        return [
            'currentPage' => $currentPage,
            'firstPage' => $firstPage,
            'lastPage' => $lastPage,
            'pageCount' => $pageCount,
            'pageLinks' => $this->buildPageLinks($currentPage, $pageCount, 10, $uriBuilder, $routeName),
            ...$navigationLinks,
        ];
    }

    private function buildNavigationLinks(
        int $currentPage,
        int $pageCount,
        int $firstPage,
        int $lastPage,
        UriBuilder $uriBuilder,
        string $routeName
    ): array {
        $prevPage = $this->getPreviousPage($currentPage);
        $nextPage = $this->getNextPage($currentPage, $pageCount);

        return [
            'prevLink' => $this->buildPageLink($prevPage, $uriBuilder, $routeName),
            'nextLink' => $this->buildPageLink($nextPage, $uriBuilder, $routeName),
            'firstLink' => $this->buildPageLink($firstPage, $uriBuilder, $routeName),
            'lastLink' => $this->buildPageLink($lastPage, $uriBuilder, $routeName),
        ];
    }

    private function getPreviousPage(int $currentPage): ?int
    {
        return $currentPage > 1 ? $currentPage - 1 : null;
    }

    private function getNextPage(int $currentPage, int $pageCount): ?int
    {
        return $currentPage < $pageCount ? $currentPage + 1 : null;
    }

    private function buildPageLink(?int $pageNumber, UriBuilder $uriBuilder, string $routeName): ?string
    {
        if ($pageNumber === null) {
            return null;
        }

        return (string)$uriBuilder->buildUriFromRoute($routeName, ['currentPage' => $pageNumber]);
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
        $pageRange = $this->calculatePageRange($currentPage, $pageCount, $windowSize);

        return $this->generatePageLinks($pageRange, $uriBuilder, $routeName);
    }

    private function calculatePageRange(int $currentPage, int $pageCount, int $windowSize): array
    {
        $halfWindow = (int)floor($windowSize / 2);

        if ($pageCount <= $windowSize) {
            return ['start' => 1, 'end' => $pageCount];
        }

        if ($currentPage <= $halfWindow) {
            return ['start' => 1, 'end' => $windowSize];
        }

        if ($currentPage + $halfWindow > $pageCount) {
            return ['start' => $pageCount - $windowSize + 1, 'end' => $pageCount];
        }

        $startPage = $currentPage - $halfWindow;
        $endPage = $currentPage + $halfWindow;

        if ($windowSize % 2 === 0) {
            $endPage -= 1;
        }

        return ['start' => $startPage, 'end' => $endPage];
    }

    private function generatePageLinks(array $pageRange, UriBuilder $uriBuilder, string $routeName): array
    {
        $startPage = max(1, $pageRange['start']);
        $endPage = $pageRange['end'];

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
