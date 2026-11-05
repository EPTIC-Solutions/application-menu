<?php

use Eptic\ApplicationMenu\Menu;

test('it builds a menu once per request and reuses it within that request', function () {
    $builds = 0;
    Menu::newMenu('main', function (Menu $menu) use (&$builds) {
        $builds++;
        $menu->add('Home', 'https://example.com');
    });

    expect(Menu::getInstance())->toBe(Menu::getInstance())
        ->and($builds)->toBe(1);
});

test('it builds the menu again for the next request on a long-lived worker and keeps its definition', function () {
    $builds = 0;
    Menu::newMenu('main', function (Menu $menu) use (&$builds) {
        $builds++;
        $menu->add('Home', 'https://example.com');
    });
    $first = Menu::getInstance();

    // What Laravel Octane does after every request, and the queue worker before every job.
    app()->forgetScopedInstances();

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

test('it throws for a menu that was never defined', function () {
    Menu::getInstance('missing');
})->throws(RuntimeException::class, 'Could not find application menu with name: missing');
