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
const proveedoresUrl = '/proveedores';

const puedeGestionar = can('gestionar_proveedores');

interface Proveedor {
    id: number;
    nombre: string;
    contacto: string | null;
    telefono: string | null;
    email: string | null;
    direccion: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    proveedores: {
        data: Proveedor[];
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
const selectedProveedor = ref<Proveedor | null>(null);
const isSubmitting = ref(false);

const createForm = useForm({
    nombre: '',
    contacto: '',
    telefono: '',
    email: '',
    direccion: '',
});

const editForm = useForm({
    nombre: '',
    contacto: '',
    telefono: '',
    email: '',
    direccion: '',
});

const deleteForm = useForm({});

const filteredProveedores = computed(() => {
    let result = props.proveedores.data;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(p =>
            p.nombre.toLowerCase().includes(query) ||
            (p.contacto && p.contacto.toLowerCase().includes(query)) ||
            (p.email && p.email.toLowerCase().includes(query)) ||
            (p.telefono && p.telefono.includes(query))
        );
    }

    return result;
});

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

function openEditModal(proveedor: Proveedor) {
    selectedProveedor.value = proveedor;
    editForm.nombre = proveedor.nombre;
    editForm.contacto = proveedor.contacto || '';
    editForm.telefono = proveedor.telefono || '';
    editForm.email = proveedor.email || '';
    editForm.direccion = proveedor.direccion || '';
    editForm.clearErrors();
    isEditModalOpen.value = true;
}

function openDeleteModal(proveedor: Proveedor) {
    selectedProveedor.value = proveedor;
    isDeleteModalOpen.value = true;
}

function submitCreate() {
    isSubmitting.value = true;
    createForm.post(proveedoresUrl, {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
            toast.success('Proveedor creado exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function submitEdit() {
    if (!selectedProveedor.value) return;
    isSubmitting.value = true;
    editForm.put(`${proveedoresUrl}/${selectedProveedor.value.id}`, {
        onSuccess: () => {
            isEditModalOpen.value = false;
            selectedProveedor.value = null;
            editForm.reset();
            toast.success('Proveedor actualizado exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function submitDelete() {
    if (!selectedProveedor.value) return;
    isSubmitting.value = true;
    deleteForm.delete(`${proveedoresUrl}/${selectedProveedor.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedProveedor.value = null;
            toast.success('Proveedor eliminado exitosamente');
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
    <Head title="Proveedores - Panadería Delicias" />

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
                                <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/>
                                <path d="M15 18H9"/>
                                <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/>
                                <circle cx="17" cy="18" r="2"/>
                                <circle cx="7" cy="18" r="2"/>
                            </svg>
                        </div>
                        <div class="absolute -bottom-1.5 -right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 shadow-lg">
                            <svg class="h-3 w-3 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Gestión de Proveedores</h1>
                        <p class="text-yellow-200 text-sm">Administra tus proveedores</p>
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
                        Nuevo Proveedor
                    </span>
                </Button>
            </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:max-w-xs">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <Input
                    v-model="searchQuery"
                    placeholder="Buscar proveedores..."
                    class="pl-10 h-9 rounded-lg border-border/50 bg-card shadow-sm text-sm"
                />
            </div>
        </div>

        <div class="flex-1 overflow-hidden rounded-xl border border-border/50 bg-card shadow-sm">
            <div class="h-full overflow-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 border-b border-border/50 bg-gradient-to-r from-muted/80 via-muted/60 to-muted/80 backdrop-blur-sm">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-lg bg-red-600/10 flex items-center justify-center">
                                        <span class="text-red-600">🚚</span>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Proveedor</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-lg bg-yellow-500/10 flex items-center justify-center">
                                        <span class="text-yellow-500">📞</span>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Contacto</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left">
                                <div class="flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-lg bg-blue-500/10 flex items-center justify-center">
                                        <span class="text-blue-500">📧</span>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Email</span>
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
                            v-for="(proveedor, index) in filteredProveedores"
                            :key="proveedor.id"
                            class="group transition-all hover:bg-gradient-to-r hover:from-red-600/5 hover:to-yellow-500/5"
                            :style="{ animationDelay: index * 50 + 'ms' }"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="group-avatar relative">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-red-600 via-red-700 to-yellow-500 text-base font-bold text-white shadow-md shadow-red-500/30 transition-transform group-hover:scale-110 group-hover:shadow-lg">
                                            {{ proveedor.nombre.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full border-2 border-background bg-emerald-500">
                                            <svg class="h-2.5 w-2.5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 6L9 17l-5-5"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-foreground group-hover:text-amber-600 transition-colors">{{ proveedor.nombre }}</p>
                                        <p v-if="proveedor.direccion" class="text-xs text-muted-foreground">{{ proveedor.direccion }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div v-if="proveedor.contacto" class="flex items-center gap-2 rounded-lg bg-emerald-500/5 px-3 py-1.5 transition-colors group-hover:bg-emerald-500/10">
                                        <svg class="h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        <span class="text-sm">{{ proveedor.contacto }}</span>
                                    </div>
                                    <div v-if="proveedor.telefono" class="flex items-center gap-2 rounded-lg bg-blue-500/5 px-3 py-1.5 transition-colors group-hover:bg-blue-500/10">
                                        <svg class="h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                        </svg>
                                        <span class="text-sm">{{ proveedor.telefono }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="proveedor.email" class="flex items-center gap-2">
                                    <svg class="h-4 w-4 flex-shrink-0 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="20" height="16" x="2" y="4" rx="2"/>
                                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                    </svg>
                                    <span class="text-sm">{{ proveedor.email }}</span>
                                </div>
                                <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-muted px-3 py-1 text-xs text-muted-foreground">
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="m15 9-6 6"/>
                                        <path d="m9 9 6 6"/>
                                    </svg>
                                    Sin email
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="text-sm font-medium">{{ formatDate(proveedor.created_at) }}</span>
                                    <span class="text-xs text-muted-foreground">Registro</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        v-if="puedeGestionar"
                                        @click="openEditModal(proveedor)"
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
                                        @click="openDeleteModal(proveedor)"
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
                        <tr v-if="filteredProveedores.length === 0">
                            <td colspan="5" class="px-6 py-20">
                                <div class="flex flex-col items-center gap-5">
                                    <div class="relative">
                                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-amber-100 to-orange-100">
                                            <span class="text-5xl">🚚</span>
                                        </div>
                                        <div class="absolute -bottom-2 -right-2 flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg">
                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="11" cy="11" r="8"/>
                                                <path d="m21 21-4.3-4.3"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xl font-bold text-foreground">No se encontraron proveedores</p>
                                        <p class="mt-1 text-sm text-muted-foreground">Comienza agregando tu primer proveedor</p>
                                    </div>
                                    <Button v-if="puedeGestionar" @click="openCreateModal" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600">
                                        <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" x2="12" y1="5" y2="19"/>
                                            <line x1="5" x2="19" y1="12" y2="12"/>
                                        </svg>
                                        Agregar Proveedor
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-border/50 bg-card p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-red-600 to-yellow-500">
                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/>
                        <path d="M15 18H9"/>
                        <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/>
                        <circle cx="17" cy="18" r="2"/>
                        <circle cx="7" cy="18" r="2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium">Mostrando {{ filteredProveedores.length }} de {{ props.proveedores.total }} proveedores</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-amber-500/10 px-4 py-2 text-sm font-medium text-amber-600">
                    📄 Página {{ props.proveedores.current_page }} de {{ props.proveedores.last_page }}
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
                    <DialogTitle class="text-2xl">Nuevo Proveedor</DialogTitle>
                    <p class="text-sm text-muted-foreground">Completa la información del proveedor</p>
                </div>
            </DialogHeader>
            <form @submit.prevent="submitCreate" class="grid gap-5 py-4">
                <div class="group relative">
                    <Label for="create-nombre" class="mb-2 block text-sm font-medium">
                        Nombre <span class="text-red-500">*</span>
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
                            placeholder="Ej: Molinos del Norte S.A."
                            class="pl-12 h-12 rounded-xl border-amber-500/20 bg-amber-500/5 transition-all focus:border-amber-500 focus:bg-amber-500/10"
                            required
                        />
                    </div>
                    <InputError :message="createForm.errors.nombre" />
                </div>
                <div class="group relative">
                    <Label for="create-contacto" class="mb-2 block text-sm font-medium">Persona de Contacto</Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-emerald-500 transition-colors group-focus-within:text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <Input
                            id="create-contacto"
                            v-model="createForm.contacto"
                            placeholder="Ej: Juan Pérez"
                            class="pl-12 h-12 rounded-xl border-emerald-500/20 bg-emerald-500/5 transition-all focus:border-emerald-500 focus:bg-emerald-500/10"
                        />
                    </div>
                    <InputError :message="createForm.errors.contacto" />
                </div>
                <div class="group relative">
                    <Label for="create-telefono" class="mb-2 block text-sm font-medium">Teléfono</Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-blue-500 transition-colors group-focus-within:text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <Input
                            id="create-telefono"
                            v-model="createForm.telefono"
                            placeholder="Ej: 7777-1234"
                            class="pl-12 h-12 rounded-xl border-blue-500/20 bg-blue-500/5 transition-all focus:border-blue-500 focus:bg-blue-500/10"
                        />
                    </div>
                    <InputError :message="createForm.errors.telefono" />
                </div>
                <div class="group relative">
                    <Label for="create-email" class="mb-2 block text-sm font-medium">Email</Label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-violet-500 transition-colors group-focus-within:text-violet-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                        </div>
                        <Input
                            id="create-email"
                            type="email"
                            v-model="createForm.email"
                            placeholder="contacto@ejemplo.com"
                            class="pl-12 h-12 rounded-xl border-violet-500/20 bg-violet-500/5 transition-all focus:border-violet-500 focus:bg-violet-500/10"
                        />
                    </div>
                    <InputError :message="createForm.errors.email" />
                </div>
                <div class="group relative">
                    <Label for="create-direccion" class="mb-2 block text-sm font-medium">Dirección</Label>
                    <div class="relative">
                        <Input
                            id="create-direccion"
                            v-model="createForm.direccion"
                            placeholder="Ej: Carretera Norte, km 5"
                            class="h-12 rounded-xl border-amber-500/20 bg-amber-500/5 transition-all focus:border-amber-500 focus:bg-amber-500/10"
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
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                        Guardar Proveedor
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
                    <DialogTitle class="text-2xl">Editar Proveedor</DialogTitle>
                    <p class="text-sm text-muted-foreground">Actualiza la información del proveedor</p>
                </div>
            </DialogHeader>
            <form @submit.prevent="submitEdit" class="grid gap-5 py-4">
                <div class="group relative">
                    <Label for="edit-nombre" class="mb-2 block text-sm font-medium">
                        Nombre <span class="text-red-500">*</span>
                    </Label>
                    <div class="relative">
                        <Input
                            id="edit-nombre"
                            v-model="editForm.nombre"
                            class="h-12 rounded-xl"
                            required
                        />
                    </div>
                    <InputError :message="editForm.errors.nombre" />
                </div>
                <div class="group relative">
                    <Label for="edit-contacto" class="mb-2 block text-sm font-medium">Persona de Contacto</Label>
                    <div class="relative">
                        <Input
                            id="edit-contacto"
                            v-model="editForm.contacto"
                            class="h-12 rounded-xl"
                        />
                    </div>
                    <InputError :message="editForm.errors.contacto" />
                </div>
                <div class="group relative">
                    <Label for="edit-telefono" class="mb-2 block text-sm font-medium">Teléfono</Label>
                    <div class="relative">
                        <Input
                            id="edit-telefono"
                            v-model="editForm.telefono"
                            class="h-12 rounded-xl"
                        />
                    </div>
                    <InputError :message="editForm.errors.telefono" />
                </div>
                <div class="group relative">
                    <Label for="edit-email" class="mb-2 block text-sm font-medium">Email</Label>
                    <div class="relative">
                        <Input
                            id="edit-email"
                            type="email"
                            v-model="editForm.email"
                            class="h-12 rounded-xl"
                        />
                    </div>
                    <InputError :message="editForm.errors.email" />
                </div>
                <div class="group relative">
                    <Label for="edit-direccion" class="mb-2 block text-sm font-medium">Dirección</Label>
                    <div class="relative">
                        <Input
                            id="edit-direccion"
                            v-model="editForm.direccion"
                            class="h-12 rounded-xl"
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
                        Actualizar Proveedor
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
                    <DialogTitle class="text-2xl">Eliminar Proveedor</DialogTitle>
                    <p class="text-sm text-muted-foreground">Esta acción no se puede deshacer</p>
                </div>
            </DialogHeader>
            <div class="py-4">
                <div class="mb-4 overflow-hidden rounded-2xl border border-red-500/20 bg-gradient-to-br from-red-500/5 to-rose-500/5 p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-xl font-bold text-white shadow-lg">
                            {{ selectedProveedor?.nombre.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-lg">{{ selectedProveedor?.nombre }}</p>
                            <p class="text-sm text-muted-foreground">{{ selectedProveedor?.contacto || 'Sin contacto' }}</p>
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
                        <p class="text-sm text-yellow-700 dark:text-yellow-400">
                            ¿Estás seguro de eliminar este proveedor? Las compras asociadas conservarán el nombre pero perderán la referencia.
                        </p>
                    </div>
                </div>
            </div>
            <DialogFooter class="gap-3 mt-2">
                <DialogClose as-child>
                    <Button type="button" variant="outline" class="h-12 rounded-xl">Cancelar</Button>
                </DialogClose>
                <Button @click="submitDelete" :disabled="deleteForm.processing || isSubmitting" class="h-12 rounded-xl bg-gradient-to-r from-red-600 to-red-900 hover:from-red-700 hover:to-red-950 shadow-lg">
                    <svg v-if="deleteForm.processing" class="mr-2 h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg v-else class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"/>
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                    </svg>
                    Eliminar Proveedor
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
