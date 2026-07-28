<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{ status: number; message?: string }>();

const errors: Record<number, { title: string; description: string; icon: string; gradient: string }> = {
    403: {
        title: '403 — Acceso denegado',
        description: props.message ?? 'No tienes permiso para realizar esta acción.',
        icon: '🔒',
        gradient: 'from-red-600 to-rose-600',
    },
    404: {
        title: '404 — Página no encontrada',
        description: 'La página que buscas no existe.',
        icon: '🔍',
        gradient: 'from-amber-500 to-orange-600',
    },
    419: {
        title: '419 — Sesión expirada',
        description: 'Tu sesión ha expirado. Inicia sesión nuevamente.',
        icon: '⏰',
        gradient: 'from-yellow-500 to-amber-600',
    },
    429: {
        title: '429 — Demasiadas solicitudes',
        description: 'Has realizado demasiadas solicitudes. Intenta de nuevo más tarde.',
        icon: '🚦',
        gradient: 'from-orange-500 to-red-600',
    },
    500: {
        title: '500 — Error del servidor',
        description: 'Ocurrió un error inesperado. Intenta de nuevo más tarde.',
        icon: '⚙️',
        gradient: 'from-slate-600 to-gray-700',
    },
    503: {
        title: '503 — Servicio no disponible',
        description: 'El servicio está temporalmente fuera de servicio.',
        icon: '🔧',
        gradient: 'from-slate-500 to-gray-600',
    },
};

const error = computed(() => errors[props.status] ?? errors[500]);
</script>

<template>
    <Head :title="error.title" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-slate-900 via-purple-950 to-slate-900 p-6">
        <div class="relative w-full max-w-lg">
            <div class="absolute -inset-1 rounded-2xl bg-gradient-to-r from-purple-600/20 to-indigo-600/20 blur-xl" />

            <div class="relative rounded-2xl border border-white/10 bg-slate-900/80 p-8 text-center shadow-2xl backdrop-blur-xl">
                <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-2xl bg-gradient-to-br shadow-xl" :class="error.gradient">
                    <span class="text-5xl">{{ error.icon }}</span>
                </div>

                <h1 class="mb-2 text-6xl font-bold text-white">{{ status }}</h1>
                <h2 class="mb-4 text-xl font-semibold text-white/90">{{ error.title.replace(/^\d+ — /, '') }}</h2>
                <p class="mb-8 text-sm text-white/60">
                    {{ error.description }}
                </p>

                <div class="flex items-center justify-center gap-3">
                    <Link
                        href="/dashboard"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-purple-500/30 transition-all hover:scale-105 hover:from-purple-700 hover:to-indigo-700"
                    >
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                        Ir al inicio
                    </Link>
                    <button
                        @click="history.back()"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-6 py-3 text-sm font-semibold text-white/80 shadow-lg transition-all hover:border-white/20 hover:bg-white/10 hover:text-white"
                    >
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5" />
                            <path d="m12 19-7-7 7-7" />
                        </svg>
                        Volver atrás
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
