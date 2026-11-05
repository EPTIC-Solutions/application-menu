<?php

use Eptic\ApplicationMenu\Menu;
use Eptic\ApplicationMenu\MenuManager;

test('it builds a menu once and reuses it until the built menus are forgotten', function () {
    $builds = 0;
    Menu::newMenu('main', function (Menu $menu) use (&$builds) {
        $builds++;
        $menu->add('Home', 'https://example.com');
    });

    $first = Menu::getInstance();

    expect(Menu::getInstance())->toBe($first)
        ->and($builds)->toBe(1);

    app(MenuManager::class)->forgetBuilt();

    expect(Menu::getInstance())->not->toBe($first)
        ->and($builds)->toBe(2)
        ->and(Menu::getInstances())->toHaveKey('main');
});

test('redefining a menu replaces the one already built', function () {
    Menu::newMenu('main', fn (Menu $menu) => $menu->add('Old', 'https://example.com/old'));
    Menu::getInstance();

    Menu::newMenu('main', fn (Menu $menu) => $menu->add('New', 'https://example.com/new'));

    expect(Menu::getInstance()->getLinks()[0]->getLabel())->toBe('New');
});

test('it keeps no menus outside the manager bound in the container', function () {
    Menu::newMenu('main', fn (Menu $menu) => $menu->add('Home', 'https://example.com'));

    app()->forgetInstance(MenuManager::class);

    expect(Menu::getInstances())->toBeEmpty()
        ->and(fn () => Menu::getInstance())->toThrow(RuntimeException::class);
});
