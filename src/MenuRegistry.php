<?php

namespace Eptic\ApplicationMenu;

use RuntimeException;

/**
 * The menu definitions, registered once at boot and kept for the life of the application.
 *
 * Bound as a singleton: a definition holds no request state, so it is safe to share between
 * the requests a long-lived worker (Laravel Octane) serves.
 */
class MenuRegistry
{
    /**
     * @var array<string, callable(Menu): mixed>
     */
    private array $definitions = [];

    /**
     * Register how the menu with the given name is built.
     *
     * @param  callable(Menu): mixed  $definition
     */
    public function define(string $name, callable $definition): void
    {
        $this->definitions[$name] = $definition;
    }

    public function has(string $name): bool
    {
        return isset($this->definitions[$name]);
    }

    /**
     * @return callable(Menu): mixed
     *
     * @throws RuntimeException
     */
    public function definition(string $name): callable
    {
        return $this->definitions[$name]
            ?? throw new RuntimeException('Could not find application menu with name: ' . $name);
    }

    /**
     * @return array<string, callable(Menu): mixed>
     */
    public function definitions(): array
    {
        return $this->definitions;
    }

    /**
     * Build a new menu from its definition.
     *
     * @throws RuntimeException
     */
    public function build(string $name): Menu
    {
        $definition = $this->definition($name);

        $menu = new Menu($name);
        $definition($menu);

        return $menu;
    }
}
