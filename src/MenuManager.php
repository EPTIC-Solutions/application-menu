<?php

namespace Eptic\ApplicationMenu;

use RuntimeException;

/**
 * Holds the menu definitions and the menus built from them.
 *
 * Nothing here is static: how long the definitions and the built menus live is decided by
 * how the manager is bound in the container. The package binds it as a singleton; a
 * long-lived worker (Octane) should call forgetBuilt() between requests, because a built
 * menu reflects the user, locale and URL of the request that built it.
 */
class MenuManager
{
    /**
     * @var array<string, callable(Menu): mixed>
     */
    private array $definitions = [];

    /**
     * @var array<string, Menu>
     */
    private array $built = [];

    /**
     * Register how the menu with the given name is built.
     *
     * @param  callable(Menu): mixed  $definition
     */
    public function define(string $name, callable $definition): void
    {
        $this->definitions[$name] = $definition;

        unset($this->built[$name]);
    }

    /**
     * @return array<string, callable(Menu): mixed>
     */
    public function definitions(): array
    {
        return $this->definitions;
    }

    /**
     * The menu with the given name, built on first use and reused until forgetBuilt().
     *
     * @throws RuntimeException
     */
    public function get(?string $name = null): Menu
    {
        $name ??= 'main';

        if (isset($this->built[$name])) {
            return $this->built[$name];
        }

        if (!isset($this->definitions[$name])) {
            throw new RuntimeException('Could not find application menu with name: ' . $name);
        }

        $menu = new Menu($name);
        ($this->definitions[$name])($menu);

        return $this->built[$name] = $menu;
    }

    /**
     * Forget the built menus so the next get() builds them again; the definitions are kept.
     */
    public function forgetBuilt(): void
    {
        $this->built = [];
    }
}
