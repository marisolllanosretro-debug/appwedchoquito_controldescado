<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';

onMounted(() => {
    const flash = (usePage().props as any).flash;

    if (flash?.welcome) {
        toast(flash.welcome, {
            duration: 5000,
            position: 'top-center',
        });
    }
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/dashboard',
            },
        ],
    },
});

const stats = ref([
    {
        title: 'Ventas Hoy',
        value: '$12,450',
        change: '+12.5%',
        trend: 'up',
        icon: 'baguette',
        color: 'from-amber-500 to-orange-600',
        shadowColor: 'shadow-amber-500/20',
    },
    {
        title: 'Pedidos Pendientes',
        value: '23',
        change: '+5',
        trend: 'up',
        icon: 'orders',
        color: 'from-rose-500 to-pink-600',
        shadowColor: 'shadow-rose-500/20',
    },
    {
        title: 'Productos en Stock',
        value: '156',
        change: '-8',
        trend: 'down',
        icon: 'stock',
        color: 'from-emerald-500 to-teal-600',
        shadowColor: 'shadow-emerald-500/20',
    },
    {
        title: 'Clientes del Mes',
        value: '342',
        change: '+18.2%',
        trend: 'up',
        icon: 'customers',
        color: 'from-violet-500 to-purple-600',
        shadowColor: 'shadow-violet-500/20',
    },
]);

const recentOrders = ref([
    {
        id: 1,
        product: 'Pan Francés x50',
        customer: 'María López',
        amount: '$250',
        status: 'listo',
        time: 'Hace 5 min',
        emoji: '🥖',
    },
    {
        id: 2,
        product: 'Pastel de Chocolate',
        customer: 'Carlos Ruiz',
        amount: '$450',
        status: 'preparando',
        time: 'Hace 12 min',
        emoji: '🎂',
    },
    {
        id: 3,
        product: 'Croissants x12',
        customer: 'Ana García',
        amount: '$180',
        status: 'pendiente',
        time: 'Hace 18 min',
        emoji: '🥐',
    },
    {
        id: 4,
        product: 'Pan Integral x30',
        customer: 'Roberto Díaz',
        amount: '$210',
        status: 'listo',
        time: 'Hace 25 min',
        emoji: '🍞',
    },
    {
        id: 5,
        product: 'Conchas x24',
        customer: 'Laura Sánchez',
        amount: '$360',
        status: 'preparando',
        time: 'Hace 30 min',
        emoji: '🥯',
    },
]);

const popularProducts = ref([
    { name: 'Pan Francés', sales: 1245, percentage: 95, emoji: '🥖' },
    { name: 'Conchas', sales: 980, percentage: 78, emoji: '🥯' },
    { name: 'Bollos de Mantequilla', sales: 856, percentage: 68, emoji: '🧈' },
    { name: 'Croissants', sales: 720, percentage: 58, emoji: '🥐' },
    { name: 'Pasteles Especiales', sales: 540, percentage: 43, emoji: '🎂' },
]);

const todaySchedule = ref([
    { time: '06:00', label: 'Inicio de hornada matutina', done: true },
    { time: '08:00', label: 'Entrega pedido María López', done: true },
    { time: '10:00', label: 'Preparar pasteles del día', done: false },
    { time: '12:00', label: 'Revisar inventario de harina', done: false },
    { time: '14:00', label: 'Hornada de pan integral', done: false },
]);

const statusColors: Record<string, string> = {
    listo: 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20',
    preparando: 'bg-amber-500/10 text-amber-500 border border-amber-500/20',
    pendiente: 'bg-slate-500/10 text-slate-400 border border-slate-500/20',
};

const statusLabels: Record<string, string> = {
    listo: 'Listo',
    preparando: 'Preparando',
    pendiente: 'Pendiente',
};
</script>

