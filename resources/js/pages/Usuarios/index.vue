<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { ref, computed, watch } from 'vue';
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
import usuarios from '@/routes/usuarios';

interface Usuario {
    id: number;
    name: string;
    apellido_paterno: string | null;
    apellido_materno: string | null;
    email: string;
    telefono: string | null;
    fecha_ingreso: string | null;
    rol: { id: number; nombre: string } | null;
    id_rol: number | null;
    activo: boolean;
    created_at: string;
}

interface Rol {
    id: number;
    nombre: string;
}

interface Props {
    usuarios: {
        data: Usuario[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    roles: Rol[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedUser = ref<Usuario | null>(null);

const createForm = useForm({
    name: '',
    apellido_paterno: '',
    apellido_materno: '',
    email: '',
    telefono: '',
    fecha_ingreso: '',
    password: '',
    password_confirmation: '',
    id_rol: '',
});

const editForm = useForm({
    name: '',
    apellido_paterno: '',
    apellido_materno: '',
    email: '',
    telefono: '',
    fecha_ingreso: '',
    password: '',
    password_confirmation: '',
    id_rol: '',
    activo: true,
});

const estadoForm = useForm({});

const deleteForm = useForm({});

const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.usuarios.data;
    const q = searchQuery.value.toLowerCase();
    return props.usuarios.data.filter(
        (u) => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q),
    );
});

watch(() => (usePage().props as any).flash, (flash) => {
    if (flash?.success) toast.success(flash.success);
    if (flash?.error) toast.error(flash.error);
}, { immediate: true });

function openCreateModal() {
    createForm.reset();
    createForm.clearErrors();
    createForm.id_rol = String(props.roles[0]?.id ?? '');
    isCreateModalOpen.value = true;
}

function openEditModal(user: Usuario) {
    selectedUser.value = user;
    editForm.name = user.name;
    editForm.apellido_paterno = user.apellido_paterno ?? '';
    editForm.apellido_materno = user.apellido_materno ?? '';
    editForm.email = user.email;
    editForm.telefono = user.telefono ?? '';
    editForm.fecha_ingreso = user.fecha_ingreso ?? '';
    editForm.password = '';
    editForm.password_confirmation = '';
    editForm.id_rol = String(user.id_rol ?? '');
    editForm.activo = user.activo;
    editForm.clearErrors();
    isEditModalOpen.value = true;
}

function openDeleteModal(user: Usuario) {
    selectedUser.value = user;
    isDeleteModalOpen.value = true;
}

function submitCreate() {
    createForm.password_confirmation = createForm.password;
    createForm.post(usuarios.store.url(), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
            toast.success('Usuario creado exitosamente');
        },
    });
}

function submitEdit() {
    if (!selectedUser.value) return;
    editForm.password_confirmation = editForm.password;
    editForm.put(usuarios.update.url(selectedUser.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            selectedUser.value = null;
            editForm.reset();
            toast.success('Usuario actualizado exitosamente');
        },
    });
}

function toggleEstado(user: Usuario) {
    estadoForm.patch(usuarios.estado.url(user.id), {
        data: { activo: !user.activo },
        preserveScroll: true,
        onSuccess: () => {
            toast.success(user.activo ? 'Usuario desactivado' : 'Usuario reactivado');
        },
    });
}

function submitDelete() {
    if (!selectedUser.value) return;
    deleteForm.delete(usuarios.destroy.url(selectedUser.value.id), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedUser.value = null;
            toast.success('Usuario eliminado exitosamente');
        },
    });
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

const roleBadge = (role: string) => {
    const colors: Record<string, string> = {
        admin: 'bg-red-500/10 text-red-400 border-red-500/20',
        cajera: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        operador: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    };
    return colors[role] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/20';
};

