<?php

use Eptic\ApplicationMenu\Menu;
use Eptic\ApplicationMenu\MenuItem;

test('it serializes to array', function () {
    Menu::newMenu('json', function (Menu $menu) {
        $menu->add('testing', 'https://example.com/testing');
        $menu->add('testing2', 'https://example.com/testing/2', hidden: true);
        $menu->add('test', 'https://example.com/test', function (MenuItem $menuItem) {
            $menuItem->add('test2', 'https://example.com/test/2')
                ->setData([123]);
        });
    });

    expect(Menu::getInstance('json')->setCurrentUrl('https://example.com/testing')->toJson())->toMatchSnapshot();
});