<template>
    <Head title="Dashboard - Panadería" />

    <div
        class="mx-auto flex h-full w-full max-w-screen-2xl flex-1 flex-col gap-4 overflow-x-hidden rounded-xl p-3 sm:gap-6 sm:p-6"
    >
        <!-- Banner Anuncio Destacado -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 via-orange-500 to-amber-600 p-4 shadow-xl sm:p-6 lg:p-8"
        >
            <div
                class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIj48Y2lyY2xlIGN4PSI0MCIgY3k9IjMwIiByPSIyIi8+PC9nPjwvZz48L3N2Zz4=')] opacity-50"
            />
            <div
                class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"
            />
            <div
                class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-white/10 blur-2xl"
            />
            <div
                class="relative flex flex-col items-center justify-center text-center md:flex-row md:text-left"
            >
                <div
                    class="mb-4 flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm sm:h-20 sm:w-20 md:mr-6 md:mb-0"
                >
                    <span class="text-3xl sm:text-4xl">🎉</span>
                </div>
                <div class="flex-1">
                    <div
                        class="mb-2 inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-semibold tracking-wider text-white uppercase"
                    >
                        ¡Promoción del día!
                    </div>
                    <h2
                        class="text-xl leading-tight font-bold text-white sm:text-2xl lg:text-3xl"
                    >
                        2x1 en Croissants y Bollos de Mantequilla
                    </h2>
                    <p class="mt-2 text-sm text-white/90 sm:text-base">
                        ¡Válido solo hoy! Compra cualquier croissant y recibe
                        otro completamente gratis. No te pierdas esta oferta
                        especial de nuestra panadería.
                    </p>
                    <div
                        class="mt-4 flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:flex-wrap sm:items-center md:justify-start"
                    >
                        <div
                            class="flex items-center justify-center gap-2 rounded-lg bg-white/20 px-3 py-2 backdrop-blur-sm sm:px-4"
                        >
                            <svg
                                class="h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span class="text-sm font-medium text-white"
                                >Válido hasta las 8:00 PM</span
                            >
                        </div>
                        <button
                            class="w-full rounded-lg bg-white px-6 py-2 font-semibold text-amber-600 shadow-lg transition-all hover:scale-105 hover:bg-amber-50 sm:w-auto"
                        >
                            Ver productos
                        </button>
                    </div>
                </div>
                <div class="mt-4 hidden text-8xl md:mt-0 lg:block lg:text-9xl">
                    🥐
                </div>
            </div>
        </div>

        <!-- Header -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-xl font-bold tracking-tight text-foreground sm:text-2xl"
                >
                    Panadería Delicias
                </h1>
                <p class="text-sm text-muted-foreground">
                    Bienvenido, aquí tienes un resumen de hoy
                </p>
            </div>
            <div
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-border/50 bg-card px-4 py-2 shadow-sm sm:w-auto"
            >
                <svg
                    class="h-4 w-4 text-amber-500"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                >
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                    />
                </svg>
                <span class="text-sm font-medium">Hoy, 5 de Mayo 2026</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="(stat, index) in stats"
                :key="index"
                class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:scale-[1.02] hover:shadow-lg sm:p-6"
            >
                <div
                    :class="`absolute -top-4 -right-4 h-20 w-20 rounded-full bg-gradient-to-br ${stat.color} opacity-10 blur-xl transition-all group-hover:opacity-20`"
                />
                <div class="flex items-start justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-muted-foreground">
                            {{ stat.title }}
                        </p>
                        <p class="text-2xl font-bold tracking-tight">
                            {{ stat.value }}
                        </p>
                        <div class="flex items-center gap-1">
                            <svg
                                v-if="stat.trend === 'up'"
                                class="h-3.5 w-3.5 text-emerald-500"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="m18 15-6-6-6 6" />
                            </svg>
                            <svg
                                v-else
                                class="h-3.5 w-3.5 text-rose-500"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                            <span
                                :class="
                                    stat.trend === 'up'
                                        ? 'text-emerald-500'
                                        : 'text-rose-500'
                                "
                                class="text-xs font-medium"
                            >
                                {{ stat.change }}
                            </span>
                            <span class="text-xs text-muted-foreground"
                                >vs ayer</span
                            >
                        </div>
                    </div>
                    <div
                        :class="`flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br ${stat.color} shadow-lg ${stat.shadowColor}`"
                    >
                        <template v-if="stat.icon === 'baguette'">
                            <span class="text-2xl">🥖</span>
                        </template>
                        <template v-else-if="stat.icon === 'orders'">
                            <span class="text-2xl">📋</span>
                        </template>
                        <template v-else-if="stat.icon === 'stock'">
                            <span class="text-2xl">📦</span>
                        </template>
                        <template v-else>
                            <span class="text-2xl">👥</span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Recent Orders -->
            <div
                class="min-w-0 rounded-xl border border-border/50 bg-card p-4 shadow-sm sm:p-6 lg:col-span-2"
            >
                <div
                    class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="text-lg font-semibold">Pedidos Recientes</h2>
                        <p class="text-sm text-muted-foreground">
                            Últimos pedidos recibidos
                        </p>
                    </div>
                    <button
                        class="w-full rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 sm:w-auto sm:py-1.5"
                    >
                        Ver todos
                    </button>
                </div>
                <div class="space-y-3 md:hidden">
                    <article
                        v-for="order in recentOrders"
                        :key="order.id"
                        class="rounded-lg border border-border/50 bg-background/40 p-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="shrink-0 text-xl">{{
                                    order.emoji
                                }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">
                                        {{ order.product }}
                                    </p>
                                    <p
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {{ order.customer }}
                                    </p>
                                </div>
                            </div>
                            <span class="shrink-0 text-sm font-semibold">{{
                                order.amount
                            }}</span>
                        </div>
                        <div
                            class="mt-3 flex items-center justify-between gap-3"
                        >
                            <span
                                :class="statusColors[order.status]"
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                            >
                                {{ statusLabels[order.status] }}
                            </span>
                            <span class="text-xs text-muted-foreground">{{
                                order.time
                            }}</span>
                        </div>
                    </article>
                </div>
                <div
                    class="hidden overflow-x-auto rounded-lg border border-border/50 md:block"
                >
                    <table class="w-full min-w-[45rem]">
                        <thead class="border-b border-border/50 bg-muted/50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Producto
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Cliente
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Monto
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                >
                                    Hora
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="transition-colors hover:bg-muted/30"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl">{{
                                            order.emoji
                                        }}</span>
                                        <span class="text-sm font-medium">{{
                                            order.product
                                        }}</span>
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 text-sm text-muted-foreground"
                                >
                                    {{ order.customer }}
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold">
                                    {{ order.amount }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="statusColors[order.status]"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    >
                                        {{ statusLabels[order.status] }}
                                    </span>
                                </td>
                                <td
                                    class="px-4 py-3 text-right text-xs text-muted-foreground"
                                >
                                    {{ order.time }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Popular Products -->
            <div
                class="min-w-0 rounded-xl border border-border/50 bg-card p-4 shadow-sm sm:p-6"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Más Vendidos</h2>
                        <p class="text-sm text-muted-foreground">
                            Productos estrella del mes
                        </p>
                    </div>
                    <span class="text-2xl">🏆</span>
                </div>
                <div class="space-y-4">
                    <div
                        v-for="(product, index) in popularProducts"
                        :key="index"
                        class="group space-y-2"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ product.emoji }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">
                                        {{ product.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ product.sales }} vendidos
                                    </p>
                                </div>
                            </div>
                            <span class="text-sm font-bold"
                                >{{ product.percentage }}%</span
                            >
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-500"
                                :style="`width: ${product.percentage}%`"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Today's Schedule -->
            <div
                class="min-w-0 rounded-xl border border-border/50 bg-card p-4 shadow-sm sm:p-6"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Agenda del Día</h2>
                        <p class="text-sm text-muted-foreground">
                            Tareas pendientes
                        </p>
                    </div>
                    <span class="text-2xl">📅</span>
                </div>
                <div class="space-y-3">
                    <div
                        v-for="(task, index) in todaySchedule"
                        :key="index"
                        :class="task.done ? 'opacity-60' : ''"
                        class="flex items-center gap-3 rounded-lg border border-border/30 p-3 transition-colors hover:bg-muted/30"
                    >
                        <div
                            :class="task.done ? 'bg-emerald-500' : 'bg-muted'"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                        >
                            <svg
                                v-if="task.done"
                                class="h-4 w-4 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M20 6L9 17l-5-5" />
                            </svg>
                            <span
                                v-else
                                class="text-xs font-bold text-muted-foreground"
                                >{{ index + 1 }}</span
                            >
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                :class="task.done ? 'line-through' : ''"
                                class="text-sm font-medium"
                            >
                                {{ task.label }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ task.time }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div
                class="min-w-0 rounded-xl border border-border/50 bg-card p-4 shadow-sm sm:p-6"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Acciones Rápidas</h2>
                        <p class="text-sm text-muted-foreground">
                            Accesos directos
                        </p>
                    </div>
                    <span class="text-2xl">⚡</span>
                </div>
                <div class="grid gap-3">
                    <button
                        class="flex items-center gap-3 rounded-lg border border-border/30 p-4 text-left transition-all hover:border-amber-500/50 hover:bg-amber-500/5"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/10"
                        >
                            <span class="text-xl">🥖</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium">Nuevo Pedido</p>
                            <p class="text-xs text-muted-foreground">
                                Crear pedido personalizado
                            </p>
                        </div>
                    </button>
                    <button
                        class="flex items-center gap-3 rounded-lg border border-border/30 p-4 text-left transition-all hover:border-rose-500/50 hover:bg-rose-500/5"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-500/10"
                        >
                            <span class="text-xl">🎂</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium">Pastel Especial</p>
                            <p class="text-xs text-muted-foreground">
                                Diseño personalizado
                            </p>
                        </div>
                    </button>
                    <button
                        class="flex items-center gap-3 rounded-lg border border-border/30 p-4 text-left transition-all hover:border-emerald-500/50 hover:bg-emerald-500/5"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10"
                        >
                            <span class="text-xl">📊</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium">Reporte de Ventas</p>
                            <p class="text-xs text-muted-foreground">
                                Ver estadísticas
                            </p>
                        </div>
                    </button>
                    <button
                        class="flex items-center gap-3 rounded-lg border border-border/30 p-4 text-left transition-all hover:border-violet-500/50 hover:bg-violet-500/5"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-500/10"
                        >
                            <span class="text-xl">📦</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium">Inventario</p>
                            <p class="text-xs text-muted-foreground">
                                Gestionar stock
                            </p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Weather & Info -->
            <div
                class="min-w-0 rounded-xl border border-border/50 bg-card p-4 shadow-sm sm:p-6"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Info del Día</h2>
                        <p class="text-sm text-muted-foreground">
                            Datos importantes
                        </p>
                    </div>
                    <span class="text-2xl">🌤️</span>
                </div>
                <div class="space-y-4">
                    <div
                        class="rounded-lg bg-gradient-to-br from-sky-500/10 to-blue-500/10 p-4"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">☀️</span>
                            <div class="min-w-0">
                                <p class="text-lg font-bold">28°C</p>
                                <p class="text-xs text-muted-foreground">
                                    Soleado
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-border/30 p-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📈</span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium">
                                    Mejor día de la semana
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Sábado - $18,450 en ventas
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-border/30 p-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🎯</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium">Meta mensual</p>
                                <p class="text-xs text-muted-foreground">
                                    $245,000 / $300,000
                                </p>
                                <div
                                    class="mt-2 h-2 overflow-hidden rounded-full bg-muted"
                                >
                                    <div
                                        class="h-full w-[82%] rounded-full bg-gradient-to-r from-emerald-500 to-teal-500"
                                    />
                                </div>
                                <p
                                    class="mt-1 text-right text-xs font-medium text-emerald-500"
                                >
                                    82%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
