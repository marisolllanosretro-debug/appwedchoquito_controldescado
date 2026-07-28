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
const categoriasUrl = '/categorias';

const puedeGestionar = can('gestionar_categorias');

interface Categoria {
    id: number;
    nombre: string;
    descripcion: string | null;
    icono: string | null;
    activo: boolean;
    productos_count: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    categorias: {
        data: Categoria[];
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
const selectedCategoria = ref<Categoria | null>(null);
const expandedId = ref<number | null>(null);
const isSubmitting = ref(false);

const createForm = useForm({
    nombre: '',
    descripcion: '',
    icono: '',
    activo: true,
});

const editForm = useForm({
    nombre: '',
    descripcion: '',
    icono: '',
    activo: true,
});

const deleteForm = useForm({});

const filteredCategorias = computed(() => {
    let result = props.categorias.data;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(c =>
            c.nombre.toLowerCase().includes(query) ||
            (c.descripcion && c.descripcion.toLowerCase().includes(query))
        );
    }

    return result;
});

const iconosDisponibles = ['🥖', '🍕', '🧁', '🥤', '📦', '🍞', '🥐', '🍪', '🎂', '🍩', '🥟', '🥗', '🧀', '🥚', '🧈'];

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
    createForm.activo = true;
    createForm.clearErrors();
    isCreateModalOpen.value = true;
}

function openEditModal(categoria: Categoria) {
    expandedId.value = null;
    selectedCategoria.value = categoria;
    editForm.nombre = categoria.nombre;
    editForm.descripcion = categoria.descripcion || '';
    editForm.icono = categoria.icono || '';
    editForm.activo = categoria.activo;
    editForm.clearErrors();
    isEditModalOpen.value = true;
}

function openDeleteModal(categoria: Categoria) {
    expandedId.value = null;
    selectedCategoria.value = categoria;
    isDeleteModalOpen.value = true;
}

