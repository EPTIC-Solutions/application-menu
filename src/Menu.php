<?php

namespace Eptic\ApplicationMenu;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Request;
use JsonSerializable;
use RuntimeException;

class Menu implements Arrayable, Jsonable, JsonSerializable
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

    private ?string $view;

    private ?string $breadcrumbsView;

    protected ?string $currentUrl = null;

    private function __construct(private string $name)
    {
        $this->view = config('application-menu.view');
        $this->breadcrumbsView = config('application-menu.breadcrumbs.view');
    }

    public static function getInstances(): array
    {
        return static::$instances;
    }

    public static function newMenu(string $name, callable $instance): void
    {
        static::$instances[$name] = $instance;
    }

    /**
     * @throws RuntimeException
     */
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
        $menuItem->setCurrentUrl($this->getCurrentUrl());
        if ($addSubmenuCallback) {
            $addSubmenuCallback($menuItem);
        }
        $this->links[] = $menuItem;
        if (!$menuItem->isHidden() && ($menuItem->getUrl() || $menuItem->hasSubMenus())) {
            $this->visibleLinks[] = $menuItem;
        }

        return $this;
    }

    public function getActiveLink(bool $breadCrumbs = false, ?string $searchUrl = null): ?MenuItem
    {
        foreach ($this->getLinks($breadCrumbs) as $link) {
            if ($link->isCurrent($searchUrl)) {
                return $link;
            }
        }

        return null;
    }

    public function getActiveIndex(bool $breadCrumbs = false, ?string $searchUrl = null): int
    {
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

    public function render(?string $view = null): View
    {
        $view ??= $this->view;

        return view($view, [
            'links' => $this->getLinks(),
            'activeIndex' => $this->getActiveIndex(),
        ]);
    }

    public function renderBreadCrumbs(?string $view = null): View
    {
        $view ??= $this->breadcrumbsView;

        return view($view, [
            'links' => $this->getLinks(true),
            'activeIndex' => $this->getActiveIndex(true),
        ]);
    }

    public function getCurrentUrl(): ?string
    {
        return $this->currentUrl;
    }

    public function setCurrentUrl(?string $currentUrl): static
    {
        $this->currentUrl = $currentUrl;
        foreach ($this->links as $link) {
            $link->setCurrentUrl($currentUrl);
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'links' => $this->getLinks(),
            'breadcrumbs' => $this->getLinks(true),
            'activeIndex' => $this->getActiveIndex(),
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
