<?php

namespace Eptic\ApplicationMenu;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class MenuItem
{
    private array $subMenus = [];

    private array $visibleSubMenus = [];

    private ?bool $isCurrentCache = null;

    private ?string $breadcrumbLabel;

    public function __construct(
        private string $label,
        private ?string $url = null,
        private bool $hidden = false,
        private ?string $description = null,
    ) {
        //
    }

    public function add(
        string $label,
        ?string $route = null,
        bool $hidden = false,
        ?string $description = null,
    ): self {
        $subMenu = new self($label, $route, $hidden, $description);
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

    private function isActiveUrl(array $parsedUrl, $url): bool
    {
        if (!$url) {
            return false;
        }

        $parsedMenuUrl = parse_url($url);
        if (isset($parsedMenuUrl['path'])) {
            if (!isset($parsedUrl['path']) || $parsedMenuUrl['path'] !== $parsedUrl['path']) {
                return false;
            }

            if (!isset($parsedMenuUrl['query'])) {
                return true;
            }

            if (!isset($parsedUrl['query']) || !Str::contains(
                $parsedUrl['query'],
                explode('&', $parsedMenuUrl['query'])
            )) {
                return false;
            }

            return true;
        }

        if (!isset($parsedUrl['path'])) {
            return true;
        }

        return false;
    }

    public function isActive(?string $searchUrl = null): bool
    {
        if (!$this->getUrl()) {
            return false;
        }

        $searchUrl ??= Request::fullUrl();
        $parsedUrl = parse_url($searchUrl);

        if ($this->url && $this->isActiveUrl($parsedUrl, $this->url)) {
            return true;
        }

        foreach ($this->subMenus as $subMenu) {
            if ($subMenu->isCurrent()) {
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
