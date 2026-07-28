<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { ref, watch } from 'vue';
import { Shield, Trash2 } from 'lucide-vue-next';
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
import roles from '@/routes/roles';

interface Role {
    id: number;
    nombre: string;
    descripcion: string | null;
    permisos: Record<string, string> | null;
    usuarios_count: number;
}

interface Props {
    roles: Role[];
    modulos: string[];
    niveles: string[];
}

const props = defineProps<Props>();

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedRole = ref<Role | null>(null);

const permisosIniciales = () => {
    const p: Record<string, string> = {};
    props.modulos.forEach((m) => { p[m] = 'ninguno'; });
    return p;
};

const createForm = useForm({
    nombre: '',
    descripcion: '',
    permisos: permisosIniciales(),
});

const editForm = useForm({
    nombre: '',
    descripcion: '',
    permisos: permisosIniciales(),
});

const deleteForm = useForm({});

const nivelLabels: Record<string, string> = {
    ninguno: 'Sin acceso',
    ver: 'Lectura',
    gestionar: 'Lectura + Agregar + Eliminar',
};

const nivelColors: Record<string, string> = {
    ninguno: 'border-gray-500/30 text-gray-400',
    ver: 'border-blue-500/30 text-blue-400 bg-blue-500/10',
    gestionar: 'border-emerald-500/30 text-emerald-400 bg-emerald-500/10',
};

const moduloLabels: Record<string, string> = {
    clientes: 'Clientes',
    compras: 'Compras',
    usuarios: 'Usuarios',
    roles: 'Roles',
};

watch(() => (usePage().props as any).flash, (flash) => {
    if (flash?.success) toast.success(flash.success);
    if (flash?.error) toast.error(flash.error);
}, { immediate: true });

function openCreateModal() {
    createForm.reset();
    createForm.clearErrors();
    createForm.permisos = permisosIniciales();
    isCreateModalOpen.value = true;
}

function openEditModal(role: Role) {
    selectedRole.value = role;
    editForm.nombre = role.nombre;
    editForm.descripcion = role.descripcion ?? '';
    editForm.permisos = { ...permisosIniciales(), ...(role.permisos ?? {}) };
    editForm.clearErrors();
    isEditModalOpen.value = true;
}

function openDeleteModal(role: Role) {
    selectedRole.value = role;
    isDeleteModalOpen.value = true;
}

function submitCreate() {
    createForm.post(roles.store.url(), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
            toast.success('Rol creado exitosamente');
        },
    });
}

function submitEdit() {
    if (!selectedRole.value) return;
    editForm.put(roles.update.url(selectedRole.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            selectedRole.value = null;
            editForm.reset();
            toast.success('Rol actualizado exitosamente');
        },
    });
}

function submitDelete() {
    if (!selectedRole.value) return;
    deleteForm.delete(roles.destroy.url(selectedRole.value.id), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedRole.value = null;
            toast.success('Rol eliminado exitosamente');
        },
    });
}

const roleBadge = (nombre: string) => {
    const colors: Record<string, string> = {
        admin: 'bg-red-500/10 text-red-400 border-red-500/20',
        cajera: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        operador: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    };
    return colors[nombre.toLowerCase()] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/20';
};

function permisosResumen(role: Role): string {
    if (!role.permisos) return '—';
    const activos = Object.entries(role.permisos)
        .filter(([, v]) => v !== 'ninguno')
        .map(([k]) => moduloLabels[k] ?? k);
    return activos.length > 0 ? activos.join(', ') : '—';
}
</script>

