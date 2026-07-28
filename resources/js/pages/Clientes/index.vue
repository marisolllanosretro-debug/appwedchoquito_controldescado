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
const clientesUrl = '/clientes';

const puedeGestionar = can('gestionar_clientes');

interface Cliente {
    id: number;
    nombre: string;
    email: string;
    telefono: string | null;
    direccion: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    clientes: {
        data: Cliente[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

const props = defineProps<Props>();

const searchQuery = ref('');
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedCliente = ref<Cliente | null>(null);
const isSubmitting = ref(false);
const activeTab = ref('all');

const createForm = useForm({
    nombre: '',
    email: '',
    telefono: '',
    direccion: '',
});

const editForm = useForm({
    nombre: '',
    email: '',
    telefono: '',
    direccion: '',
});

const deleteForm = useForm({});

const filteredClientes = computed(() => {
    let result = props.clientes.data;
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(cliente =>
            cliente.nombre.toLowerCase().includes(query) ||
            cliente.email.toLowerCase().includes(query) ||
            (cliente.telefono && cliente.telefono.includes(query))
        );
    }
    
    return result;
});

const clientesConEmail = computed(() => props.clientes.data.filter(c => c.email));
const clientesSinEmail = computed(() => props.clientes.data.filter(c => !c.email));

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast.success(flash.success);
    }
    if (flash?.error) {
        toast.error(flash.error);
    }
}, { immediate: true });

function openCreateModal() {
    createForm.reset();
    createForm.clearErrors();
    isCreateModalOpen.value = true;
}

function openEditModal(cliente: Cliente) {
    selectedCliente.value = cliente;
    editForm.nombre = cliente.nombre;
    editForm.email = cliente.email;
    editForm.telefono = cliente.telefono || '';
    editForm.direccion = cliente.direccion || '';
    editForm.clearErrors();
    isEditModalOpen.value = true;
}

function openDeleteModal(cliente: Cliente) {
    selectedCliente.value = cliente;
    isDeleteModalOpen.value = true;
}

