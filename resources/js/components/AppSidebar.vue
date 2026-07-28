<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, ClipboardList, FolderGit2, FolderOpen, LayoutGrid, LayoutList, Package, Shield, ShoppingCart, Store, Truck, UserCog, Users, Warehouse } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import type { NavItem } from '@/types';

const { can } = usePermissions();

const allNavItems: { title: string; href: string; icon: any; permiso?: string }[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Clientes',
        href: '/clientes',
        icon: Users,
        permiso: 'ver_clientes',
    },
    {
        title: 'Pedidos',
        href: '#',
        icon: ClipboardList,
    },
    {
        title: 'Ventas',
        href: '#',
        icon: ShoppingCart,
    },
    {
        title: 'Compras',
        href: '/compras',
        icon: LayoutList,
        permiso: 'ver_compras',
    },
    {
        title: 'Proveedores',
        href: '/proveedores',
        icon: Truck,
        permiso: 'ver_proveedores',
    },
    {
        title: 'Productos',
        href: '/productos',
        icon: Package,
        permiso: 'ver_productos',
    },
    {
        title: 'Catálogo',
        href: '/catalogo',
        icon: Store,
    },
    {
        title: 'Categorías',
        href: '/categorias',
        icon: FolderOpen,
        permiso: 'gestionar_categorias',
    },
    {
        title: 'Usuarios',
        href: '/usuarios',
        icon: UserCog,
        permiso: 'gestionar_usuarios',
    },
    {
        title: 'Roles',
        href: '/roles',
        icon: Shield,
        permiso: 'gestionar_roles',
    },
];

const mainNavItems = computed<NavItem[]>(() =>
    allNavItems.filter((item) => !item.permiso || can(item.permiso)),
);

const footerNavItems: NavItem[] = [
    {
        title: 'Repositorio',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentación',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
