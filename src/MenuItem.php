<?php

namespace Eptic\ApplicationMenu;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;

class MenuItem
{
    /** @var array<MenuItem> */
    private array $subMenus = [];

    /** @var array<MenuItem> */
    private array $visibleSubMenus = [];

    private ?bool $isCurrentCache = null;

    private ?string $breadcrumbLabel;

    public function __construct(
        private readonly string $label,
        private readonly ?string $url = null,
        private readonly bool $hidden = false,
        private readonly ?string $description = null,
        private Fluent $data = new Fluent,
    ) {
        //
    }

    public function add(
        string $label,
        ?string $route = null,
        bool $hidden = false,
        ?string $description = null,
        array|Fluent $data = [],
    ): self {
        $subMenu = new self($label, $route, $hidden, $description, ($data instanceof Fluent) ? $data : new Fluent($data));
        $this->subMenus[] = $subMenu;
        if (!$hidden) {
            $this->visibleSubMenus[] = $subMenu;
        }

        return $subMenu;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getData(): Fluent
    {
        return $this->data;
    }

    public function setData(Fluent|array $data): self
    {
        if (is_array($data)) {
            $data = new Fluent($data);
        }
        $this->data = $data;

        return $this;
    }

    public function getVisibleSubMenus(bool $breadCrumbs = false): array
    {
        if ($breadCrumbs) {
            return $this->subMenus;
        }

        return $this->visibleSubMenus;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function isCurrent(?string $searchUrl = null): bool
    {
        if (!$searchUrl && $this->isCurrentCache) {
            return $this->isCurrentCache;
        }

        foreach ($this->subMenus as $subMenu) {
            if ($subMenu->isCurrent($searchUrl)) {
                if ($searchUrl) {
                    return true;
                }
                $this->isCurrentCache = true;

                return $this->isCurrentCache;
            }
        }

        if ($searchUrl) {
            return $this->isActive($searchUrl);
        }

        $this->isCurrentCache = $this->isActive();

        return $this->isCurrentCache;
    }

    private function isActiveUrl(array $needle, ?string $haystack): bool
    {
        // If the current menu item has no url, it can never be active
        if (!$haystack) {
            return false;
        }

        $haystackUri = Uri::of($haystack);
        if ($haystackUri->path() && isset($needle['path'])) {
            // If it is not the same path
            if ($needle['path'][0] === '/' && strlen($needle['path']) > 1) {
                $needle['path'] = substr($needle['path'], 1);
            }

            if ($haystackUri->path() !== $needle['path']) {
                return false;
            }

            // We only want to take into consideration required query params
            // from the menu item's url, the current page can have additional
            // query params, but they should not miss the required ones.
            if ($haystackUri->query()->collect()->isEmpty()) {
                return true;
            }

            // If we have no query params for the needle we can return directly.
            if (!isset($needle['query'])) {
                return false;
            }

            if (!Str::contains($needle['query'], $haystackUri->query()->decode())) {
                return false;
            }

            return true;
        } elseif ($haystackUri->path() !== null || isset($needle['path'])) {
            return false;
        }

        return true;
    }

    public function isActive(?string $searchUrl = null): bool
    {
        if (!$this->getUrl()) {
            return false;
        }

        $searchUrl ??= Request::fullUrl();
        $parsedUrl = parse_url($searchUrl);
        if (!$parsedUrl) {
            return false;
        }

        if ($this->url && $this->isActiveUrl($parsedUrl, $this->url)) {
            return true;
        }

        foreach ($this->subMenus as $subMenu) {
            if ($subMenu->isCurrent($searchUrl)) {
                return true;
            }
        }

        return false;
    }

    public function hasSubMenus(bool $breadCrumbs = false): bool
    {
        return count($this->getVisibleSubMenus($breadCrumbs)) > 0;
    }

    public function setBreadcrumbLabel(string $breadcrumbLabel): self
    {
        $this->breadcrumbLabel = $breadcrumbLabel;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getBreadcrumbLabel(): string
    {
        return $this->breadcrumbLabel ?? $this->label;
    }
}
