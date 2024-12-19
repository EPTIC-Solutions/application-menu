<?php

use Eptic\ApplicationMenu\MenuItem;

describe('MenuItem', function () {

    test('correctly checks active urls', function () {
        $menuItem = new MenuItem(
            label: 'Testing',
            url: 'https://example.com/',
        );

        $active = $menuItem->isActive('https://example.com/');

        expect($active)->toBeTrue();
    });

    test('correctly checks active urls with path', function () {
        $menuItem = new MenuItem(
            label: 'Testing',
            url: 'https://example.com/testing',
        );

        $active = $menuItem->isActive('https://example.com/testing');

        expect($active)->toBeTrue();
    });

    test('correctly checks active urls with query string - false', function (string $menuUrl, string $url) {
        $menuItem = new MenuItem(
            label: 'Testing',
            url: $menuUrl,
        );

        $active = $menuItem->isActive($url);

        expect($active)->tobeFalse();
    })->with([
        ['https://example.com/?test=true', 'https://example.com/?'],
        ['https://example.com/?test=true', 'https://example.com/'],
        ['https://example.com/', 'https://example.com'],
        ['https://example.com/?test[]=1', 'https://example.com/?test[0]=1'],
    ]);

    test('correctly checks active urls with query string - true', function (string $url) {
        $menuItem = new MenuItem(
            label: 'Testing',
            url: $url,
        );

        $active = $menuItem->isActive($url);

        expect($active)->toBeTrue();
    })->with([
        'https://example.com/?test=true',
        'https://example.com/?test=true&testing=false',
        'https://example.com/?test[]=1',
        'https://example.com/?test[0]=1',
    ]);

    test('correctly checks submenus', function () {
        $menuItem = new MenuItem(
            label: 'Testing',
            url: 'https://example.com/',
        );
        $menuItem->add('testing', 'https://example.com/testing', true);
        $menuItem->add('testing', 'https://example.com/testing/2', false);

        expect($menuItem->hasSubMenus())->toBeTrue();
    });

    test('correctly checks submenus with hidden child', function () {
        $menuItem = new MenuItem(
            label: 'Testing',
            url: 'https://example.com/',
        );
        $menuItem->add('testing', 'https://example.com/testing', true);

        expect($menuItem->hasSubMenus())->toBeFalse();
    });

    test('correctly checks submenus with hidden child included', function () {
        $menuItem = new MenuItem(
            label: 'Testing',
            url: 'https://example.com/',
        );
        $menuItem->add('testing', 'https://example.com/testing', true);

        expect($menuItem->hasSubMenus(true))->toBeTrue();
    });

    test('correctly checks submenu active url', function () {
        $menuItem = new MenuItem(
            label: 'Testing',
            url: 'https://example.com/',
        );
        $activeUrl = 'https://example.com/testing';
        $menuItem->add('testing', $activeUrl);

        expect($menuItem->isActive($activeUrl))->toBeTrue();
    });

});