<template>
    <Head title="Gestión de Roles" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-hidden p-4">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-red-950 to-slate-900 p-6 shadow-xl">
            <div class="absolute inset-0 opacity-5">
                <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid-roles" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5" />
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid-roles)" />
                </svg>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-red-600 to-amber-500 shadow-xl shadow-red-500/30">
                        <Shield class="h-7 w-7 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Gestión de Roles</h1>
                        <p class="text-sm text-red-200/70">Administra los roles y permisos del sistema</p>
                    </div>
                </div>
                <Button
                    @click="openCreateModal"
                    class="bg-gradient-to-r from-red-600 to-amber-500 shadow-lg shadow-red-500/30 transition-all hover:scale-105 hover:from-red-700 hover:to-amber-600"
                >
                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="5" y2="19" />
                        <line x1="5" x2="19" y1="12" y2="12" />
                    </svg>
                    Nuevo Rol
                </Button>
            </div>
        </div>

        <!-- Table -->
        <div class="flex-1 overflow-hidden rounded-xl border border-border/50 bg-card shadow-sm">
            <div class="h-full overflow-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 border-b border-border/50 bg-muted/80 backdrop-blur-sm">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">Rol</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">Descripción</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">Permisos</th>
                            <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-muted-foreground">Usuarios</th>
                            <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-muted-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/30">
                        <tr v-for="role in props.roles" :key="role.id" class="transition-all hover:bg-muted/30">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-600 to-amber-500 text-sm font-bold text-white shadow-md">
                                        {{ role.nombre.charAt(0).toUpperCase() }}
                                    </div>
                                    <span :class="roleBadge(role.nombre)" class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium">
                                        {{ role.nombre }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-muted-foreground">{{ role.descripcion ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-muted-foreground max-w-[200px] truncate" :title="permisosResumen(role)">
                                <div class="flex flex-wrap gap-1">
                                    <template v-for="(nivel, mod) in (role.permisos ?? {})" :key="mod">
                                        <span
                                            v-if="nivel !== 'ninguno'"
                                            :class="nivelColors[nivel]"
                                            class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs font-medium"
                                        >
                                            {{ moduloLabels[mod] ?? mod }}: {{ nivelLabels[nivel] }}
                                        </span>
                                    </template>
                                    <span v-if="!role.permisos || Object.values(role.permisos).every(v => v === 'ninguno')" class="text-muted-foreground">—</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    {{ role.usuarios_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(role)" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border/50 bg-background transition-all hover:border-amber-500/50 hover:bg-amber-500/10" title="Editar">
                                        <svg class="h-4 w-4 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                            <path d="m15 5 4 4" />
                                        </svg>
                                    </button>
                                    <button @click="openDeleteModal(role)" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border/50 bg-background transition-all hover:border-red-500/50 hover:bg-red-500/10" title="Eliminar">
                                        <svg class="h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.roles.length === 0">
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-muted">
                                        <Shield class="h-10 w-10 text-muted-foreground" />
                                    </div>
                                    <p class="text-lg font-bold text-foreground">No hay roles registrados</p>
                                    <p class="text-sm text-muted-foreground">Crea el primer rol para comenzar</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-border/50 bg-card p-4">
            <p class="text-sm text-muted-foreground">{{ props.roles.length }} rol(es) registrados</p>
        </div>
    </div>

    <!-- Create Modal -->
    <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="text-center text-2xl">Nuevo Rol</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitCreate" class="grid gap-4 py-4">
                <div>
                    <Label for="create-nombre">Nombre <span class="text-red-500">*</span></Label>
                    <Input id="create-nombre" v-model="createForm.nombre" placeholder="ej: vendedor" class="mt-1" required />
                    <InputError :message="createForm.errors.nombre" />
                </div>
                <div>
                    <Label for="create-descripcion">Descripción</Label>
                    <textarea
                        id="create-descripcion"
                        v-model="createForm.descripcion"
                        placeholder="Descripción del rol (opcional)"
                        class="mt-1 flex min-h-[80px] w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    />
                    <InputError :message="createForm.errors.descripcion" />
                </div>

                <!-- Permisos -->
                <div>
                    <Label class="text-base font-semibold">Permisos del Rol</Label>
                    <p class="mb-3 text-xs text-muted-foreground">Define qué módulos puede ver y gestionar este rol.</p>
                    <div class="grid gap-2">
                        <div
                            v-for="modulo in props.modulos"
                            :key="modulo"
                            class="flex items-center justify-between rounded-lg border border-border/50 bg-muted/30 p-3"
                        >
                            <div class="flex items-center gap-2">
                                <Shield class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm font-medium">{{ moduloLabels[modulo] ?? modulo }}</span>
                            </div>
                            <div class="flex gap-1">
                                <button
                                    v-for="nivel in props.niveles"
                                    :key="`create-${modulo}-${nivel}`"
                                    type="button"
                                    @click="createForm.permisos[modulo] = nivel"
                                    :class="createForm.permisos[modulo] === nivel ? nivelColors[nivel] : 'border-border/50 text-muted-foreground hover:border-border'"
                                    class="inline-flex items-center gap-1 rounded-md border px-2.5 py-1 text-xs font-medium transition-all"
                                >
                                    {{ nivelLabels[nivel] }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <InputError :message="createForm.errors['permisos.clientes']" />
                    <InputError :message="createForm.errors['permisos.compras']" />
                    <InputError :message="createForm.errors['permisos.usuarios']" />
                    <InputError :message="createForm.errors['permisos.roles']" />
                </div>

                <DialogFooter class="mt-4 gap-2">
                    <DialogClose as-child><Button type="button" variant="outline">Cancelar</Button></DialogClose>
                    <Button type="submit" :disabled="createForm.processing" class="bg-gradient-to-r from-red-600 to-amber-500 hover:from-red-700 hover:to-amber-600">Crear Rol</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Edit Modal -->
    <Dialog :open="isEditModalOpen" @update:open="isEditModalOpen = $event">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="text-center text-2xl">Editar Rol</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitEdit" class="grid gap-4 py-4">
                <div>
                    <Label for="edit-nombre">Nombre <span class="text-red-500">*</span></Label>
                    <Input id="edit-nombre" v-model="editForm.nombre" class="mt-1" required />
                    <InputError :message="editForm.errors.nombre" />
                </div>
                <div>
                    <Label for="edit-descripcion">Descripción</Label>
                    <textarea
                        id="edit-descripcion"
                        v-model="editForm.descripcion"
                        placeholder="Descripción del rol"
                        class="mt-1 flex min-h-[80px] w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    />
                    <InputError :message="editForm.errors.descripcion" />
                </div>

                <!-- Permisos -->
                <div>
                    <Label class="text-base font-semibold">Permisos del Rol</Label>
                    <p class="mb-3 text-xs text-muted-foreground">Define qué módulos puede ver y gestionar este rol.</p>
                    <div class="grid gap-2">
                        <div
                            v-for="modulo in props.modulos"
                            :key="modulo"
                            class="flex items-center justify-between rounded-lg border border-border/50 bg-muted/30 p-3"
                        >
                            <div class="flex items-center gap-2">
                                <Shield class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm font-medium">{{ moduloLabels[modulo] ?? modulo }}</span>
                            </div>
                            <div class="flex gap-1">
                                <button
                                    v-for="nivel in props.niveles"
                                    :key="`edit-${modulo}-${nivel}`"
                                    type="button"
                                    @click="editForm.permisos[modulo] = nivel"
                                    :class="editForm.permisos[modulo] === nivel ? nivelColors[nivel] : 'border-border/50 text-muted-foreground hover:border-border'"
                                    class="inline-flex items-center gap-1 rounded-md border px-2.5 py-1 text-xs font-medium transition-all"
                                >
                                    {{ nivelLabels[nivel] }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <InputError :message="editForm.errors['permisos.clientes']" />
                    <InputError :message="editForm.errors['permisos.compras']" />
                    <InputError :message="editForm.errors['permisos.usuarios']" />
                    <InputError :message="editForm.errors['permisos.roles']" />
                </div>

                <DialogFooter class="mt-4 gap-2">
                    <DialogClose as-child><Button type="button" variant="outline">Cancelar</Button></DialogClose>
                    <Button type="submit" :disabled="editForm.processing" class="bg-gradient-to-r from-amber-500 to-red-600 hover:from-amber-600 hover:to-red-700">Actualizar Rol</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Modal -->
    <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="flex flex-col items-center gap-3 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-rose-500 shadow-lg shadow-red-500/30">
                        <Trash2 class="h-7 w-7 text-white" />
                    </div>
                    <DialogTitle class="text-2xl">Eliminar Rol</DialogTitle>
                    <p class="text-sm text-muted-foreground">Esta acción no se puede deshacer</p>
                </div>
            </DialogHeader>
            <div class="py-4 text-center">
                <p class="mb-2 text-lg font-semibold">{{ selectedRole?.nombre }}</p>
                <p v-if="selectedRole?.usuarios_count && selectedRole.usuarios_count > 0" class="text-sm text-red-500">
                    Este rol tiene {{ selectedRole.usuarios_count }} usuario(s) asignados. No se puede eliminar.
                </p>
            </div>
            <DialogFooter class="gap-2">
                <DialogClose as-child><Button type="button" variant="outline">Cancelar</Button></DialogClose>
                <Button
                    @click="submitDelete"
                    :disabled="deleteForm.processing || (selectedRole?.usuarios_count ?? 0) > 0"
                    class="bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700"
                >
                    {{ deleteForm.processing ? 'Eliminando...' : 'Eliminar Rol' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
