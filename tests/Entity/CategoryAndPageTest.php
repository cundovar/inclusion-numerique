<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Page;
use PHPUnit\Framework\TestCase;

/**
 * Les relations retirées avec les exercices et les favoris ne doivent pas laisser
 * des collections non initialisées sur les entités conservées.
 */
final class CategoryAndPageTest extends TestCase
{
    public function testNewCategoryIsVisibleWithEmptyCollections(): void
    {
        $category = new Category();

        self::assertTrue($category->isVisible());
        self::assertCount(0, $category->getMenus());
        self::assertCount(0, $category->getPageContents());
    }

    public function testNewPageHasNoContentYet(): void
    {
        self::assertCount(0, (new Page())->getPageContents());
    }
}
