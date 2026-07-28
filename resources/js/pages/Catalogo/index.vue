<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Producto {
    id: number;
    nombre: string;
    categoria: string;
    descripcion: string | null;
    precio_compra: number;
    precio_venta: number;
    stock: number;
    stock_minimo: number;
    unidad_medida: string;
    stock_bajo: boolean;
    created_at: string;
}

interface CategoriaItem {
    id: number;
    nombre: string;
    icono: string | null;
}

interface Props {
    productos: Producto[];
    categorias: CategoriaItem[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const expandedId = ref<number | null>(null);
const expandedCategoria = ref<string | null>(null);

const filteredProductos = computed(() => {
    if (!searchQuery.value) return props.productos;

    const query = searchQuery.value.toLowerCase();
    return props.productos.filter(p =>
        p.nombre.toLowerCase().includes(query) ||
        p.categoria.toLowerCase().includes(query) ||
        (p.descripcion && p.descripcion.toLowerCase().includes(query))
    );
});

const productosPorCategoria = computed(() => {
    const grouped: Record<string, Producto[]> = {};
    for (const p of filteredProductos.value) {
        if (!grouped[p.categoria]) {
            grouped[p.categoria] = [];
        }
        grouped[p.categoria].push(p);
    }
    return grouped;
});

const stats = computed(() => {
    const total = props.productos.length;
    const cats: Record<string, number> = {};
    for (const p of props.productos) {
        cats[p.categoria] = (cats[p.categoria] || 0) + 1;
    }
    return { total, cats };
});

function toggleExpand(id: number) {
    expandedId.value = expandedId.value === id ? null : id;
}

function toggleCategoria(cat: string) {
    expandedCategoria.value = expandedCategoria.value === cat ? null : cat;
}

function getUnidadAbrev(unidad: string): string {
    const mapa: Record<string, string> = {
        'Unidad': 'unid', 'Kilogramo': 'kg', 'Libra': 'lb', 'Litro': 'L',
        'Quintal': 'qq', 'Arroba': '@', 'Caja': 'caja', 'Bolsa': 'bolsa',
        'Saco': 'saco', 'Paquete': 'pqte', 'Docena': 'doc',
    };
    return mapa[unidad] || unidad;
}

function formatCurrency(value: number | string) {
    return new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL'
    }).format(parseFloat(String(value)));
}

const categoriaIconos: Record<string, string> = {
    Panificados: '🥖',
    Pizzas: '🍕',
    Repostería: '🧁',
    Bebidas: '🥤',
    Otros: '📦',
};

const categoriaColores: Record<string, { bg: string; text: string; badge: string; light: string }> = {
    Panificados: { bg: 'bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20', text: 'text-amber-700 dark:text-amber-400', badge: 'bg-amber-500', light: 'bg-amber-500/10' },
    Pizzas: { bg: 'bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-950/20 dark:to-rose-950/20', text: 'text-red-700 dark:text-red-400', badge: 'bg-red-500', light: 'bg-red-500/10' },
    Repostería: { bg: 'bg-gradient-to-br from-pink-50 to-purple-50 dark:from-pink-950/20 dark:to-purple-950/20', text: 'text-pink-700 dark:text-pink-400', badge: 'bg-pink-500', light: 'bg-pink-500/10' },
    Bebidas: { bg: 'bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-950/20 dark:to-cyan-950/20', text: 'text-blue-700 dark:text-blue-400', badge: 'bg-blue-500', light: 'bg-blue-500/10' },
    Otros: { bg: 'bg-gradient-to-br from-gray-50 to-slate-50 dark:from-gray-950/20 dark:to-slate-950/20', text: 'text-gray-700 dark:text-gray-400', badge: 'bg-gray-500', light: 'bg-gray-500/10' },
};
</script>

