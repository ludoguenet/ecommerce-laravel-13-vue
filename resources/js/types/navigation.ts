import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
};

export type CatalogMenuItem = {
    id: number;
    attribute_data: { name: { en: string }; highlight: boolean; [key: string]: unknown };
    default_url: { slug: string };
    children: CatalogMenuItem[];
};