const roleNombre = (id: number): string => {
    return props.roles.find((r) => r.id === id)?.nombre ?? 'Desconocido';
};
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-hidden p-4">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-purple-950 to-slate-900 p-6 shadow-xl">
            <div class="absolute inset-0 opacity-5">
                <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5" />
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid)" />
                </svg>
            </div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-purple-600 to-indigo-500 shadow-xl shadow-purple-500/30">
                        <svg class="h-7 w-7 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Gestión de Usuarios</h1>
                        <p class="text-sm text-purple-200/70">Administra los usuarios del sistema</p>
                    </div>
                </div>
                <Button
                    @click="openCreateModal"
                    class="bg-gradient-to-r from-purple-600 to-indigo-600 shadow-lg shadow-purple-500/30 transition-all hover:scale-105 hover:from-purple-700 hover:to-indigo-700"
                >
                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="5" y2="19" />
                        <line x1="5" x2="19" y1="12" y2="12" />
                    </svg>
                    Nuevo Usuario
                </Button>
            </div>
        </div>

        <!-- Search -->
        <div class="relative w-full sm:max-w-xs">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
            </svg>
            <Input v-model="searchQuery" placeholder="Buscar usuarios..." class="pl-10 h-9 rounded-lg border-border/50 bg-card shadow-sm text-sm" />
        </div>

        <!-- Table -->
        <div class="flex-1 overflow-hidden rounded-xl border border-border/50 bg-card shadow-sm">
            <div class="h-full overflow-auto">
                <table class="w-full">
                    <thead class="sticky top-0 z-10 border-b border-border/50 bg-muted/80 backdrop-blur-sm">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">Rol</th>
                            <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">Registro</th>
                            <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-muted-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/30">
                        <tr v-for="user in filteredUsers" :key="user.id" class="transition-all hover:bg-muted/30">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-purple-600 to-indigo-500 text-sm font-bold text-white shadow-md">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="font-medium">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-muted-foreground">{{ user.email }}</td>
                            <td class="px-4 py-3">
                                <span v-if="user.id_rol" :class="roleBadge(roleNombre(user.id_rol))" class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium">
                                    {{ roleNombre(user.id_rol) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="user.activo ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'" class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-medium">
                                    {{ user.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-muted-foreground">{{ formatDate(user.created_at) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="toggleEstado(user)" :class="user.activo ? 'hover:border-red-500/50 hover:bg-red-500/10' : 'hover:border-emerald-500/50 hover:bg-emerald-500/10'" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border/50 bg-background transition-all" :title="user.activo ? 'Desactivar' : 'Activar'">
                                        <svg v-if="user.activo" class="h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" /><line x1="4.93" x2="19.07" y1="4.93" y2="19.07" />
                                        </svg>
                                        <svg v-else class="h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" /><path d="M8 12h8" /><path d="m12 8 4 4-4 4" />
                                        </svg>
                                    </button>
                                    <button @click="openEditModal(user)" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border/50 bg-background transition-all hover:border-blue-500/50 hover:bg-blue-500/10" title="Editar">
                                        <svg class="h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                            <path d="m15 5 4 4" />
                                        </svg>
                                    </button>
                                    <button @click="openDeleteModal(user)" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border/50 bg-background transition-all hover:border-red-500/50 hover:bg-red-500/10" title="Eliminar">
                                        <svg class="h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredUsers.length === 0">
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-muted text-4xl">👥</div>
                                    <p class="text-lg font-bold text-foreground">No se encontraron usuarios</p>
                                    <p class="text-sm text-muted-foreground">Crea el primer usuario para comenzar</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-border/50 bg-card p-4">
            <p class="text-sm text-muted-foreground">Mostrando {{ filteredUsers.length }} de {{ props.usuarios.total }} usuarios</p>
            <span class="rounded-full bg-purple-500/10 px-4 py-1.5 text-sm font-medium text-purple-500">
                Pág. {{ props.usuarios.current_page }} de {{ props.usuarios.last_page }}
            </span>
        </div>
    </div>

    <!-- Create Modal -->
    <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-center text-2xl">Nuevo Usuario</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitCreate" class="grid gap-4 py-4">
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <Label for="create-name">Nombre(s) <span class="text-red-500">*</span></Label>
                        <Input id="create-name" v-model="createForm.name" placeholder="Nombres" class="mt-1" required />
                        <InputError :message="createForm.errors.name" />
                    </div>
                    <div>
                        <Label for="create-apellido_paterno">Apellido paterno</Label>
                        <Input id="create-apellido_paterno" v-model="createForm.apellido_paterno" placeholder="Apellido paterno" class="mt-1" />
                        <InputError :message="createForm.errors.apellido_paterno" />
                    </div>
                    <div>
                        <Label for="create-apellido_materno">Apellido materno</Label>
                        <Input id="create-apellido_materno" v-model="createForm.apellido_materno" placeholder="Apellido materno" class="mt-1" />
                        <InputError :message="createForm.errors.apellido_materno" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <Label for="create-email">Correo electrónico <span class="text-red-500">*</span></Label>
                        <Input id="create-email" type="email" v-model="createForm.email" placeholder="email@ejemplo.com" class="mt-1" required />
                        <InputError :message="createForm.errors.email" />
                    </div>
                    <div>
                        <Label for="create-telefono">Teléfono</Label>
                        <Input id="create-telefono" v-model="createForm.telefono" placeholder="Teléfono" class="mt-1" />
                        <InputError :message="createForm.errors.telefono" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <Label for="create-fecha_ingreso">Fecha de ingreso</Label>
                        <Input id="create-fecha_ingreso" type="date" v-model="createForm.fecha_ingreso" class="mt-1" />
                        <InputError :message="createForm.errors.fecha_ingreso" />
                    </div>
                    <div>
                        <Label for="create-role">Rol <span class="text-red-500">*</span></Label>
                        <select id="create-id_rol" v-model="createForm.id_rol" class="mt-1 flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                            <option value="" disabled>Seleccionar rol</option>
                            <option v-for="rol in props.roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
                        </select>
                        <InputError :message="createForm.errors.id_rol" />
                    </div>
                </div>
                <div>
                    <Label for="create-password">Contraseña <span class="text-red-500">*</span></Label>
                    <Input id="create-password" type="password" v-model="createForm.password" placeholder="Mínimo 8 caracteres" class="mt-1" required />
                    <InputError :message="createForm.errors.password" />
                </div>
                <DialogFooter class="mt-4 gap-2">
                    <DialogClose as-child><Button type="button" variant="outline">Cancelar</Button></DialogClose>
                    <Button type="submit" :disabled="createForm.processing" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700">Crear Usuario</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Edit Modal -->
    <Dialog :open="isEditModalOpen" @update:open="isEditModalOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-center text-2xl">Editar Usuario</DialogTitle>
            </DialogHeader>
            <form @submit.prevent="submitEdit" class="grid gap-4 py-4">
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <Label for="edit-name">Nombre(s) <span class="text-red-500">*</span></Label>
                        <Input id="edit-name" v-model="editForm.name" class="mt-1" required />
                        <InputError :message="editForm.errors.name" />
                    </div>
                    <div>
                        <Label for="edit-apellido_paterno">Apellido paterno</Label>
                        <Input id="edit-apellido_paterno" v-model="editForm.apellido_paterno" class="mt-1" />
                        <InputError :message="editForm.errors.apellido_paterno" />
                    </div>
                    <div>
                        <Label for="edit-apellido_materno">Apellido materno</Label>
                        <Input id="edit-apellido_materno" v-model="editForm.apellido_materno" class="mt-1" />
                        <InputError :message="editForm.errors.apellido_materno" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <Label for="edit-email">Correo electrónico <span class="text-red-500">*</span></Label>
                        <Input id="edit-email" type="email" v-model="editForm.email" class="mt-1" required />
                        <InputError :message="editForm.errors.email" />
                    </div>
                    <div>
                        <Label for="edit-telefono">Teléfono</Label>
                        <Input id="edit-telefono" v-model="editForm.telefono" class="mt-1" />
                        <InputError :message="editForm.errors.telefono" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <Label for="edit-fecha_ingreso">Fecha de ingreso</Label>
                        <Input id="edit-fecha_ingreso" type="date" v-model="editForm.fecha_ingreso" class="mt-1" />
                        <InputError :message="editForm.errors.fecha_ingreso" />
                    </div>
                    <div>
                        <Label for="edit-role">Rol <span class="text-red-500">*</span></Label>
                        <select id="edit-id_rol" v-model="editForm.id_rol" class="mt-1 flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" required>
                            <option value="" disabled>Seleccionar rol</option>
                            <option v-for="rol in props.roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
                        </select>
                        <InputError :message="editForm.errors.id_rol" />
                    </div>
                </div>
                <div>
                    <Label for="edit-password">Nueva contraseña <span class="text-xs text-muted-foreground">(dejar vacío para mantener)</span></Label>
                    <Input id="edit-password" type="password" v-model="editForm.password" placeholder="Solo si deseas cambiarla" class="mt-1" />
                    <InputError :message="editForm.errors.password" />
                </div>
                <div>
                    <Label>Estado</Label>
                    <div class="mt-1 flex items-center gap-3">
                        <button
                            type="button"
                            @click="editForm.activo = !editForm.activo"
                            :class="editForm.activo
                                ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                                : 'bg-red-500/10 text-red-400 border-red-500/30'"
                            class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition-all hover:scale-105"
                        >
                            <span v-if="editForm.activo" class="flex h-4 w-4 items-center justify-center rounded-full bg-emerald-500">
                                <svg class="h-2.5 w-2.5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <span v-else class="flex h-4 w-4 items-center justify-center rounded-full bg-red-500">
                                <svg class="h-2.5 w-2.5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </span>
                            {{ editForm.activo ? 'Activo' : 'Inactivo' }}
                        </button>
                    </div>
                    <InputError :message="editForm.errors.activo" />
                </div>
                <DialogFooter class="mt-4 gap-2">
                    <DialogClose as-child><Button type="button" variant="outline">Cancelar</Button></DialogClose>
                    <Button type="submit" :disabled="editForm.processing" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700">Actualizar Usuario</Button>
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
                        <span class="text-3xl">⚠️</span>
                    </div>
                    <DialogTitle class="text-2xl">Eliminar Usuario</DialogTitle>
                    <p class="text-sm text-muted-foreground">Esta acción no se puede deshacer</p>
                </div>
            </DialogHeader>
            <div class="py-4 text-center">
                <p class="mb-2 text-lg font-semibold">{{ selectedUser?.name }}</p>
                <p class="text-sm text-muted-foreground">{{ selectedUser?.email }}</p>
            </div>
            <DialogFooter class="gap-2">
                <DialogClose as-child><Button type="button" variant="outline">Cancelar</Button></DialogClose>
                <Button @click="submitDelete" :disabled="deleteForm.processing" class="bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700">
                    {{ deleteForm.processing ? 'Eliminando...' : 'Eliminar Usuario' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
