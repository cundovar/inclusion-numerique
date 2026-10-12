<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Menus;
use App\Entity\PositionMenus;
use PHPUnit\Framework\TestCase;

final class PositionMenusTest extends TestCase
{
    public function testAddAndRemoveMenuKeepBothSidesInSync(): void
    {
        $position = new PositionMenus();
        $menu = new Menus();

        $position->addMenu($menu);

        self::assertTrue($position->getMenus()->contains($menu));
        self::assertSame($position, $menu->getPositionMenus());

        $position->removeMenu($menu);

        self::assertFalse($position->getMenus()->contains($menu));
        self::assertNull($menu->getPositionMenus());
    }

    public function testMenuPositionCanBeReassignedAndCleared(): void
    {
        $first = new PositionMenus();
        $second = new PositionMenus();
        $menu = new Menus();

        $menu->setPositionMenus($first);
        $menu->setPositionMenus($second);
        self::assertSame($second, $menu->getPositionMenus());

        $menu->setPositionMenus(null);
        self::assertNull($menu->getPositionMenus());
    }
}