function submitCreate() {
    isSubmitting.value = true;
    createForm.post(clientesUrl, {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
            toast.success('Cliente creado exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function submitEdit() {
    if (!selectedCliente.value) return;
    isSubmitting.value = true;
    editForm.put(`${clientesUrl}/${selectedCliente.value.id}`, {
        onSuccess: () => {
            isEditModalOpen.value = false;
            selectedCliente.value = null;
            editForm.reset();
            toast.success('Cliente actualizado exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function submitDelete() {
    if (!selectedCliente.value) return;
    isSubmitting.value = true;
    deleteForm.delete(`${clientesUrl}/${selectedCliente.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedCliente.value = null;
            toast.success('Cliente eliminado exitosamente');
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
</script>

<template>
    <Head title="Clientes - Panadería Delicias" />

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
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div class="absolute -bottom-1.5 -right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 shadow-lg">
                            <svg class="h-3 w-3 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Gestión de Clientes</h1>
                        <p class="text-yellow-200 text-sm">Administra tus clientes</p>
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
                        Nuevo Cliente
                    </span>
                </Button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-red-500/10">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-red-500/20 to-yellow-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-600 to-yellow-500 shadow-md shadow-red-500/30">
                        <span class="text-base">👥</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Total Clientes</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ props.clientes.total }}</span>
                        <span class="text-xs font-medium text-yellow-500">100%</span>
                    </div>
                    <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-muted">
                        <div class="h-full w-full rounded-full bg-gradient-to-r from-red-600 to-yellow-500"></div>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-blue-500/10">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-blue-400/20 to-indigo-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-500 shadow-md shadow-blue-500/30">
                        <span class="text-base">📧</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Con Email</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ clientesConEmail.length }}</span>
                        <span class="text-xs font-medium text-blue-500">{{ Math.round((clientesConEmail.length / Math.max(props.clientes.total, 1)) * 100) }}%</span>
                    </div>
                    <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-muted">
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all" :style="{ width: Math.round((clientesConEmail.length / Math.max(props.clientes.total, 1)) * 100) + '%' }"></div>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-red-500/10">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-red-400/20 to-rose-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-rose-500 shadow-md shadow-red-500/30">
                        <span class="text-base">📱</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Con Teléfono</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ props.clientes.data.filter(c => c.telefono).length }}</span>
                        <span class="text-xs font-medium text-red-500">{{ Math.round((props.clientes.data.filter(c => c.telefono).length / Math.max(props.clientes.total, 1)) * 100) }}%</span>
                    </div>
                    <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-muted">
                        <div class="h-full rounded-full bg-gradient-to-r from-red-500 to-rose-500 transition-all" :style="{ width: Math.round((props.clientes.data.filter(c => c.telefono).length / Math.max(props.clientes.total, 1)) * 100) + '%' }"></div>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm transition-all hover:shadow-lg hover:shadow-yellow-500/10">
                <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-yellow-400/20 to-orange-500/20 blur-xl transition-all group-hover:scale-125"></div>
                <div class="relative">
                    <div class="mb-1.5 flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-yellow-500 to-orange-500 shadow-md shadow-yellow-500/30">
                        <span class="text-base">📍</span>
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">Con Dirección</p>
                    <div class="mt-0.5 flex items-baseline gap-1.5">
                        <span class="text-xl font-bold">{{ props.clientes.data.filter(c => c.direccion).length }}</span>
                        <span class="text-xs font-medium text-yellow-500">{{ Math.round((props.clientes.data.filter(c => c.direccion).length / Math.max(props.clientes.total, 1)) * 100) }}%</span>
                    </div>
                    <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-muted">
                        <div class="h-full rounded-full bg-gradient-to-r from-yellow-500 to-orange-500 transition-all" :style="{ width: Math.round((props.clientes.data.filter(c => c.direccion).length / Math.max(props.clientes.total, 1)) * 100) + '%' }"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs and Search -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-1 rounded-lg border border-border/50 bg-card p-1">
                <button 
                    @click="activeTab = 'all'"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-xs font-medium transition-all',
                        activeTab === 'all' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-md' : 'text-muted-foreground hover:bg-muted'
                    ]"
                >
                    Todos ({{ props.clientes.total }})
                </button>
                <button 
                    @click="activeTab = 'recent'"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-xs font-medium transition-all',
                        activeTab === 'recent' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white shadow-md' : 'text-muted-foreground hover:bg-muted'
                    ]"
                >
                    Recientes
                </button>
            </div>
            
            <div class="relative w-full sm:max-w-xs">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <Input
                    v-model="searchQuery"
                    placeholder="Buscar clientes..."
                    class="pl-10 h-9 rounded-lg border-border/50 bg-card shadow-sm text-sm"
                />
            </div>
        </div>

        <!-- Table -->
        <div class="flex-1 overflow-hidden rounded-xl border border-border/50 bg-card shadow-sm">
            <div class="h-full overflow-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 border-b border-border/50 bg-gradient-to-r from-muted/80 via-muted/60 to-muted/80 backdrop-blur-sm">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-lg bg-red-600/10 flex items-center justify-center">
                                        <span class="text-red-600">👤</span>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Cliente</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                                        <span class="text-yellow-500">📧</span>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Contacto</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-lg bg-red-500/10 flex items-center justify-center">
                                        <span class="text-red-500">📍</span>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Dirección</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="h-7 w-7 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                                        <span class="text-yellow-500">📅</span>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Fecha</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/30">
                        <tr
                            v-for="(cliente, index) in filteredClientes"
                            :key="cliente.id"
                            class="group transition-all hover:bg-gradient-to-r hover:from-red-600/5 hover:to-yellow-500/5"
                            :style="{ animationDelay: index * 50 + 'ms' }"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="group-avatar relative">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-red-600 via-red-700 to-yellow-500 text-base font-bold text-white shadow-md shadow-red-500/30 transition-transform group-hover:scale-110 group-hover:shadow-lg">
                                            {{ cliente.nombre.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full border-2 border-background bg-emerald-500">
                                            <svg class="h-2.5 w-2.5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 6L9 17l-5-5"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-foreground group-hover:text-amber-600 transition-colors">{{ cliente.nombre }}</p>
                                        <p class="text-xs text-muted-foreground">ID: #{{ String(cliente.id).padStart(4, '0') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 rounded-lg bg-blue-500/5 px-3 py-1.5 transition-colors group-hover:bg-blue-500/10">
                                        <svg class="h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="20" height="16" x="2" y="4" rx="2"/>
                                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                        </svg>
                                        <span class="text-sm">{{ cliente.email }}</span>
                                    </div>
                                    <div v-if="cliente.telefono" class="flex items-center gap-2 rounded-lg bg-emerald-500/5 px-3 py-1.5 transition-colors group-hover:bg-emerald-500/10">
                                        <svg class="h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                        </svg>
                                        <span class="text-sm">{{ cliente.telefono }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="cliente.direccion" class="flex items-center gap-2 max-w-[200px]">
                                    <svg class="h-4 w-4 flex-shrink-0 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span class="text-sm truncate">{{ cliente.direccion }}</span>
                                </div>
                                <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground">
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="m15 9-6 6"/>
                                        <path d="m9 9 6 6"/>
                                    </svg>
                                    Sin dirección
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="text-sm font-medium">{{ formatDate(cliente.created_at) }}</span>
                                    <span class="text-xs text-muted-foreground">Registro</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        v-if="puedeGestionar"
                                        @click="openEditModal(cliente)"
                                        class="group relative flex h-10 w-10 items-center justify-center rounded-xl border border-border/50 bg-background transition-all hover:border-blue-500/50 hover:bg-blue-500/10 hover:shadow-lg hover:shadow-blue-500/20"
                                        title="Editar"
                                    >
                                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-500/20 to-indigo-500/20 opacity-0 transition-opacity group-hover:opacity-100"></div>
                                        <svg class="relative h-5 w-5 text-blue-500 transition-transform group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>
                                    <button
                                        v-if="puedeGestionar"
                                        @click="openDeleteModal(cliente)"
                                        class="group relative flex h-10 w-10 items-center justify-center rounded-xl border border-border/50 bg-background transition-all hover:border-red-500/50 hover:bg-red-500/10 hover:shadow-lg hover:shadow-red-500/20"
                                        title="Eliminar"
                                    >
                                        <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-red-500/20 to-rose-500/20 opacity-0 transition-opacity group-hover:opacity-100"></div>
                                        <svg class="relative h-5 w-5 text-red-500 transition-transform group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/>
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            <line x1="10" x2="10" y1="11" y2="17"/>
                                            <line x1="14" x2="14" y1="11" y2="17"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredClientes.length === 0">
                            <td colspan="5" class="px-6 py-20">
                                <div class="flex flex-col items-center gap-5">
                                    <div class="relative">
                                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-amber-100 to-orange-100">
                                            <span class="text-5xl">👥</span>
                                        </div>
                                        <div class="absolute -bottom-2 -right-2 flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg">
                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="11" cy="11" r="8"/>
                                                <path d="m21 21-4.3-4.3"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xl font-bold text-foreground">No se encontraron clientes</p>
                                        <p class="mt-1 text-sm text-muted-foreground">Comienza agregando tu primer cliente a la base de datos</p>
                                    </div>
                                    <Button v-if="puedeGestionar" @click="openCreateModal" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600">
                                        <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                            <line x1="19" x2="19" y1="8" y2="14"/>
                                            <line x1="22" x2="16" y1="11" y2="11"/>
                                        </svg>
                                        Agregar Cliente
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex flex-col gap-4 rounded-2xl border border-border/50 bg-card p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-500">
                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium">Mostrando {{ filteredClientes.length }} de {{ props.clientes.total }} clientes</p>
                    <p class="text-xs text-muted-foreground">Última actualización: {{ new Date().toLocaleString('es-ES') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-amber-500/10 px-4 py-2 text-sm font-medium text-amber-600">
                    📄 Página {{ props.clientes.current_page }} de {{ props.clientes.last_page }}
                </span>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
        <DialogContent class="sm:max-w-md">
            <div class="absolute inset-x-0 -top-px h-24 bg-gradient-to-r from-transparent via-amber-500/20 to-transparent"></div>
            <DialogHeader>
                <div class="flex flex-col items-center gap-3 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/30">
                        <span class="text-3xl">➕</span>
                    </div>
                    <DialogTitle class="text-2xl">Nuevo Cliente</DialogTitle>
                    <p class="text-sm text-muted-foreground">Completa la información del nuevo cliente</p>
                </div>
            </DialogHeader>
            <form @submit.prevent="submitCreate" class="grid gap-5 py-4">
                <div class="group relative">
                    <Label for="create-nombre" class="mb-2 block text-sm font-medium">
                        Nombre completo <span class="text-red-500">*</span>
                    </Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-amber-500 transition-colors group-focus-within:text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <Input
                            id="create-nombre"
                            v-model="createForm.nombre"
                            placeholder="Ej: Juan Pérez García"
                            class="pl-12 h-12 rounded-xl border-amber-500/20 bg-amber-500/5 transition-all focus:border-amber-500 focus:bg-amber-500/10"
                            required
                        />
                    </div>
                    <InputError :message="createForm.errors.nombre" />
                </div>
                <div class="group relative">
                    <Label for="create-email" class="mb-2 block text-sm font-medium">
                        Correo electrónico <span class="text-red-500">*</span>
                    </Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-blue-500 transition-colors group-focus-within:text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                        </div>
                        <Input
                            id="create-email"
                            type="email"
                            v-model="createForm.email"
                            placeholder="tu@ejemplo.com"
                            class="pl-12 h-12 rounded-xl border-blue-500/20 bg-blue-500/5 transition-all focus:border-blue-500 focus:bg-blue-500/10"
                            required
                        />
                    </div>
                    <InputError :message="createForm.errors.email" />
                </div>
                <div class="group relative">
                    <Label for="create-telefono" class="mb-2 block text-sm font-medium">Teléfono</Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-emerald-500 transition-colors group-focus-within:text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <Input
                            id="create-telefono"
                            v-model="createForm.telefono"
                            placeholder="Ej: 7777-1234"
                            class="pl-12 h-12 rounded-xl border-emerald-500/20 bg-emerald-500/5 transition-all focus:border-emerald-500 focus:bg-emerald-500/10"
                        />
                    </div>
                    <InputError :message="createForm.errors.telefono" />
                </div>
                <div class="group relative">
                    <Label for="create-direccion" class="mb-2 block text-sm font-medium">Dirección</Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-violet-500 transition-colors group-focus-within:text-violet-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <Input
                            id="create-direccion"
                            v-model="createForm.direccion"
                            placeholder="Ej: Calle Principal #123"
                            class="pl-12 h-12 rounded-xl border-violet-500/20 bg-violet-500/5 transition-all focus:border-violet-500 focus:bg-violet-500/10"
                        />
                    </div>
                    <InputError :message="createForm.errors.direccion" />
                </div>
                <DialogFooter class="gap-3 mt-4">
                    <DialogClose as-child>
                        <Button type="button" variant="outline" class="h-12 rounded-xl">Cancelar</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="createForm.processing || isSubmitting" class="h-12 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-lg shadow-amber-500/30">
                        <svg v-if="createForm.processing" class="mr-2 h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <line x1="19" x2="19" y1="8" y2="14"/>
                            <line x1="22" x2="16" y1="11" y2="11"/>
                        </svg>
                        Guardar Cliente
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Edit Modal -->
    <Dialog :open="isEditModalOpen" @update:open="isEditModalOpen = $event">
        <DialogContent class="sm:max-w-md">
            <div class="absolute inset-x-0 -top-px h-24 bg-gradient-to-r from-transparent via-blue-500/20 to-transparent"></div>
            <DialogHeader>
                <div class="flex flex-col items-center gap-3 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-500 shadow-lg shadow-blue-500/30">
                        <span class="text-3xl">✏️</span>
                    </div>
                    <DialogTitle class="text-2xl">Editar Cliente</DialogTitle>
                    <p class="text-sm text-muted-foreground">Actualiza la información del cliente</p>
                </div>
            </DialogHeader>
            <form @submit.prevent="submitEdit" class="grid gap-5 py-4">
                <div class="group relative">
                    <Label for="edit-nombre" class="mb-2 block text-sm font-medium">
                        Nombre completo <span class="text-red-500">*</span>
                    </Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <Input
                            id="edit-nombre"
                            v-model="editForm.nombre"
                            placeholder="Ej: Juan Pérez García"
                            class="pl-12 h-12 rounded-xl"
                            required
                        />
                    </div>
                    <InputError :message="editForm.errors.nombre" />
                </div>
                <div class="group relative">
                    <Label for="edit-email" class="mb-2 block text-sm font-medium">
                        Correo electrónico <span class="text-red-500">*</span>
                    </Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                        </div>
                        <Input
                            id="edit-email"
                            type="email"
                            v-model="editForm.email"
                            placeholder="tu@ejemplo.com"
                            class="pl-12 h-12 rounded-xl"
                            required
                        />
                    </div>
                    <InputError :message="editForm.errors.email" />
                </div>
                <div class="group relative">
                    <Label for="edit-telefono" class="mb-2 block text-sm font-medium">Teléfono</Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <Input
                            id="edit-telefono"
                            v-model="editForm.telefono"
                            placeholder="Ej: 7777-1234"
                            class="pl-12 h-12 rounded-xl"
                        />
                    </div>
                    <InputError :message="editForm.errors.telefono" />
                </div>
                <div class="group relative">
                    <Label for="edit-direccion" class="mb-2 block text-sm font-medium">Dirección</Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-violet-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <Input
                            id="edit-direccion"
                            v-model="editForm.direccion"
                            placeholder="Ej: Calle Principal #123"
                            class="pl-12 h-12 rounded-xl"
                        />
                    </div>
                    <InputError :message="editForm.errors.direccion" />
                </div>
                <DialogFooter class="gap-3 mt-4">
                    <DialogClose as-child>
                        <Button type="button" variant="outline" class="h-12 rounded-xl">Cancelar</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="editForm.processing || isSubmitting" class="h-12 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 shadow-lg shadow-blue-500/30">
                        <svg v-if="editForm.processing" class="mr-2 h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                            <path d="m15 5 4 4"/>
                        </svg>
                        Actualizar Cliente
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Modal -->
    <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
        <DialogContent class="sm:max-w-md">
            <div class="absolute inset-x-0 -top-px h-24 bg-gradient-to-r from-transparent via-red-500/20 to-transparent"></div>
            <DialogHeader>
                <div class="flex flex-col items-center gap-3 text-center">
                    <div class="relative">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-rose-500 shadow-lg shadow-red-500/30">
                            <span class="text-3xl">⚠️</span>
                        </div>
                        <div class="absolute -inset-2 rounded-2xl bg-gradient-to-br from-red-500/20 to-rose-500/20 blur-xl"></div>
                    </div>
                    <DialogTitle class="text-2xl">Eliminar Cliente</DialogTitle>
                    <p class="text-sm text-muted-foreground">Esta acción no se puede deshacer</p>
                </div>
            </DialogHeader>
            <div class="py-4">
                <div class="mb-4 overflow-hidden rounded-2xl border border-red-500/20 bg-gradient-to-br from-red-500/5 to-rose-500/5 p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-xl font-bold text-white shadow-lg">
                            {{ selectedCliente?.nombre.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-lg">{{ selectedCliente?.nombre }}</p>
                            <p class="text-sm text-muted-foreground">{{ selectedCliente?.email }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl bg-amber-500/10 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                            <line x1="12" x2="12" y1="9" y2="13"/>
                            <line x1="12" x2="12.01" y1="17" y2="17"/>
                        </svg>
                        <p class="text-sm text-amber-700 dark:text-amber-400">
                            ¿Estás seguro de que deseas eliminar a este cliente? Se perderán todos los datos asociados y esta acción no se puede deshacer.
                        </p>
                    </div>
                </div>
            </div>
            <DialogFooter class="gap-3 mt-4">
                <DialogClose as-child>
                    <Button type="button" variant="outline" class="h-12 rounded-xl">Cancelar</Button>
                </DialogClose>
                <Button @click="submitDelete" :disabled="deleteForm.processing || isSubmitting" class="h-12 rounded-xl bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 shadow-lg shadow-red-500/30">
                    <svg v-if="deleteForm.processing" class="mr-2 h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg v-else class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"/>
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                    </svg>
                    Eliminar Cliente
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
