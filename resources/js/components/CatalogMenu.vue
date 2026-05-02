<script setup lang="ts">
import { usePage, Link } from '@inertiajs/vue3';
import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { show as collectionsShow } from '@/routes/collections';
import type { CatalogMenuItem } from '@/types';

defineProps<{
    items: CatalogMenuItem[];
}>();

const page = usePage();

function itemName(item: CatalogMenuItem): string {
    return item.attribute_data.name.en;
}

function itemHref(item: CatalogMenuItem): string {
    return collectionsShow.url(item.default_url.slug);
}

function isActive(item: CatalogMenuItem): boolean {
    const activeIds = page.props.activeCollectionIds as number[] | undefined;
    return activeIds?.includes(item.id) ?? false;
}
</script>

<template>
    <NavigationMenu>
        <NavigationMenuList>
            <NavigationMenuItem v-for="item in items" :key="item.id">
                <template v-if="item.children.length > 0">
                    <NavigationMenuTrigger
                        :class="[item.attribute_data.highlight ? 'text-yellow-500' : '', isActive(item) ? 'bg-accent' : '']"
                    >
                        {{ itemName(item) }}
                    </NavigationMenuTrigger>
                    <NavigationMenuContent>
                        <ul class="grid w-[220px] gap-1 p-3">
                            <li v-for="child in item.children" :key="child.id">
                                <NavigationMenuLink :as-child="true">
                                    <Link
                                        :href="itemHref(child)"
                                        class="block select-none rounded-sm px-3 py-2 text-sm leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground"
                                    >
                                        {{ itemName(child) }}
                                    </Link>
                                </NavigationMenuLink>
                            </li>
                        </ul>
                    </NavigationMenuContent>
                </template>
                <NavigationMenuLink v-else :as-child="true">
                    <Link :href="itemHref(item)" :class="[navigationMenuTriggerStyle(), isActive(item) ? 'bg-accent' : '']">
                        {{ itemName(item) }}
                    </Link>
                </NavigationMenuLink>
            </NavigationMenuItem>
        </NavigationMenuList>
    </NavigationMenu>
</template>