<template>
    <Head title="Catálogo - Panadería Delicias" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-hidden p-4">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-black via-red-950 to-yellow-900 p-5 shadow-xl shadow-red-900/30">
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid)"/>
                </svg>
            </div>
            <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-red-600/30 blur-2xl animate-pulse"></div>
            <div class="absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-yellow-600/25 blur-2xl animate-pulse" style="animation-delay: 1s"></div>

            <div class="relative flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-red-600 to-yellow-500 shadow-xl shadow-red-500/30">
                            <span class="text-2xl">🏪</span>
                        </div>
                        <div class="absolute -bottom-1.5 -right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 shadow-lg">
                            <svg class="h-3 w-3 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Catálogo de Productos</h1>
                        <p class="text-yellow-200 text-sm">Explora nuestros productos por categoría</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Summary Cards -->
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div
                v-for="cat in categorias"
                :key="cat.id"
                @click="toggleCategoria(cat.nombre)"
                class="group relative overflow-hidden rounded-xl border transition-all duration-300 cursor-pointer"
                :class="[
                    expandedCategoria === cat.nombre ? 'ring-2 ring-yellow-500 shadow-lg' : 'border-border/50 bg-card shadow-sm hover:shadow-md',
                ]"
            >
                <div class="flex items-center gap-3 p-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-lg shadow-sm bg-gradient-to-br"
                        :class="expandedCategoria === cat.nombre ? 'from-yellow-500 to-amber-500' : (cat.icono ? 'from-gray-500 to-slate-500' : 'from-gray-500 to-slate-500')">
                        <span class="text-white">{{ cat.icono || '📦' }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-muted-foreground truncate">{{ cat.nombre }}</p>
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg font-bold">{{ stats.cats[cat.nombre] || 0 }}</span>
                            <span class="text-xs text-muted-foreground">productos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="relative w-full sm:max-w-md">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
            </svg>
            <input
                v-model="searchQuery"
                placeholder="Buscar productos en el catálogo..."
                class="w-full rounded-xl border border-border/50 bg-card pl-10 pr-4 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500/50"
            />
        </div>

        <!-- Catalog Grid grouped by categories -->
        <div class="flex-1 overflow-auto">
            <div class="space-y-6">
                <div
                    v-for="(productos, categoria) in productosPorCategoria"
                    :key="categoria"
                    class="rounded-xl border transition-all duration-300 overflow-hidden"
                    :class="[
                        expandedCategoria === categoria ? 'border-yellow-500 shadow-lg shadow-yellow-500/20' : 'border-border/50',
                        categoriaColores[categoria]?.bg || ''
                    ]"
                >
                    <div
                        @click="toggleCategoria(categoria)"
                        class="flex items-center justify-between px-5 py-4 cursor-pointer select-none"
                        :class="expandedCategoria === categoria ? 'border-b border-yellow-500/30' : ''"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ categoriaIconos[categoria] || '📦' }}</span>
                            <div>
                                <h2 class="text-lg font-bold" :class="categoriaColores[categoria]?.text || ''">{{ categoria }}</h2>
                                <p class="text-xs text-muted-foreground">{{ productos.length }} producto{{ productos.length !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex gap-1">
                                <div v-for="(prod, i) in productos.slice(0, 4)" :key="i"
                                    class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold shadow-sm"
                                    :class="categoriaColores[categoria]?.badge || 'bg-gray-500'">
                                    <span class="text-white">{{ prod.nombre.charAt(0) }}</span>
                                </div>
                            </div>
                            <svg class="h-5 w-5 transition-transform duration-300 text-muted-foreground"
                                :class="expandedCategoria === categoria ? 'rotate-180' : ''"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>
                    </div>

                    <div
                        class="overflow-hidden transition-all duration-300"
                        :class="expandedCategoria === categoria ? 'max-h-[2000px] opacity-100' : 'max-h-0 opacity-0'"
                    >
                        <div class="p-5">
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                <div
                                    v-for="producto in productos"
                                    :key="producto.id"
                                    class="group relative overflow-hidden rounded-xl border bg-white dark:bg-gray-950 transition-all duration-300 cursor-pointer"
                                    :class="expandedId === producto.id ? 'border-yellow-500 shadow-lg shadow-yellow-500/20' : 'border-border/50 shadow-sm hover:shadow-md hover:border-yellow-500/50'"
                                    @click="toggleExpand(producto.id)"
                                >
                                    <div class="absolute top-0 left-0 right-0 h-1" :class="categoriaColores[producto.categoria]?.badge || 'bg-gray-500'"></div>

                                    <div class="p-4">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-xl shadow-md"
                                                :class="'bg-gradient-to-br ' + (categoriaColores[producto.categoria]?.bg || 'from-gray-500 to-slate-500')">
                                                <span class="text-white font-bold">{{ producto.nombre.charAt(0) }}</span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <h3 class="font-bold text-sm truncate group-hover:text-amber-600 transition-colors">{{ producto.nombre }}</h3>
                                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium text-white"
                                                    :class="categoriaColores[producto.categoria]?.badge || 'bg-gray-500'">
                                                    {{ producto.categoria }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-baseline justify-between mb-2">
                                            <div>
                                                <span class="text-2xl font-bold text-yellow-500">{{ formatCurrency(producto.precio_venta) }}</span>
                                                <span class="text-xs text-muted-foreground"> /{{ producto.unidad_medida }}</span>
                                            </div>
                                            <div class="flex items-center gap-1 rounded-lg px-2 py-1"
                                                :class="producto.stock <= producto.stock_minimo ? 'bg-amber-500/10' : 'bg-emerald-500/10'">
                                                <span class="text-xs font-bold" :class="producto.stock <= producto.stock_minimo ? 'text-amber-500' : 'text-emerald-500'">
                                                    {{ producto.stock }}
                                                </span>
                                                <span class="text-xs text-muted-foreground">{{ getUnidadAbrev(producto.unidad_medida) }}</span>
                                            </div>
                                        </div>

                                        <div v-if="producto.descripcion" class="text-xs text-muted-foreground line-clamp-2 mb-2">
                                            {{ producto.descripcion }}
                                        </div>

                                        <div v-if="producto.stock <= producto.stock_minimo && producto.stock > 0"
                                            class="flex items-center gap-1.5 rounded-lg bg-amber-500/10 px-2 py-1 text-xs text-amber-600 dark:text-amber-400">
                                            <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                                                <line x1="12" x2="12" y1="9" y2="13"/>
                                                <line x1="12" x2="12.01" y1="17" y2="17"/>
                                            </svg>
                                            <span>Stock bajo (mín. {{ producto.stock_minimo }})</span>
                                        </div>

                                        <div v-if="producto.stock === 0"
                                            class="flex items-center gap-1.5 rounded-lg bg-red-500/10 px-2 py-1 text-xs text-red-600 dark:text-red-400">
                                            <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <path d="m15 9-6 6"/>
                                                <path d="m9 9 6 6"/>
                                            </svg>
                                            <span>Sin stock</span>
                                        </div>

                                        <transition name="expand">
                                            <div v-if="expandedId === producto.id" class="mt-3 pt-3 border-t border-border/50">
                                                <div class="grid grid-cols-2 gap-2 text-center text-xs">
                                                    <div class="rounded-lg bg-muted/50 p-2">
                                                        <p class="text-muted-foreground">Precio Compra</p>
                                                        <p class="font-bold">{{ formatCurrency(producto.precio_compra) }}</p>
                                                    </div>
                                                    <div class="rounded-lg bg-muted/50 p-2">
                                                        <p class="text-muted-foreground">Stock Mínimo</p>
                                                        <p class="font-bold">{{ producto.stock_minimo }} {{ getUnidadAbrev(producto.unidad_medida) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="Object.keys(productosPorCategoria).length === 0" class="flex flex-col items-center gap-4 rounded-xl border border-border/50 bg-card p-12">
                <span class="text-5xl">🔍</span>
                <div class="text-center">
                    <p class="text-lg font-bold text-foreground">Sin resultados</p>
                    <p class="text-sm text-muted-foreground">No hay productos que coincidan con tu búsqueda</p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-border/50 bg-card p-3">
            <p class="text-sm text-muted-foreground">
                <span class="font-medium text-foreground">{{ props.productos.length }}</span> productos en
                <span class="font-medium text-foreground">{{ Object.keys(productosPorCategoria).length }}</span> categorías
            </p>
            <div class="flex gap-2">
                <span v-for="cat in categorias" :key="cat.id" class="rounded-full px-2.5 py-1 text-xs font-medium text-white bg-gray-500">
                    {{ cat.icono || '📦' }} {{ stats.cats[cat.nombre] || 0 }}
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.expand-enter-active,
.expand-leave-active {
    transition: all 0.2s ease;
}

.expand-enter-from,
.expand-leave-to {
    opacity: 0;
    max-height: 0;
}
</style>
