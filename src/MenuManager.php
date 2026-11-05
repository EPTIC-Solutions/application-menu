<?php

namespace Eptic\ApplicationMenu;

use RuntimeException;

/**
 * The menus built during the current request.
 *
 * Bound as scoped: a built menu reflects the user, locale and URL of the request that built
 * it, so Laravel forgets the manager after every Octane request and every queued job, while
 * a PHP-FPM process simply ends with the request.
 */
class MenuManager
{
    /**
     * @var array<string, array{definition: callable(Menu): mixed, menu: Menu}>
     */
    private array $built = [];

    public function __construct(private MenuRegistry $registry)
    {
        //
    }

    /**
     * The menu with the given name, built on first use in this request and built again
     * when its definition changes.
     *
     * @throws RuntimeException
     */
    public function get(?string $name = null): Menu
    {
        $name ??= 'main';
        $definition = $this->registry->definition($name);

        if (($this->built[$name]['definition'] ?? null) === $definition) {
            return $this->built[$name]['menu'];
        }

        $menu = $this->registry->build($name);
        $this->built[$name] = ['definition' => $definition, 'menu' => $menu];

        return $menu;
    }
}
