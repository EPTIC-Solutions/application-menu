<?php

namespace Eptic\ApplicationMenu;

use Illuminate\Contracts\View\View as ContractsView;
use Illuminate\Support\Facades\View;
use RuntimeException;

class Menu
{
    /**
     * @var array<string, callable>
     */
    protected static array $instances = [];

    /**
     * @var array<string, self>
     */
    protected static array $cachedInstances = [];

    /**
     * @var MenuItem[]
     */
    protected array $links = [];

    /**
     * @var MenuItem[]
     */
    protected array $visibleLinks = [];

    private string $view = '_utils.application-menu.template';

    private string $breadcrumbsView = '_utils.application-menu.breadcrumbs';

    private function __construct(private string $name)
    {
        //
    }

    public static function getInstances(): array
    {
        return static::$instances;
    }

    public static function newMenu(string $name, callable $instance): void
    {
        static::$instances[$name] = $instance;
    }

    public static function getInstance(?string $name = null): self
    {
        $name ??= 'main';
        if (isset(static::$cachedInstances[$name])) {
            return static::$cachedInstances[$name];
        }

        if (isset(static::$instances[$name])) {
            $newMenu = new static($name);
            static::$instances[$name]($newMenu);
            static::$cachedInstances[$name] = $newMenu;

            return $newMenu;
        }

        throw new RuntimeException('Could not find application menu with name: ' . $name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function add(
        string $label,
        ?string $route = null,
        ?callable $addSubmenuCallback = null,
        bool $hidden = false,
        ?string $description = null,
    ): self {
        $menuItem = new MenuItem($label, $route, $hidden, $description);
        if ($addSubmenuCallback) {
            $addSubmenuCallback($menuItem);
        }
        $this->links[] = $menuItem;
        if (!$menuItem->isHidden()) {
            $this->visibleLinks[] = $menuItem;
        }

        return $this;
    }

    public function getActiveLink(bool $breadCrumbs = false, ?string $searchUrl = null): ?MenuItem
    {
        /**
         * @var MenuItem $link
         */
        foreach ($this->getLinks($breadCrumbs) as $link) {
            if ($link->isCurrent($searchUrl)) {
                return $link;
            }
        }

        return null;
    }

    public function getActiveIndex(bool $breadCrumbs = false, ?string $searchUrl = null): int
    {
        /**
         * @var MenuItem $link
         */
        foreach ($this->getLinks($breadCrumbs) as $index => $link) {
            if ($link->isCurrent($searchUrl)) {
                return $index;
            }
        }

        return -1;
    }

    public function getLinks(bool $breadCrumbs = false): array
    {
        if ($breadCrumbs) {
            return $this->links;
        }

        return $this->visibleLinks;
    }

    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    public function setBreadcrumbsView(string $view): self
    {
        $this->breadcrumbsView = $view;

        return $this;
    }

    public function render(?string $view = null): ContractsView
    {
        $view ??= $this->view;

        return View::make($view, [
            'links' => $this->getLinks(),
            'activeIndex' => $this->getActiveIndex(),
        ]);
    }

    public function renderBreadCrumbs(?string $view = null): ContractsView
    {
        $view ??= $this->breadcrumbsView;

        return View::make($view, [
            'links' => $this->getLinks(true),
            'activeIndex' => $this->getActiveIndex(true),
        ]);
    }
}
