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
const comprasUrl = '/compras';

const puedeGestionar = can('gestionar_compras');

interface Compra {
    id: number;
    id_proveedor: number | null;
    id_producto: number | null;
    proveedor: string;
    producto: string;
    cantidad: number;
    precio_unitario: number;
    total: number;
    fecha_compra: string;
    estado: 'completado' | 'pendiente' | 'cancelado';
    observaciones: string | null;
    created_at: string;
    updated_at: string;
    proveedor_rel?: { id: number; nombre: string } | null;
    producto_rel?: { id: number; nombre: string; precio_compra: number; stock: number; unidad_medida: string } | null;
}

interface ProveedorOption {
    id: number;
    nombre: string;
}

interface ProductoOption {
    id: number;
    nombre: string;
    precio_compra: number;
    stock: number;
    unidad_medida: string;
}

interface Props {
    compras: {
        data: Compra[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    proveedores: ProveedorOption[];
    productos: ProductoOption[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isViewModalOpen = ref(false);
const selectedCompra = ref<Compra | null>(null);
const isSubmitting = ref(false);
const activeFilter = ref('todos');
const expandedId = ref<number | null>(null);

const createForm = useForm({
    id_proveedor: '',
    id_producto: '',
    proveedor: '',
    producto: '',
    cantidad: '',
    precio_unitario: '',
    fecha_compra: new Date().toISOString().split('T')[0],
    estado: 'pendiente',
    observaciones: '',
});

const editForm = useForm({
    id_proveedor: '',
    id_producto: '',
    proveedor: '',
    producto: '',
    cantidad: '',
    precio_unitario: '',
    fecha_compra: '',
    estado: 'pendiente',
    observaciones: '',
});

const deleteForm = useForm({});

function onProveedorChange(value: string, form: typeof createForm | typeof editForm) {
    const id = parseInt(value);
    if (id) {
        const prov = props.proveedores.find(p => p.id === id);
        if (prov) {
            form.proveedor = prov.nombre;
        }
    } else {
        form.proveedor = '';
    }
}

function onProductoChange(value: string, form: typeof createForm | typeof editForm) {
    const id = parseInt(value);
    if (id) {
        const prod = props.productos.find(p => p.id === id);
        if (prod) {
            form.producto = prod.nombre;
            if (!form.precio_unitario || parseFloat(form.precio_unitario) === 0) {
                form.precio_unitario = String(prod.precio_compra);
            }
        }
    } else {
        form.producto = '';
    }
}

const totalCalculado = computed(() => {
    const cantidad = parseFloat(createForm.cantidad) || 0;
    const precio = parseFloat(createForm.precio_unitario) || 0;
    return (cantidad * precio).toFixed(2);
});

const editTotalCalculado = computed(() => {
    const cantidad = parseFloat(editForm.cantidad) || 0;
    const precio = parseFloat(editForm.precio_unitario) || 0;
    return (cantidad * precio).toFixed(2);
});

const filteredCompras = computed(() => {
    let result = props.compras.data;
    
    if (activeFilter.value !== 'todos') {
        result = result.filter(c => c.estado === activeFilter.value);
    }
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(compra =>
            compra.proveedor.toLowerCase().includes(query) ||
            compra.producto.toLowerCase().includes(query)
        );
    }
    
    return result;
});

const stats = computed(() => {
    const total = props.compras.data.reduce((sum, c) => sum + parseFloat(String(c.total)), 0);
    const completadas = props.compras.data.filter(c => c.estado === 'completado');
    const pendientes = props.compras.data.filter(c => c.estado === 'pendiente');
    const canceladas = props.compras.data.filter(c => c.estado === 'cancelado');
    
    return {
        total: total.toFixed(2),
        totalCount: props.compras.data.length,
        completadasCount: completadas.length,
        pendientesCount: pendientes.length,
        canceladasCount: canceladas.length,
        completadasTotal: completadas.reduce((sum, c) => sum + parseFloat(String(c.total)), 0).toFixed(2),
        pendientesTotal: pendientes.reduce((sum, c) => sum + parseFloat(String(c.total)), 0).toFixed(2),
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

function openCreateModal() {
    createForm.reset();
    createForm.fecha_compra = new Date().toISOString().split('T')[0];
    createForm.estado = 'pendiente';
    createForm.clearErrors();
    isCreateModalOpen.value = true;
}

function openEditModal(compra: Compra) {
    expandedId.value = null;
    selectedCompra.value = compra;
    editForm.id_proveedor = compra.id_proveedor ? String(compra.id_proveedor) : '';
    editForm.id_producto = compra.id_producto ? String(compra.id_producto) : '';
    editForm.proveedor = compra.proveedor_rel?.nombre || compra.proveedor;
    editForm.producto = compra.producto_rel?.nombre || compra.producto;
    editForm.cantidad = String(compra.cantidad);
    editForm.precio_unitario = String(compra.precio_unitario);
    editForm.fecha_compra = compra.fecha_compra;
    editForm.estado = compra.estado;
    editForm.observaciones = compra.observaciones || '';
    editForm.clearErrors();
    isEditModalOpen.value = true;
}

function openViewModal(compra: Compra) {
    expandedId.value = null;
    selectedCompra.value = compra;
    isViewModalOpen.value = true;
}

function openDeleteModal(compra: Compra) {
    expandedId.value = null;
    selectedCompra.value = compra;
    isDeleteModalOpen.value = true;
}

function submitCreate() {
    isSubmitting.value = true;
    createForm.post(comprasUrl, {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
            toast.success('Compra registrada exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function submitEdit() {
    if (!selectedCompra.value) return;
    isSubmitting.value = true;
    editForm.put(`${comprasUrl}/${selectedCompra.value.id}`, {
        onSuccess: () => {
            isEditModalOpen.value = false;
            selectedCompra.value = null;
            editForm.reset();
            toast.success('Compra actualizada exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function submitDelete() {
    if (!selectedCompra.value) return;
    isSubmitting.value = true;
    deleteForm.delete(`${comprasUrl}/${selectedCompra.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedCompra.value = null;
            toast.success('Compra eliminada exitosamente');
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

const estadoColors: Record<string, { bg: string; text: string; border: string }> = {
    completado: { bg: 'bg-emerald-500', text: 'text-white', border: 'border-emerald-600' },
    pendiente: { bg: 'bg-amber-500', text: 'text-white', border: 'border-amber-600' },
    cancelado: { bg: 'bg-red-500', text: 'text-white', border: 'border-red-600' },
};
</script>

<template>
    <Head title="Compras - Panadería Delicias" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-hidden p-4">
        <!-- Dynamic Header -->
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
                                <path d="m7.5 4.27 9 5.15"/>
                                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                <path d="m3.3 7 8.7 5 8.7-5"/>
                                <path d="M12 22V12"/>
                            </svg>
                        </div>
                        <div class="absolute -bottom-1.5 -right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 shadow-lg">
                            <svg class="h-3 w-3 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Gestión de Compras</h1>
                        <p class="text-yellow-200 text-sm">Controla el inventario</p>
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
                        Nueva Compra
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
                    <p class="text-xs font-medium text-muted-foreground">Total Compras</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ stats.totalCount }}</span>
                    </div>
                    <div class="mt-1.5 flex items-center gap-1">
                        <span class="text-xs font-medium text-yellow-500">{{ formatCurrency(stats.total) }}</span>
                        <span class="text-xs text-muted-foreground">HNL</span>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-green-500/10 cursor-pointer" @click="activeFilter = 'completado'">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-green-500/20 to-emerald-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 shadow-md shadow-green-500/30">
                        <span class="text-base">✅</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Completadas</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ stats.completadasCount }}</span>
                    </div>
                    <div class="mt-1.5 flex items-center gap-1">
                        <span class="text-xs font-medium text-green-500">{{ formatCurrency(stats.completadasTotal) }}</span>
                        <span class="text-xs text-muted-foreground">HNL</span>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-amber-500/10 cursor-pointer" @click="activeFilter = 'pendiente'">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-amber-400/20 to-orange-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 shadow-md shadow-amber-500/30">
                        <span class="text-base">⏳</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Pendientes</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ stats.pendientesCount }}</span>
                    </div>
                    <div class="mt-1.5 flex items-center gap-1">
                        <span class="text-xs font-medium text-amber-500">{{ formatCurrency(stats.pendientesTotal) }}</span>
                        <span class="text-xs text-muted-foreground">HNL</span>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-red-500/10 cursor-pointer" @click="activeFilter = 'cancelado'">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-red-400/20 to-rose-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-rose-500 shadow-md shadow-red-500/30">
                        <span class="text-base">❌</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Canceladas</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ stats.canceladasCount }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-1.5">
                <button 
                    @click="activeFilter = 'todos'"
                    :class="[
                        'rounded-lg px-2.5 py-1 text-xs font-medium transition-all',
                        activeFilter === 'todos' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-md shadow-red-500/30' : 'bg-card border border-border/50 text-muted-foreground hover:bg-muted'
                    ]"
                >
                    Todos ({{ stats.totalCount }})
                </button>
                <button 
                    @click="activeFilter = 'completado'"
                    :class="[
                        'rounded-lg px-2.5 py-1 text-xs font-medium transition-all',
                        activeFilter === 'completado' ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-md shadow-green-500/30' : 'bg-card border border-border/50 text-muted-foreground hover:bg-muted'
                    ]"
                >
                    ✅ Completadas ({{ stats.completadasCount }})
                </button>
                <button 
                    @click="activeFilter = 'pendiente'"
                    :class="[
                        'rounded-lg px-2.5 py-1 text-xs font-medium transition-all',
                        activeFilter === 'pendiente' ? 'bg-gradient-to-r from-yellow-500 to-amber-500 text-black shadow-md shadow-yellow-500/30' : 'bg-card border border-border/50 text-muted-foreground hover:bg-muted'
                    ]"
                >
                    ⏳ Pendientes ({{ stats.pendientesCount }})
                </button>
                <button 
                    @click="activeFilter = 'cancelado'"
                    :class="[
                        'rounded-lg px-2.5 py-1 text-xs font-medium transition-all',
                        activeFilter === 'cancelado' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-md shadow-red-500/30' : 'bg-card border border-border/50 text-muted-foreground hover:bg-muted'
                    ]"
                >
                    ❌ Canceladas ({{ stats.canceladasCount }})
                </button>
            </div>
            
            <div class="relative w-full sm:max-w-xs">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <Input
                    v-model="searchQuery"
                    placeholder="Buscar..."
                    class="pl-10 h-9 rounded-lg border-border/50 bg-card shadow-sm text-sm"
                />
            </div>
        </div>

        <!-- Cards List with Swipe/Expand -->
        <div class="flex-1 overflow-auto space-y-2">
            <div
                v-for="compra in filteredCompras"
                :key="compra.id"
                class="relative overflow-hidden rounded-xl border transition-all duration-300"
                :class="[
                    expandedId === compra.id ? 'border-yellow-500 shadow-lg shadow-yellow-500/20' : 'border-border/50 bg-card shadow-sm hover:shadow-md',
                    estadoColors[compra.estado].border
                ]"
            >
                <!-- Main Card -->
                <div 
                    class="relative cursor-pointer"
                    @click="toggleExpand(compra.id)"
                >
                    <!-- Status Indicator -->
                    <div 
                        class="absolute left-0 top-0 bottom-0 w-1"
                        :class="estadoColors[compra.estado].bg"
                    ></div>
                    
                    <div class="flex items-center gap-3 p-3 pl-4">
                        <!-- Avatar -->
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-red-600 to-yellow-500 text-lg font-bold text-white shadow-md">
                            {{ (compra.proveedor_rel?.nombre || compra.proveedor).charAt(0) }}
                        </div>
                        
                        <!-- Main Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5">
                                <h3 class="font-bold text-sm truncate">{{ compra.proveedor_rel?.nombre || compra.proveedor }}</h3>
                                <span 
                                    class="shrink-0 rounded-full px-2 py-0.5 text-xs font-bold"
                                    :class="[estadoColors[compra.estado].bg, estadoColors[compra.estado].text]"
                                >
                                    {{ compra.estado.charAt(0).toUpperCase() + compra.estado.slice(1) }}
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground truncate">{{ compra.producto_rel?.nombre || compra.producto }}</p>
                        </div>
                        
                        <!-- Price -->
                        <div class="text-right shrink-0">
                            <p class="text-base font-bold text-yellow-500">{{ formatCurrency(compra.total) }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDate(compra.fecha_compra) }}</p>
                        </div>
                        
                        <!-- Expand Arrow -->
                        <div class="shrink-0 transition-transform duration-300" :class="expandedId === compra.id ? 'rotate-180' : ''">
                            <svg class="h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Expanded Details -->
                <div 
                    class="overflow-hidden transition-all duration-300"
                    :class="expandedId === compra.id ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'"
                >
                    <div class="border-t border-border/50 bg-muted/30 p-3">
                        <!-- Quick Info Grid -->
                        <div class="mb-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <div class="rounded-lg bg-background/80 p-2 text-center">
                                <p class="text-xs text-muted-foreground">Cantidad</p>
                                <p class="text-sm font-bold">{{ compra.cantidad }}</p>
                            </div>
                            <div class="rounded-lg bg-background/80 p-2 text-center">
                                <p class="text-xs text-muted-foreground">Precio Unit.</p>
                                <p class="text-sm font-bold">{{ formatCurrency(compra.precio_unitario) }}</p>
                            </div>
                            <div class="rounded-lg bg-background/80 p-2 text-center">
                                <p class="text-xs text-muted-foreground">Total</p>
                                <p class="text-sm font-bold text-yellow-500">{{ formatCurrency(compra.total) }}</p>
                            </div>
                            <div class="rounded-lg bg-background/80 p-2 text-center">
                                <p class="text-xs text-muted-foreground">Fecha</p>
                                <p class="text-sm font-bold">{{ formatDate(compra.fecha_compra) }}</p>
                            </div>
                        </div>
                        
                        <!-- Observations -->
                        <div v-if="compra.observaciones" class="mb-3 rounded-lg bg-yellow-500/10 p-2">
                            <div class="flex items-start gap-1.5">
                                <svg class="mt-0.5 h-3 w-3 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                </svg>
                                <p class="text-xs">{{ compra.observaciones }}</p>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-1.5">
                            <Button 
                                @click.stop="openViewModal(compra)"
                                size="sm" 
                                variant="outline" 
                                class="flex-1 gap-1 text-xs h-8"
                            >
                                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Ver
                            </Button>
                            <Button
                                v-if="puedeGestionar"
                                @click.stop="openEditModal(compra)"
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
                                @click.stop="openDeleteModal(compra)"
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

            <!-- Empty State -->
            <div v-if="filteredCompras.length === 0" class="flex flex-col items-center gap-3 rounded-xl border border-border/50 bg-card p-8">
                <div class="relative">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-red-600 to-yellow-500">
                        <span class="text-3xl">📦</span>
                    </div>
                </div>
                <div class="text-center">
                    <p class="text-base font-bold text-foreground">No hay compras registradas</p>
                    <p class="mt-1 text-sm text-muted-foreground">Comienza registrando tu primera compra</p>
                </div>
                <Button v-if="puedeGestionar" @click="openCreateModal" class="bg-gradient-to-r from-red-600 to-yellow-500 hover:from-red-700 hover:to-yellow-600">
                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="5" y2="19"/>
                        <line x1="5" x2="19" y1="12" y2="12"/>
                    </svg>
                    Nueva Compra
                </Button>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex flex-col gap-4 rounded-xl border border-border/50 bg-card p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-600 to-yellow-500">
                    <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m7.5 4.27 9 5.15"/>
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium">Mostrando {{ filteredCompras.length }} de {{ props.compras.total }} compras</p>
                    <p class="text-xs text-muted-foreground">Total filtrado: {{ formatCurrency(filteredCompras.reduce((sum, c) => sum + parseFloat(String(c.total)), 0)) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-yellow-500/10 px-3 py-1.5 text-xs font-medium text-yellow-600">
                    📄 Página {{ props.compras.current_page }} de {{ props.compras.last_page }}
                </span>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
        <DialogContent class="sm:max-w-2xl">
            <div class="absolute inset-x-0 -top-px h-24 bg-gradient-to-r from-transparent via-red-500/20 to-transparent"></div>
            <DialogHeader>
                <div class="flex flex-col items-center gap-2 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 to-yellow-500 shadow-lg shadow-red-500/30">
                        <span class="text-2xl">🛒</span>
                    </div>
                    <DialogTitle class="text-xl">Nueva Compra</DialogTitle>
                </div>
            </DialogHeader>
            <form @submit.prevent="submitCreate" class="grid gap-3 py-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="create-proveedor" class="text-xs font-medium">
                            Proveedor <span class="text-red-500">*</span>
                        </Label>
                        <select
                            id="create-proveedor"
                            v-model="createForm.id_proveedor"
                            @change="onProveedorChange(($event.target as HTMLSelectElement).value, createForm)"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                        >
                            <option value="">-- Seleccionar proveedor --</option>
                            <option v-for="prov in props.proveedores" :key="prov.id" :value="prov.id">{{ prov.nombre }}</option>
                        </select>
                        <InputError :message="createForm.errors.id_proveedor" />
                        <Input
                            v-model="createForm.proveedor"
                            placeholder="O escribe el nombre del proveedor"
                            class="h-8 text-xs mt-1"
                        />
                        <InputError :message="createForm.errors.proveedor" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="create-producto" class="text-xs font-medium">
                            Producto <span class="text-red-500">*</span>
                        </Label>
                        <select
                            id="create-producto"
                            v-model="createForm.id_producto"
                            @change="onProductoChange(($event.target as HTMLSelectElement).value, createForm)"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                        >
                            <option value="">-- Seleccionar producto --</option>
                            <option v-for="prod in props.productos" :key="prod.id" :value="prod.id">{{ prod.nombre }} ({{ prod.unidad_medida }})</option>
                        </select>
                        <InputError :message="createForm.errors.id_producto" />
                        <Input
                            v-model="createForm.producto"
                            placeholder="O escribe el nombre del producto"
                            class="h-8 text-xs mt-1"
                        />
                        <InputError :message="createForm.errors.producto" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="create-cantidad" class="text-xs font-medium">
                            Cantidad <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="create-cantidad"
                            type="number"
                            v-model="createForm.cantidad"
                            min="1"
                            placeholder="Ej: 10"
                            class="h-9"
                            required
                        />
                        <InputError :message="createForm.errors.cantidad" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="create-precio" class="text-xs font-medium">
                            Precio Unit. (HNL) <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="create-precio"
                            type="number"
                            step="0.01"
                            v-model="createForm.precio_unitario"
                            min="0"
                            placeholder="Ej: 150.00"
                            class="h-9"
                            required
                        />
                        <InputError :message="createForm.errors.precio_unitario" />
                    </div>
                </div>
                <div class="rounded-lg bg-yellow-500/10 p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-yellow-700 dark:text-yellow-400">Total Calculado:</span>
                        <span class="text-xl font-bold text-yellow-500">{{ formatCurrency(totalCalculado) }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="create-fecha" class="text-xs font-medium">
                            Fecha de Compra <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="create-fecha"
                            type="date"
                            v-model="createForm.fecha_compra"
                            class="h-9"
                            required
                        />
                        <InputError :message="createForm.errors.fecha_compra" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="create-estado" class="text-xs font-medium">
                            Estado <span class="text-red-500">*</span>
                        </Label>
                        <select
                            id="create-estado"
                            v-model="createForm.estado"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                            required
                        >
                            <option value="pendiente">⏳ Pendiente</option>
                            <option value="completado">✅ Completado</option>
                            <option value="cancelado">❌ Cancelado</option>
                        </select>
                        <InputError :message="createForm.errors.estado" />
                    </div>
                </div>
                <div class="grid gap-1.5">
                    <Label for="create-observaciones" class="text-xs font-medium">Observaciones</Label>
                    <textarea
                        id="create-observaciones"
                        v-model="createForm.observaciones"
                        rows="2"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="Observaciones adicionales..."
                    ></textarea>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end mt-2">
                    <Button 
                        type="button" 
                        variant="outline" 
                        class="h-10 rounded-lg border-2 border-red-500/50 text-red-600 hover:bg-red-500/10 sm:w-auto"
                        @click="isCreateModalOpen = false"
                    >
                        <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/>
                            <path d="m6 6 12 12"/>
                        </svg>
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
                        Guardar Compra
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Edit Modal -->
    <Dialog :open="isEditModalOpen" @update:open="isEditModalOpen = $event">
        <DialogContent class="sm:max-w-2xl">
            <div class="absolute inset-x-0 -top-px h-24 bg-gradient-to-r from-transparent via-red-500/20 to-transparent"></div>
            <DialogHeader>
                <div class="flex flex-col items-center gap-2 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 to-yellow-500 shadow-lg shadow-red-500/30">
                        <span class="text-2xl">✏️</span>
                    </div>
                    <DialogTitle class="text-xl">Editar Compra</DialogTitle>
                </div>
            </DialogHeader>
            <form @submit.prevent="submitEdit" class="grid gap-3 py-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="edit-proveedor" class="text-xs font-medium">
                            Proveedor <span class="text-red-500">*</span>
                        </Label>
                        <select
                            id="edit-proveedor"
                            v-model="editForm.id_proveedor"
                            @change="onProveedorChange(($event.target as HTMLSelectElement).value, editForm)"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                        >
                            <option value="">-- Seleccionar proveedor --</option>
                            <option v-for="prov in props.proveedores" :key="prov.id" :value="prov.id">{{ prov.nombre }}</option>
                        </select>
                        <InputError :message="editForm.errors.id_proveedor" />
                        <Input
                            v-model="editForm.proveedor"
                            placeholder="O escribe el nombre del proveedor"
                            class="h-8 text-xs mt-1"
                        />
                        <InputError :message="editForm.errors.proveedor" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="edit-producto" class="text-xs font-medium">
                            Producto <span class="text-red-500">*</span>
                        </Label>
                        <select
                            id="edit-producto"
                            v-model="editForm.id_producto"
                            @change="onProductoChange(($event.target as HTMLSelectElement).value, editForm)"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                        >
                            <option value="">-- Seleccionar producto --</option>
                            <option v-for="prod in props.productos" :key="prod.id" :value="prod.id">{{ prod.nombre }} ({{ prod.unidad_medida }})</option>
                        </select>
                        <InputError :message="editForm.errors.id_producto" />
                        <Input
                            v-model="editForm.producto"
                            placeholder="O escribe el nombre del producto"
                            class="h-8 text-xs mt-1"
                        />
                        <InputError :message="editForm.errors.producto" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="edit-cantidad" class="text-xs font-medium">
                            Cantidad <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="edit-cantidad"
                            type="number"
                            v-model="editForm.cantidad"
                            min="1"
                            class="h-9"
                            required
                        />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="edit-precio" class="text-xs font-medium">
                            Precio Unit. (HNL) <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="edit-precio"
                            type="number"
                            step="0.01"
                            v-model="editForm.precio_unitario"
                            min="0"
                            class="h-9"
                            required
                        />
                    </div>
                </div>
                <div class="rounded-lg bg-yellow-500/10 p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-yellow-700 dark:text-yellow-400">Total Calculado:</span>
                        <span class="text-xl font-bold text-yellow-500">{{ formatCurrency(editTotalCalculado) }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label for="edit-fecha" class="text-xs font-medium">
                            Fecha <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="edit-fecha"
                            type="date"
                            v-model="editForm.fecha_compra"
                            class="h-9"
                            required
                        />
                    </div>
                    <div class="grid gap-1.5">
                        <Label for="edit-estado" class="text-xs font-medium">
                            Estado <span class="text-red-500">*</span>
                        </Label>
                        <select
                            id="edit-estado"
                            v-model="editForm.estado"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm"
                            required
                        >
                            <option value="pendiente">⏳ Pendiente</option>
                            <option value="completado">✅ Completado</option>
                            <option value="cancelado">❌ Cancelado</option>
                        </select>
                    </div>
                </div>
                <div class="grid gap-1.5">
                    <Label for="edit-observaciones" class="text-xs font-medium">Observaciones</Label>
                    <textarea
                        id="edit-observaciones"
                        v-model="editForm.observaciones"
                        rows="2"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    ></textarea>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end mt-2">
                    <Button 
                        type="button" 
                        variant="outline" 
                        class="h-10 rounded-lg border-2 border-red-500/50 text-red-600 hover:bg-red-500/10 sm:w-auto"
                        @click="isEditModalOpen = false"
                    >
                        <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/>
                            <path d="m6 6 12 12"/>
                        </svg>
                        Cancelar
                    </Button>
                    <Button 
                        type="submit" 
                        :disabled="editForm.processing || isSubmitting" 
                        class="h-10 rounded-lg bg-gradient-to-r from-red-600 to-yellow-500 hover:from-red-700 hover:to-yellow-600 shadow-lg shadow-yellow-500/30 sm:w-auto"
                    >
                        <svg v-if="editForm.processing" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                        Guardar Cambios
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <!-- View Modal -->
    <Dialog :open="isViewModalOpen" @update:open="isViewModalOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="flex flex-col items-center gap-2 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 to-yellow-500 shadow-lg shadow-red-500/30">
                        <span class="text-2xl">📋</span>
                    </div>
                    <DialogTitle class="text-xl">Detalles de la Compra</DialogTitle>
                </div>
            </DialogHeader>
            <div v-if="selectedCompra" class="space-y-3 py-3">
                <div class="rounded-xl bg-gradient-to-br from-red-600/10 to-yellow-500/10 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-red-600 to-yellow-500 text-lg font-bold text-white">
                            {{ (selectedCompra.proveedor_rel?.nombre || selectedCompra.proveedor).charAt(0) }}
                        </div>
                        <div>
                            <p class="font-bold">{{ selectedCompra.proveedor_rel?.nombre || selectedCompra.proveedor }}</p>
                            <p class="text-sm text-muted-foreground">{{ selectedCompra.producto_rel?.nombre || selectedCompra.producto }}</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-muted/50 p-3">
                        <p class="text-xs text-muted-foreground">Cantidad</p>
                        <p class="text-lg font-bold">{{ selectedCompra.cantidad }}</p>
                    </div>
                    <div class="rounded-xl bg-muted/50 p-3">
                        <p class="text-xs text-muted-foreground">Precio Unit.</p>
                        <p class="text-lg font-bold">{{ formatCurrency(selectedCompra.precio_unitario) }}</p>
                    </div>
                </div>
                <div class="rounded-xl bg-gradient-to-r from-yellow-500/20 to-red-500/20 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">Total</span>
                        <span class="text-2xl font-bold text-yellow-500">{{ formatCurrency(selectedCompra.total) }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-muted/50 p-3">
                        <p class="text-xs text-muted-foreground">Fecha</p>
                        <p class="text-sm font-medium">{{ formatDate(selectedCompra.fecha_compra) }}</p>
                    </div>
                    <div class="rounded-xl bg-muted/50 p-3">
                        <p class="text-xs text-muted-foreground">Estado</p>
                        <span 
                            :class="[
                                'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold',
                                estadoColors[selectedCompra.estado].bg,
                                estadoColors[selectedCompra.estado].text
                            ]"
                        >
                            {{ selectedCompra.estado.charAt(0).toUpperCase() + selectedCompra.estado.slice(1) }}
                        </span>
                    </div>
                </div>
                <div v-if="selectedCompra.observaciones" class="rounded-xl bg-muted/50 p-3">
                    <p class="text-xs text-muted-foreground">Observaciones</p>
                    <p class="text-sm font-medium">{{ selectedCompra.observaciones }}</p>
                </div>
                <div class="rounded-xl bg-muted/30 p-2">
                    <p class="text-xs text-muted-foreground">ID de registro</p>
                    <p class="font-mono text-sm">#{{ String(selectedCompra.id).padStart(6, '0') }}</p>
                </div>
            </div>
            <DialogFooter class="gap-2 mt-3">
                <DialogClose as-child>
                    <Button type="button" class="h-10 rounded-lg">Cerrar</Button>
                </DialogClose>
            </DialogFooter>
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
                    <DialogTitle class="text-xl">Eliminar Compra</DialogTitle>
                </div>
            </DialogHeader>
            <div class="py-3">
                <div class="mb-3 overflow-hidden rounded-xl border border-red-500/20 bg-gradient-to-br from-red-600/5 to-yellow-500/5 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-red-600 to-yellow-500 text-lg font-bold text-white">
                            {{ (selectedCompra?.proveedor_rel?.nombre || selectedCompra?.proveedor || '').charAt(0) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold">{{ selectedCompra?.proveedor_rel?.nombre || selectedCompra?.proveedor }}</p>
                            <p class="text-sm text-muted-foreground">{{ selectedCompra?.producto_rel?.nombre || selectedCompra?.producto }}</p>
                            <p class="text-sm font-bold text-yellow-500">{{ formatCurrency(selectedCompra?.total || 0) }}</p>
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
                            ¿Estás seguro de que deseas eliminar esta compra? Esta acción eliminará permanentemente el registro.
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
                    </svg>
                    Eliminar Compra
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