function submitCreate() {
    isSubmitting.value = true;
    createForm.post(categoriasUrl, {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
            toast.success('Categoría creada exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function submitEdit() {
    if (!selectedCategoria.value) return;
    isSubmitting.value = true;
    editForm.put(`${categoriasUrl}/${selectedCategoria.value.id}`, {
        onSuccess: () => {
            isEditModalOpen.value = false;
            selectedCategoria.value = null;
            editForm.reset();
            toast.success('Categoría actualizada exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

function submitDelete() {
    if (!selectedCategoria.value) return;
    isSubmitting.value = true;
    deleteForm.delete(`${categoriasUrl}/${selectedCategoria.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedCategoria.value = null;
            toast.success('Categoría eliminada exitosamente');
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}

const coloresPorIcono: Record<string, string> = {
    '🥖': 'from-amber-500 to-orange-500',
    '🍞': 'from-amber-500 to-yellow-500',
    '🥐': 'from-amber-500 to-orange-600',
    '🍕': 'from-red-500 to-rose-600',
    '🧁': 'from-pink-500 to-purple-500',
    '🎂': 'from-pink-500 to-rose-500',
    '🍪': 'from-amber-400 to-yellow-600',
    '🍩': 'from-orange-400 to-red-500',
    '🥤': 'from-blue-500 to-cyan-500',
    '🥟': 'from-yellow-400 to-amber-500',
    '🧀': 'from-yellow-500 to-amber-600',
    '🥚': 'from-stone-300 to-stone-400',
    '🧈': 'from-yellow-300 to-amber-400',
    '📦': 'from-gray-500 to-slate-500',
    '🥗': 'from-green-500 to-emerald-500',
};

function getGradiente(icono: string | null): string {
    return coloresPorIcono[icono || ''] || 'from-gray-500 to-slate-500';
}
</script>

<template>
    <Head title="Categorías - Catálogo" />

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
                            <span class="text-2xl">📂</span>
                        </div>
                        <div class="absolute -bottom-1.5 -right-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-yellow-400 shadow-lg">
                            <svg class="h-3 w-3 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Gestión del Catálogo</h1>
                        <p class="text-yellow-200 text-sm">Administra las categorías de productos</p>
                        <div class="mt-1 flex items-center gap-2">
                            <div class="flex items-center gap-1 rounded-full bg-white/10 px-2 py-0.5">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-yellow-400"></span>
                                <span class="text-xs font-medium text-white">Catálogo</span>
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
                        Nueva Categoría
                    </span>
                </Button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-red-600 to-yellow-500 shadow-md">
                        <span class="text-lg">📂</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Total Categorías</p>
                        <p class="text-xl font-bold">{{ props.categorias.total }}</p>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-green-500 shadow-md">
                        <span class="text-lg">✅</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Activas</p>
                        <p class="text-xl font-bold">{{ props.categorias.data.filter(c => c.activo).length }}</p>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 shadow-md">
                        <span class="text-lg">📦</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Total Productos</p>
                        <p class="text-xl font-bold">{{ props.categorias.data.reduce((s, c) => s + c.productos_count, 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden rounded-xl border border-border/50 bg-card p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-500 shadow-md">
                        <span class="text-lg">🏪</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">En Catálogo</p>
                        <p class="text-xl font-bold">{{ props.categorias.data.filter(c => c.activo && c.productos_count > 0).length }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="flex items-center justify-between">
            <div class="relative w-full sm:max-w-xs">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <Input
                    v-model="searchQuery"
                    placeholder="Buscar categorías..."
                    class="pl-10 h-9 rounded-lg border-border/50 bg-card shadow-sm text-sm"
                />
            </div>
        </div>

        <!-- Categories List -->
        <div class="flex-1 overflow-auto space-y-2">
            <div
                v-for="categoria in filteredCategorias"
                :key="categoria.id"
                class="relative overflow-hidden rounded-xl border transition-all duration-300"
                :class="[
                    expandedId === categoria.id ? 'border-yellow-500 shadow-lg shadow-yellow-500/20' : 'border-border/50 bg-card shadow-sm hover:shadow-md',
                    !categoria.activo ? 'opacity-60' : ''
                ]"
            >
                <div
                    class="relative cursor-pointer"
                    @click="toggleExpand(categoria.id)"
                >
                    <div class="absolute left-0 top-0 bottom-0 w-1"
                        :class="categoria.activo ? 'bg-emerald-500' : 'bg-gray-400'">
                    </div>

                    <div class="flex items-center gap-3 p-3 pl-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-lg shadow-md"
                            :class="'bg-gradient-to-br ' + getGradiente(categoria.icono)">
                            <span>{{ categoria.icono || '📂' }}</span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5">
                                <h3 class="font-bold text-sm truncate">{{ categoria.nombre }}</h3>
                                <span v-if="!categoria.activo"
                                    class="shrink-0 rounded-full bg-gray-400 px-2 py-0.5 text-xs font-bold text-white">
                                    Inactiva
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground truncate">{{ categoria.descripcion || 'Sin descripción' }}</p>
                        </div>

                        <div class="text-right shrink-0">
                            <div class="flex items-center gap-1 justify-end">
                                <span class="text-sm font-bold">{{ categoria.productos_count }}</span>
                                <span class="text-xs text-muted-foreground">productos</span>
                            </div>
                        </div>

                        <div class="shrink-0 transition-transform duration-300" :class="expandedId === categoria.id ? 'rotate-180' : ''">
                            <svg class="h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="overflow-hidden transition-all duration-300"
                    :class="expandedId === categoria.id ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'"
                >
                    <div class="border-t border-border/50 bg-muted/30 p-3">
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="rounded-lg bg-background/80 p-2 text-center">
                                <p class="text-xs text-muted-foreground">Productos</p>
                                <p class="text-base font-bold">{{ categoria.productos_count }}</p>
                            </div>
                            <div class="rounded-lg bg-background/80 p-2 text-center">
                                <p class="text-xs text-muted-foreground">Estado</p>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold mt-1"
                                    :class="categoria.activo ? 'bg-emerald-500 text-white' : 'bg-gray-400 text-white'">
                                    {{ categoria.activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5">
                            <Button
                                v-if="puedeGestionar"
                                @click.stop="openEditModal(categoria)"
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
                                @click.stop="openDeleteModal(categoria)"
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

            <div v-if="filteredCategorias.length === 0" class="flex flex-col items-center gap-3 rounded-xl border border-border/50 bg-card p-8">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-red-600 to-yellow-500">
                    <span class="text-3xl">📂</span>
                </div>
                <div class="text-center">
                    <p class="text-base font-bold text-foreground">No hay categorías</p>
                    <p class="mt-1 text-sm text-muted-foreground">Crea tu primera categoría para organizar el catálogo</p>
                </div>
                <Button v-if="puedeGestionar" @click="openCreateModal" class="bg-gradient-to-r from-red-600 to-yellow-500 hover:from-red-700 hover:to-yellow-600">
                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="5" y2="19"/>
                        <line x1="5" x2="19" y1="12" y2="12"/>
                    </svg>
                    Nueva Categoría
                </Button>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-border/50 bg-card p-3">
            <p class="text-sm text-muted-foreground">
                Mostrando {{ filteredCategorias.length }} de {{ props.categorias.total }} categorías
            </p>
            <span class="rounded-full bg-amber-500/10 px-3 py-1.5 text-xs font-medium text-amber-600">
                📄 Página {{ props.categorias.current_page }} de {{ props.categorias.last_page }}
            </span>
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
                    <DialogTitle class="text-2xl">Nueva Categoría</DialogTitle>
                    <p class="text-sm text-muted-foreground">Agrega una nueva categoría al catálogo</p>
                </div>
            </DialogHeader>
            <form @submit.prevent="submitCreate" class="grid gap-5 py-4">
                <div class="grid gap-1.5">
                    <Label for="create-nombre" class="text-sm font-medium">
                        Nombre <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="create-nombre"
                        v-model="createForm.nombre"
                        placeholder="Ej: Panificados"
                        class="h-12 rounded-xl"
                        required
                    />
                    <InputError :message="createForm.errors.nombre" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="create-descripcion" class="text-sm font-medium">Descripción</Label>
                    <Input
                        id="create-descripcion"
                        v-model="createForm.descripcion"
                        placeholder="Breve descripción de la categoría"
                        class="h-12 rounded-xl"
                    />
                    <InputError :message="createForm.errors.descripcion" />
                </div>
                <div class="grid gap-1.5">
                    <Label class="text-sm font-medium">Ícono</Label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="icono in iconosDisponibles"
                            :key="icono"
                            type="button"
                            @click="createForm.icono = icono"
                            class="flex h-10 w-10 items-center justify-center rounded-lg text-lg transition-all"
                            :class="createForm.icono === icono ? 'ring-2 ring-yellow-500 bg-yellow-500/10 scale-110' : 'bg-muted hover:bg-muted/80'"
                        >
                            {{ icono }}
                        </button>
                    </div>
                    <InputError :message="createForm.errors.icono" />
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
                        Guardar Categoría
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
                    <DialogTitle class="text-2xl">Editar Categoría</DialogTitle>
                    <p class="text-sm text-muted-foreground">Actualiza la información de la categoría</p>
                </div>
            </DialogHeader>
            <form @submit.prevent="submitEdit" class="grid gap-5 py-4">
                <div class="grid gap-1.5">
                    <Label for="edit-nombre" class="text-sm font-medium">
                        Nombre <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="edit-nombre"
                        v-model="editForm.nombre"
                        class="h-12 rounded-xl"
                        required
                    />
                    <InputError :message="editForm.errors.nombre" />
                </div>
                <div class="grid gap-1.5">
                    <Label for="edit-descripcion" class="text-sm font-medium">Descripción</Label>
                    <Input
                        id="edit-descripcion"
                        v-model="editForm.descripcion"
                        class="h-12 rounded-xl"
                    />
                    <InputError :message="editForm.errors.descripcion" />
                </div>
                <div class="grid gap-1.5">
                    <Label class="text-sm font-medium">Ícono</Label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="icono in iconosDisponibles"
                            :key="icono"
                            type="button"
                            @click="editForm.icono = icono"
                            class="flex h-10 w-10 items-center justify-center rounded-lg text-lg transition-all"
                            :class="editForm.icono === icono ? 'ring-2 ring-yellow-500 bg-yellow-500/10 scale-110' : 'bg-muted hover:bg-muted/80'"
                        >
                            {{ icono }}
                        </button>
                    </div>
                    <InputError :message="editForm.errors.icono" />
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
                        Actualizar Categoría
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
                    </div>
                    <DialogTitle class="text-2xl">Eliminar Categoría</DialogTitle>
                    <p class="text-sm text-muted-foreground">Esta acción no se puede deshacer</p>
                </div>
            </DialogHeader>
            <div class="py-4">
                <div class="mb-4 overflow-hidden rounded-2xl border border-red-500/20 bg-gradient-to-br from-red-500/5 to-rose-500/5 p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full text-xl shadow-lg"
                            :class="'bg-gradient-to-br ' + getGradiente(selectedCategoria?.icono || null)">
                            <span>{{ selectedCategoria?.icono || '📂' }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-lg">{{ selectedCategoria?.nombre }}</p>
                            <p class="text-sm text-muted-foreground">{{ selectedCategoria?.descripcion || 'Sin descripción' }}</p>
                            <p class="text-sm font-medium text-yellow-500">{{ selectedCategoria?.productos_count || 0 }} productos asociados</p>
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
                            Al eliminar esta categoría, los productos asociados conservarán el nombre de la categoría pero perderán la referencia.
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
                    Eliminar Categoría
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
