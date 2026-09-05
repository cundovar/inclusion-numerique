<?php

declare(strict_types=1);

namespace App\Application\AgentCourse\Handler;

use App\Application\AgentCourse\Command\CreateAgentCourseCommand;
use App\Application\AgentCourse\DTO\AgentCourseDTO;
use App\Domain\Category\Repository\CategoryRepositoryInterface;
use App\Domain\CourseLevel\Repository\CourseLevelRepositoryInterface;
use App\Domain\Menu\Repository\MenuRepositoryInterface;
use App\Domain\Page\Repository\PageRepositoryInterface;
use App\Domain\PageContent\Repository\PageContentRepositoryInterface;
use App\Domain\Shared\Persistence\TransactionalExecutorInterface;
use App\Entity\Menus;
use App\Entity\Page;
use App\Entity\PageContent;
use InvalidArgumentException;

final class CreateAgentCourseHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private CourseLevelRepositoryInterface $courseLevelRepository,
        private MenuRepositoryInterface $menuRepository,
        private PageRepositoryInterface $pageRepository,
        private PageContentRepositoryInterface $pageContentRepository,
        private TransactionalExecutorInterface $transactionalExecutor
    ) {}

    public function handle(CreateAgentCourseCommand $command): AgentCourseDTO
    {
        $this->assertCommand($command);

        $category = $this->categoryRepository->findByName($command->technology);
        if ($category === null) {
            throw new InvalidArgumentException('La technologie n\'a pas été trouvée');
        }

        $level = $this->courseLevelRepository->findByName($command->level);
        if ($level === null) {
            throw new InvalidArgumentException('Le niveau n\'a pas été trouvé');
        }

        return $this->transactionalExecutor->run(function () use ($command, $category, $level): AgentCourseDTO {
            $menu = $command->menuId !== null
                ? $this->menuRepository->findById($command->menuId)
                : null;

            if ($command->menuId !== null && $menu === null) {
                throw new InvalidArgumentException('Le menu n\'a pas été trouvé');
            }

            if ($menu !== null && $menu->getCategory()?->getId() !== $category->getId()) {
                throw new InvalidArgumentException('Le menu n\'appartient pas à la catégorie demandée');
            }

            if ($menu !== null && $menu->getNiveauCours()?->getId() !== $level->getId()) {
                throw new InvalidArgumentException('Le menu n\'appartient pas au niveau demandé');
            }

            if ($menu === null) {
                $menu = new Menus();
                $menu->setLabel($command->newMenuLabel ?: $command->title);
                $menu->setCategory($category);
                $menu->setNiveauCours($level);
                $this->menuRepository->save($menu);
            }

            $page = new Page();
            $page->setSlug($this->generateUniqueSlug($command->title));
            $page->setMenus($menu);
            $this->pageRepository->save($page);

            $pageContent = new PageContent();
            $pageContent->setTitle($command->title);
            $pageContent->setType('agent-cours');
            $pageContent->setContent($command->description ?: sprintf('Cours %s généré automatiquement', $command->title));
            $pageContent->setCode($command->codeHtml);
            $pageContent->setDuration($command->duration);
            $pageContent->setPage($page);
            $pageContent->setCategory($category);
            $pageContent->setMenu($menu);
            $pageContent->setNiveauCours($level);
            $pageContent->setVisible($command->status === 'publie');
            $this->pageContentRepository->save($pageContent);

            return new AgentCourseDTO(
                id: (int) $pageContent->getId(),
                pageId: (int) $page->getId(),
                menuId: (int) $menu->getId(),
                slug: (string) $page->getSlug(),
                title: (string) $pageContent->getTitle(),
                technology: (string) $category->getName(),
                level: (string) $level->getName(),
                status: $command->status,
                visible: (bool) $pageContent->isVisible()
            );
        });
    }

    private function assertCommand(CreateAgentCourseCommand $command): void
    {
        if (trim($command->title) === '') {
            throw new InvalidArgumentException('Le titre est requis');
        }

        if (trim($command->technology) === '') {
            throw new InvalidArgumentException('La technologie est requise');
        }

        if (trim($command->level) === '') {
            throw new InvalidArgumentException('Le niveau est requis');
        }

        if (trim($command->duration) === '') {
            throw new InvalidArgumentException('La durée est requise');
        }

        if (trim($command->codeHtml) === '') {
            throw new InvalidArgumentException('Le contenu HTML du cours est requis');
        }
    }

    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = '/' . trim((string) preg_replace(
            '/^-|-$/',
            '',
            (string) preg_replace(
                '/[^a-z0-9]+/',
                '-',
                strtolower((string) preg_replace('/[\x{0300}-\x{036f}]/u', '', (string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title)))
            )
        ));

        $slug = $baseSlug === '/' ? '/cours' : $baseSlug;
        $index = 2;

        while ($this->pageRepository->findBySlug($slug) !== null) {
            $slug = sprintf('%s-%d', $baseSlug === '/' ? '/cours' : $baseSlug, $index);
            $index++;
        }

        return $slug;
    }
}
