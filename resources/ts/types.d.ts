export type MenuItem<T = Record<string, unknown> | Array<unknown>, K = Record<string, unknown> | Array<unknown>> = {
    label: string;
    breadcrumbLabel: MenuItem<T>['label'];
    description: string | null;
    url: string|null;
    hasSubMenus: boolean;
    hidden: boolean;
    data: T;
    isActive: boolean;
    visibleSubMenus: MenuItem<K>[];
}

export type Menu<T = Record<string, unknown> | Array<unknown>, K = Record<string, unknown> | Array<unknown>> = {
    links: MenuItem<T,K>[];
    breadcrumbs: MenuItem<T,K>[];
    activeIndex: number;
}