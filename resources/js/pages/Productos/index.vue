<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import DialogClose from '@/components/ui/dialog/DialogClose.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { usePermissions } from '@/composables/usePermissions';

const { can } = usePermissions();
const page = usePage();
const productosUrl = '/productos';

const puedeGestionar = can('gestionar_productos');

interface Producto {
    id: number;
    id_categoria: number | null;
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
    updated_at: string;
    categoria_rel?: { id: number; nombre: string; icono: string | null } | null;
}

interface CategoriaItem {
    id: number;
    nombre: string;
    icono: string | null;
}

interface Props {
    productos: {
        data: Producto[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    categorias: CategoriaItem[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedProducto = ref<Producto | null>(null);
const isSubmitting = ref(false);
const activeFilter = ref('todos');
const expandedId = ref<number | null>(null);

const unidadesMedida = [
    'Unidad', 'Kilogramo', 'Libra', 'Litro', 'Quintal', 'Arroba', 'Caja', 'Bolsa', 'Saco', 'Paquete', 'Docena'
];

const createForm = useForm({
    nombre: '',
    id_categoria: '' as string | number,
    categoria: '',
    descripcion: '',
    precio_compra: '',
    precio_venta: '',
    stock: '0',
    stock_minimo: '0',
    unidad_medida: 'Unidad',
});

const editForm = useForm({
    nombre: '',
    id_categoria: '' as string | number,
    categoria: '',
    descripcion: '',
    precio_compra: '',
    precio_venta: '',
    stock: '0',
    stock_minimo: '0',
    unidad_medida: 'Unidad',
});

const deleteForm = useForm({});

const gananciaPorcentaje = computed(() => {
    const compra = parseFloat(createForm.precio_compra) || 0;
    const venta = parseFloat(createForm.precio_venta) || 0;
    if (compra > 0) {
        return (((venta - compra) / compra) * 100).toFixed(1);
    }
    return '0.0';
});

const editGananciaPorcentaje = computed(() => {
    const compra = parseFloat(editForm.precio_compra) || 0;
    const venta = parseFloat(editForm.precio_venta) || 0;
    if (compra > 0) {
        return (((venta - compra) / compra) * 100).toFixed(1);
    }
    return '0.0';
});

const filteredProductos = computed(() => {
    let result = props.productos.data;

    if (activeFilter.value === 'stock_bajo') {
        result = result.filter(p => p.stock <= p.stock_minimo);
    } else if (activeFilter.value === 'con_stock') {
        result = result.filter(p => p.stock > 0);
    } else if (activeFilter.value === 'sin_stock') {
        result = result.filter(p => p.stock === 0);
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(p =>
            p.nombre.toLowerCase().includes(query) ||
            (p.descripcion && p.descripcion.toLowerCase().includes(query)) ||
            p.unidad_medida.toLowerCase().includes(query)
        );
    }

    return result;
});

const stats = computed(() => {
    const total = props.productos.data.length;
    const stockBajo = props.productos.data.filter(p => p.stock <= p.stock_minimo).length;
    const conStock = props.productos.data.filter(p => p.stock > 0).length;
    const sinStock = props.productos.data.filter(p => p.stock === 0).length;

    return {
        total,
        stockBajo,
        conStock,
        sinStock,
        stockBajoPorcentaje: total > 0 ? Math.round((stockBajo / total) * 100) : 0,
        conStockPorcentaje: total > 0 ? Math.round((conStock / total) * 100) : 0,
    };
});

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast.success(flash.success);
    }
    if (flash?.error) {
        toast.error(flash.error);
    }
}, { immediate: true });

function toggleExpand(id: number) {
    expandedId.value = expandedId.value === id ? null : id;
}

function onCreateCategoriaChange() {
    const cat = props.categorias.find(c => c.id === Number(createForm.id_categoria));
    createForm.categoria = cat ? cat.nombre : '';
}

function onEditCategoriaChange() {
    const cat = props.categorias.find(c => c.id === Number(editForm.id_categoria));
    editForm.categoria = cat ? cat.nombre : '';
}

function openCreateModal() {
    createForm.reset();
    createForm.id_categoria = '';
    createForm.categoria = '';
    createForm.stock = '0';
    createForm.stock_minimo = '0';
    createForm.unidad_medida = 'Unidad';
    createForm.clearErrors();
    isCreateModalOpen.value = true;
}

function openEditModal(producto: Producto) {
    selectedProducto.value = producto;
    editForm.id_categoria = producto.id_categoria ?? '';
    editForm.categoria = producto.categoria;
    editForm.descripcion = producto.descripcion || '';
    editForm.precio_compra = String(producto.precio_compra);
    editForm.precio_venta = String(producto.precio_venta);
    editForm.stock = String(producto.stock);
    editForm.stock_minimo = String(producto.stock_minimo);
    editForm.unidad_medida = producto.unidad_medida;
    editForm.clearErrors();
    isEditModalOpen.value = true;
}

function openDeleteModal(producto: Producto) {
    selectedProducto.value = producto;
    isDeleteModalOpen.value = true;
}

function submitCreate() {
    isSubmitting.value = true;
    createForm.post(productosUrl, {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
            toast.success('Producto creado exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function submitEdit() {
    if (!selectedProducto.value) return;
    isSubmitting.value = true;
    editForm.put(`${productosUrl}/${selectedProducto.value.id}`, {
        onSuccess: () => {
            isEditModalOpen.value = false;
            selectedProducto.value = null;
            editForm.reset();
            toast.success('Producto actualizado exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function submitDelete() {
    if (!selectedProducto.value) return;
    isSubmitting.value = true;
    deleteForm.delete(`${productosUrl}/${selectedProducto.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedProducto.value = null;
            toast.success('Producto eliminado exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

function formatCurrency(value: number | string) {
    return new Intl.NumberFormat('es-HN', {
        style: 'currency',
        currency: 'HNL'
    }).format(parseFloat(String(value)));
}

function getUnidadAbreviada(unidad: string): string {
    const mapa: Record<string, string> = {
        'Unidad': 'unid',
        'Kilogramo': 'kg',
        'Libra': 'lb',
        'Litro': 'L',
        'Quintal': 'qq',
        'Arroba': '@',
        'Caja': 'caja',
        'Bolsa': 'bolsa',
        'Saco': 'saco',
        'Paquete': 'pqte',
        'Docena': 'doc',
    };
    return mapa[unidad] || unidad;
}
</script>

<template>
    <Head title="Productos - Panadería Delicias" />

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
            <div class="absolute right-1/3 bottom-0 h-28 w-28 rounded-full bg-black/40 blur-2xl animate-pulse" style="animation-delay: 0.5s"></div>

            <div class="relative flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-red-600 to-yellow-500 shadow-xl shadow-red-500/30">
                            <svg class="h-7 w-7 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                                <line x1="3" x2="21" y1="6" y2="6"/>
                                <path d="M16 10a4 4 0 0 1-8 0"/>
                            </svg>
                        </div>
                        <div class="absolute -bottom-1.5 -right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 shadow-lg">
                            <svg class="h-3 w-3 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Gestión de Productos</h1>
                        <p class="text-yellow-200 text-sm">Control de inventario y stock</p>
                        <div class="mt-1 flex items-center gap-2">
                            <div class="flex items-center gap-1 rounded-full bg-white/10 px-2 py-0.5">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-yellow-400"></span>
                                <span class="text-xs font-medium text-white">Activo</span>
                            </div>
                        </div>
                    </div>
                </div>
                <Button
                    v-if="puedeGestionar"
                    @click="openCreateModal"
                    class="group relative overflow-hidden bg-gradient-to-r from-yellow-400 to-yellow-500 px-5 py-2.5 text-black shadow-xl shadow-yellow-500/30 transition-all hover:scale-105 hover:from-yellow-500 hover:to-yellow-600"
                >
                    <span class="absolute inset-0 bg-gradient-to-r from-yellow-300 to-yellow-400 opacity-0 transition-opacity group-hover:opacity-100"></span>
                    <span class="relative flex items-center gap-2 font-bold text-sm">
                        <svg class="h-4 w-4 transition-transform group-hover:rotate-90" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" x2="12" y1="5" y2="19"/>
                            <line x1="5" x2="19" y1="12" y2="12"/>
                        </svg>
                        Nuevo Producto
                    </span>
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-red-500/10 cursor-pointer" @click="activeFilter = 'todos'">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-red-500/20 to-yellow-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-600 to-yellow-500 shadow-md shadow-red-500/30">
                        <span class="text-base">📦</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Total Productos</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ stats.total }}</span>
                    </div>
                    <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-muted">
                        <div class="h-full w-full rounded-full bg-gradient-to-r from-red-600 to-yellow-500"></div>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-green-500/10 cursor-pointer" @click="activeFilter = 'con_stock'">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-green-500/20 to-emerald-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 shadow-md shadow-green-500/30">
                        <span class="text-base">✅</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Con Stock</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ stats.conStock }}</span>
                        <span class="text-xs font-medium text-green-500">{{ stats.conStockPorcentaje }}%</span>
                    </div>
                    <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-muted">
                        <div class="h-full rounded-full bg-gradient-to-r from-green-500 to-emerald-500 transition-all" :style="{ width: stats.conStockPorcentaje + '%' }"></div>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-amber-500/10 cursor-pointer" @click="activeFilter = 'stock_bajo'">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-amber-400/20 to-orange-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 shadow-md shadow-amber-500/30">
                        <span class="text-base">⚠️</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Stock Bajo</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ stats.stockBajo }}</span>
                        <span class="text-xs font-medium text-amber-500">{{ stats.stockBajoPorcentaje }}%</span>
                    </div>
                    <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-muted">
                        <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all" :style="{ width: stats.stockBajoPorcentaje + '%' }"></div>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-red-500/10 cursor-pointer" @click="activeFilter = 'sin_stock'">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-red-400/20 to-rose-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-rose-500 shadow-md shadow-red-500/30">
                        <span class="text-base">❌</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Sin Stock</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ stats.sinStock }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-1.5">
                <button
                    @click="activeFilter = 'todos'"
                    :class="[
                        'rounded-lg px-2.5 py-1 text-xs font-medium transition-all',
                        activeFilter === 'todos' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-md shadow-red-500/30' : 'bg-card border border-border/50 text-muted-foreground hover:bg-muted'
                    ]"
                >
                    Todos ({{ stats.total }})
                </button>
                <button
                    @click="activeFilter = 'con_stock'"
                    :class="[
                        'rounded-lg px-2.5 py-1 text-xs font-medium transition-all',
                        activeFilter === 'con_stock' ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-md shadow-green-500/30' : 'bg-card border border-border/50 text-muted-foreground hover:bg-muted'
                    ]"
                >
                    ✅ Con stock ({{ stats.conStock }})
                </button>
                <button
                    @click="activeFilter = 'stock_bajo'"
                    :class="[
                        'rounded-lg px-2.5 py-1 text-xs font-medium transition-all',
                        activeFilter === 'stock_bajo' ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-black shadow-md shadow-amber-500/30' : 'bg-card border border-border/50 text-muted-foreground hover:bg-muted'
                    ]"
                >
                    ⚠️ Stock bajo ({{ stats.stockBajo }})
                </button>
                <button
                    @click="activeFilter = 'sin_stock'"
                    :class="[
                        'rounded-lg px-2.5 py-1 text-xs font-medium transition-all',
                        activeFilter === 'sin_stock' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-md shadow-red-500/30' : 'bg-card border border-border/50 text-muted-foreground hover:bg-muted'
                    ]"
                >
                    ❌ Sin stock ({{ stats.sinStock }})
                </button>
            </div>

            <div class="relative w-full sm:max-w-xs">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <Input
                    v-model="searchQuery"
                    placeholder="Buscar productos..."
                    class="pl-10 h-9 rounded-lg border-border/50 bg-card shadow-sm text-sm"
                />
            </div>
        </div>

        <!-- Product Cards -->
        <div class="flex-1 overflow-auto space-y-2">
            <div
                v-for="producto in filteredProductos"
                :key="producto.id"
                class="relative overflow-hidden rounded-xl border transition-all duration-300"
                :class="[
                    expandedId === producto.id ? 'border-yellow-500 shadow-lg shadow-yellow-500/20' : 'border-border/50 bg-card shadow-sm hover:shadow-md',
                    producto.stock <= producto.stock_minimo ? 'border-amber-600' : 'border-border/50'
                ]"
            >
                <div
                    class="relative cursor-pointer"
                    @click="toggleExpand(producto.id)"
                >
                    <div
                        class="absolute left-0 top-0 bottom-0 w-1"
                        :class="producto.stock <= producto.stock_minimo ? 'bg-amber-500' : 'bg-emerald-500'"
                    ></div>

                    <div class="flex items-center gap-3 p-3 pl-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-red-600 to-yellow-500 text-lg font-bold text-white shadow-md">
                            {{ producto.nombre.charAt(0) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5">
                                <h3 class="font-bold text-sm truncate">{{ producto.nombre }}</h3>
                                <div class="flex items-center gap-1">
                                    <span class="shrink-0 rounded-full bg-red-600/10 px-2 py-0.5 text-xs font-medium text-red-600">{{ producto.categoria }}</span>
                                    <span
                                        v-if="producto.stock <= producto.stock_minimo"
                                        class="shrink-0 rounded-full bg-amber-500 px-2 py-0.5 text-xs font-bold text-white"
                                    >
                                        Stock Bajo
                                    </span>
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground">{{ producto.unidad_medida }} · {{ producto.descripcion || 'Sin descripción' }}</p>
                        </div>

                        <div class="text-right shrink-0">
                            <div class="flex items-center gap-1 justify-end">
                                <span class="text-lg font-bold" :class="producto.stock <= producto.stock_minimo ? 'text-amber-500' : 'text-emerald-500'">{{ producto.stock }}</span>
                                <span class="text-xs text-muted-foreground">{{ getUnidadAbreviada(producto.unidad_medida) }}</span>
                            </div>
                            <p class="text-xs text-muted-foreground">Stock actual</p>
                        </div>

                        <div class="shrink-0 transition-transform duration-300" :class="expandedId === producto.id ? 'rotate-180' : ''">
                            <svg class="h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="overflow-hidden transition-all duration-300"
                    :class="expandedId === producto.id ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'"
                >
                    <div class="border-t border-border/50 bg-muted/30 p-3">
                        <div class="mb-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <div class="rounded-lg bg-background/80 p-2 text-center">
                                <p class="text-xs text-muted-foreground">Precio Compra</p>
                                <p class="text-sm font-bold">{{ formatCurrency(producto.precio_compra) }}</p>
                            </div>
                            <div class="rounded-lg bg-background/80 p-2 text-center">
                                <p class="text-xs text-muted-foreground">Precio Venta</p>
                                <p class="text-sm font-bold text-yellow-500">{{ formatCurrency(producto.precio_venta) }}</p>
                            </div>
                            <div class="rounded-lg bg-background/80 p-2 text-center">
                                <p class="text-xs text-muted-foreground">Stock Mínimo</p>
                                <p class="text-sm font-bold">{{ producto.stock_minimo }}</p>
                            </div>
                            <div class="rounded-lg bg-background/80 p-2 text-center">
                                <p class="text-xs text-muted-foreground">Unidad</p>
                                <p class="text-sm font-bold">{{ producto.unidad_medida }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5">
                            <Button
                                v-if="puedeGestionar"
                                @click.stop="openEditModal(producto)"
                                size="sm"
                                variant="outline"
                                class="flex-1 gap-1 border-emerald-500/50 text-emerald-600 hover:bg-emerald-500/10 text-xs h-8"
                            >
                                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                    <path d="m15 5 4 4"/>
                                </svg>
                                Editar
                            </Button>
                            <Button
                                v-if="puedeGestionar"
                                @click.stop="openDeleteModal(producto)"
                                size="sm"
                                variant="outline"
                                class="flex-1 gap-1 border-red-500/50 text-red-600 hover:bg-red-500/10 text-xs h-8"
                            >
                                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"/>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                </svg>
                                Eliminar
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filteredProductos.length === 0" class="flex flex-col items-center gap-3 rounded-xl border border-border/50 bg-card p-8">
                <div class="relative">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-red-600 to-yellow-500">
                        <span class="text-3xl">📦</span>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-base font-bold text-foreground">No hay productos registrados</p>
                    <p class="mt-1 text-sm text-muted-foreground">Comienza agregando tu primer producto</p>
                </div>
                <Button v-if="puedeGestionar" @click="openCreateModal" class="bg-gradient-to-r from-red-600 to-yellow-500 hover:from-red-700 hover:to-yellow-600">
                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="5" y2="19"/>
                        <line x1="5" x2="19" y1="12" y2="12"/>
                    </svg>
                    Nuevo Producto
                </Button>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-xl border border-border/50 bg-card p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-600 to-yellow-500">
                    <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                        <line x1="3" x2="21" y1="6" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium">Mostrando {{ filteredProductos.length }} de {{ props.productos.total }} productos</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-amber-500/10 px-3 py-1.5 text-xs font-medium text-amber-600">
                    📄 Página {{ props.productos.current_page }} de {{ props.productos.last_page }}
                </span>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
        <DialogContent class="sm:max-w-2xl">
            <div class="absolute inset-x-0 -top-px h-24 bg-gradient-to-r from-transparent via-amber-500/20 to-transparent"></div>
            <DialogHeader>
                <div class="flex flex-col items-center gap-2 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 to-yellow-500 shadow-lg shadow-red-500/30">
                        <span class="text-2xl">📦</span>
                    </div>
                    <DialogTitle class="text-xl">Nuevo Producto</DialogTitle>
                </div>
            </DialogHeader>
            <form @submit.prevent="submitCreate" class="grid gap-3 py-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="create-nombre" class="text-xs font-medium">
                            Nombre <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="create-nombre"
                            v-model="createForm.nombre"
                            placeholder="Ej: Harina de Trigo"
                            class="h-9"
                            required
                        />
                        <InputError :message="createForm.errors.nombre" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="create-id_categoria" class="text-xs font-medium">
                            Categoría <span class="text-red-500">*</span>
                        </Label>
                        <select
                            id="create-id_categoria"
                            v-model="createForm.id_categoria"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                            required
                            @change="onCreateCategoriaChange"
                        >
                            <option value="" disabled>Seleccionar categoría</option>
                            <option v-for="cat in props.categorias" :key="cat.id" :value="cat.id">{{ cat.icono }} {{ cat.nombre }}</option>
                        </select>
                        <InputError :message="createForm.errors.id_categoria" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="create-unidad" class="text-xs font-medium">
                            Unidad de Medida <span class="text-red-500">*</span>
                        </Label>
                        <select
                            id="create-unidad"
                            v-model="createForm.unidad_medida"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                            required
                        >
                            <option v-for="u in unidadesMedida" :key="u" :value="u">{{ u }}</option>
                        </select>
                        <InputError :message="createForm.errors.unidad_medida" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="create-descripcion" class="text-xs font-medium">Descripción</Label>
                        <textarea
                            id="create-descripcion"
                            v-model="createForm.descripcion"
                            rows="2"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                            placeholder="Descripción del producto..."
                        ></textarea>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="create-precio-compra" class="text-xs font-medium">
                            Precio Compra (HNL) <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="create-precio-compra"
                            type="number"
                            step="0.01"
                            v-model="createForm.precio_compra"
                            min="0"
                            placeholder="0.00"
                            class="h-9"
                            required
                        />
                        <InputError :message="createForm.errors.precio_compra" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="create-precio-venta" class="text-xs font-medium">
                            Precio Venta (HNL) <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="create-precio-venta"
                            type="number"
                            step="0.01"
                            v-model="createForm.precio_venta"
                            min="0"
                            placeholder="0.00"
                            class="h-9"
                            required
                        />
                        <InputError :message="createForm.errors.precio_venta" />
                    </div>
                </div>
                <div v-if="parseFloat(createForm.precio_compra) > 0" class="rounded-lg bg-emerald-500/10 p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">Margen de Ganancia:</span>
                        <span class="text-lg font-bold text-emerald-500">{{ gananciaPorcentaje }}%</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="create-stock" class="text-xs font-medium">
                            Stock Actual <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="create-stock"
                            type="number"
                            v-model="createForm.stock"
                            min="0"
                            placeholder="0"
                            class="h-9"
                            required
                        />
                        <InputError :message="createForm.errors.stock" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="create-stock-minimo" class="text-xs font-medium">
                            Stock Mínimo <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="create-stock-minimo"
                            type="number"
                            v-model="createForm.stock_minimo"
                            min="0"
                            placeholder="0"
                            class="h-9"
                            required
                        />
                        <InputError :message="createForm.errors.stock_minimo" />
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end mt-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="h-10 rounded-lg border-2 border-red-500/50 text-red-600 hover:bg-red-500/10 sm:w-auto"
                        @click="isCreateModalOpen = false"
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="submit"
                        :disabled="createForm.processing || isSubmitting"
                        class="h-10 rounded-lg bg-gradient-to-r from-red-600 to-yellow-500 hover:from-red-700 hover:to-yellow-600 shadow-lg shadow-yellow-500/30 sm:w-auto"
                    >
                        <svg v-if="createForm.processing" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                        Guardar Producto
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Edit Modal -->
    <Dialog :open="isEditModalOpen" @update:open="isEditModalOpen = $event">
        <DialogContent class="sm:max-w-2xl">
            <div class="absolute inset-x-0 -top-px h-24 bg-gradient-to-r from-transparent via-blue-500/20 to-transparent"></div>
            <DialogHeader>
                <div class="flex flex-col items-center gap-2 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-500 shadow-lg shadow-blue-500/30">
                        <span class="text-2xl">✏️</span>
                    </div>
                    <DialogTitle class="text-xl">Editar Producto</DialogTitle>
                </div>
            </DialogHeader>
            <form @submit.prevent="submitEdit" class="grid gap-3 py-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="edit-nombre" class="text-xs font-medium">
                            Nombre <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="edit-nombre"
                            v-model="editForm.nombre"
                            class="h-9"
                            required
                        />
                        <InputError :message="editForm.errors.nombre" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="edit-id_categoria" class="text-xs font-medium">
                            Categoría <span class="text-red-500">*</span>
                        </Label>
                        <select
                            id="edit-id_categoria"
                            v-model="editForm.id_categoria"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                            required
                            @change="onEditCategoriaChange"
                        >
                            <option value="" disabled>Seleccionar categoría</option>
                            <option v-for="cat in props.categorias" :key="cat.id" :value="cat.id">{{ cat.icono }} {{ cat.nombre }}</option>
                        </select>
                        <InputError :message="editForm.errors.id_categoria" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="edit-unidad" class="text-xs font-medium">
                            Unidad de Medida <span class="text-red-500">*</span>
                        </Label>
                        <select
                            id="edit-unidad"
                            v-model="editForm.unidad_medida"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                            required
                        >
                            <option v-for="u in unidadesMedida" :key="u" :value="u">{{ u }}</option>
                        </select>
                        <InputError :message="editForm.errors.unidad_medida" />
                    </div>
                </div>
                <div class="grid gap-1.5">
                    <Label for="edit-descripcion" class="text-xs font-medium">Descripción</Label>
                    <textarea
                        id="edit-descripcion"
                        v-model="editForm.descripcion"
                        rows="2"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    ></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="edit-precio-compra" class="text-xs font-medium">
                            Precio Compra (HNL) <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="edit-precio-compra"
                            type="number"
                            step="0.01"
                            v-model="editForm.precio_compra"
                            min="0"
                            class="h-9"
                            required
                        />
                        <InputError :message="editForm.errors.precio_compra" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="edit-precio-venta" class="text-xs font-medium">
                            Precio Venta (HNL) <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="edit-precio-venta"
                            type="number"
                            step="0.01"
                            v-model="editForm.precio_venta"
                            min="0"
                            class="h-9"
                            required
                        />
                        <InputError :message="editForm.errors.precio_venta" />
                    </div>
                </div>
                <div v-if="parseFloat(editForm.precio_compra) > 0" class="rounded-lg bg-emerald-500/10 p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">Margen de Ganancia:</span>
                        <span class="text-lg font-bold text-emerald-500">{{ editGananciaPorcentaje }}%</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="edit-stock" class="text-xs font-medium">
                            Stock Actual <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="edit-stock"
                            type="number"
                            v-model="editForm.stock"
                            min="0"
                            class="h-9"
                            required
                        />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="edit-stock-minimo" class="text-xs font-medium">
                            Stock Mínimo <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="edit-stock-minimo"
                            type="number"
                            v-model="editForm.stock_minimo"
                            min="0"
                            class="h-9"
                            required
                        />
                    </div>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end mt-2">
                    <Button
                        type="button"
                        variant="outline"
                        class="h-10 rounded-lg border-2 border-red-500/50 text-red-600 hover:bg-red-500/10 sm:w-auto"
                        @click="isEditModalOpen = false"
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="submit"
                        :disabled="editForm.processing || isSubmitting"
                        class="h-10 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 shadow-lg shadow-blue-500/30 sm:w-auto"
                    >
                        <svg v-if="editForm.processing" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                            <path d="m15 5 4 4"/>
                        </svg>
                        Guardar Cambios
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Modal -->
    <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
        <DialogContent class="sm:max-w-md">
            <div class="absolute inset-x-0 -top-px h-24 bg-gradient-to-r from-transparent via-red-600/20 to-transparent"></div>
            <DialogHeader>
                <div class="flex flex-col items-center gap-2 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 to-red-900 shadow-lg shadow-red-500/30">
                        <span class="text-2xl">⚠️</span>
                    </div>
                    <DialogTitle class="text-xl">Eliminar Producto</DialogTitle>
                </div>
            </DialogHeader>
            <div class="py-3">
                <div class="mb-3 overflow-hidden rounded-xl border border-red-500/20 bg-gradient-to-br from-red-600/5 to-yellow-500/5 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-red-600 to-yellow-500 text-lg font-bold text-white">
                            {{ selectedProducto?.nombre?.charAt(0) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold">{{ selectedProducto?.nombre }}</p>
                            <p class="text-sm text-muted-foreground">Stock: {{ selectedProducto?.stock }} {{ selectedProducto?.unidad_medida }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl bg-yellow-500/10 p-3">
                    <div class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                            <line x1="12" x2="12" y1="9" y2="13"/>
                            <line x1="12" x2="12.01" y1="17" y2="17"/>
                        </svg>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400">
                            ¿Estás seguro de eliminar este producto? Las compras asociadas conservarán el nombre pero perderán la referencia.
                        </p>
                    </div>
                </div>
            </div>
            <DialogFooter class="gap-2 mt-2">
                <DialogClose as-child>
                    <Button type="button" variant="outline" class="h-10 rounded-lg">Cancelar</Button>
                </DialogClose>
                <Button @click="submitDelete" :disabled="deleteForm.processing || isSubmitting" class="h-10 rounded-lg bg-gradient-to-r from-red-600 to-red-900 hover:from-red-700 hover:to-red-950 shadow-lg">
                    <svg v-if="deleteForm.processing" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg v-else class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"/>
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                    </svg>
                    Eliminar Producto
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
