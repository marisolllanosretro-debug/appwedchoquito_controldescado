<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Iniciar sesión" />

    <div class="relative flex min-h-svh w-full items-center justify-center overflow-hidden bg-gradient-to-br from-slate-950 via-purple-950 to-slate-950 animate-gradient">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute left-1/4 top-1/4 h-72 w-72 rounded-full bg-purple-500/20 blur-3xl animate-float" />
            <div class="absolute bottom-1/4 right-1/4 h-96 w-96 rounded-full bg-blue-500/15 blur-3xl animate-float-slow" />
            <div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-indigo-500/10 blur-3xl animate-pulse-glow" />
            <div class="absolute left-10 top-1/3 h-32 w-32 rounded-full bg-pink-500/10 blur-2xl animate-float" style="animation-delay: -3s;" />
            <div class="absolute right-10 bottom-1/3 h-40 w-40 rounded-full bg-cyan-500/10 blur-2xl animate-float-slow" style="animation-delay: -6s;" />
        </div>

        <div class="absolute inset-0" style="background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 40px 40px;" />

        <div class="relative z-10 w-full max-w-md px-4">
            <div class="mb-8 text-center">
                <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 shadow-lg shadow-purple-500/30">
                    <svg class="h-8 w-8 fill-current text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42">
                        <path d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-white">Bienvenido</h1>
                <p class="mt-2 text-sm text-slate-400">Ingresa tus credenciales para acceder a tu cuenta</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl">
                <div
                    v-if="status"
                    class="mb-4 rounded-lg border border-green-500/20 bg-green-500/10 px-4 py-3 text-center text-sm font-medium text-green-400"
                >
                    {{ status }}
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <div class="grid gap-2">
                        <Label for="email" class="text-sm font-medium text-slate-300">
                            Correo electrónico
                        </Label>
                        <div class="group relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 transition-colors">
                                <svg class="h-5 w-5 text-slate-500 transition-colors group-focus-within:text-purple-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                </svg>
                            </div>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                tabindex="1"
                                autocomplete="email"
                                placeholder="tu@ejemplo.com"
                                class="border-white/10 bg-white/5 pl-10 text-white placeholder:text-slate-500 focus:border-purple-500/50 focus:ring-purple-500/30"
                            />
                        </div>
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="text-sm font-medium text-slate-300">
                                Contraseña
                            </Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm text-purple-400 transition-colors hover:text-purple-300"
                                tabindex="5"
                            >
                                ¿Olvidaste tu contraseña?
                            </TextLink>
                        </div>
                        <div class="group relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 transition-colors">
                                <svg class="h-5 w-5 text-slate-500 transition-colors group-focus-within:text-purple-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </div>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                tabindex="2"
                                autocomplete="current-password"
                                placeholder="Introduce tu contraseña"
                                class="border-white/10 bg-white/5 pl-10 text-white placeholder:text-slate-500 focus:border-purple-500/50 focus:ring-purple-500/30"
                            />
                        </div>
                        <InputError :message="errors.password" />
                    </div>

                    <div class="flex items-center gap-3">
                        <Checkbox
                            id="remember"
                            name="remember"
                            tabindex="3"
                            class="border-white/20 data-[state=checked]:border-purple-500 data-[state=checked]:bg-purple-500"
                        />
                        <Label for="remember" class="text-sm font-normal text-slate-400 cursor-pointer select-none">
                            Recordarme en este dispositivo
                        </Label>
                    </div>

                    <Button
                        type="submit"
                        class="mt-2 w-full bg-gradient-to-r from-purple-600 to-indigo-600 font-medium text-white shadow-lg shadow-purple-500/25 transition-all hover:shadow-purple-500/40 hover:brightness-110 focus:ring-purple-500/30"
                        tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" />
                        <svg v-else class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                            <polyline points="10 17 15 12 10 7" />
                            <line x1="15" x2="3" y1="12" y2="12" />
                        </svg>
                        Entrar
                    </Button>
                </Form>
            </div>



            <div class="mt-8 flex items-center justify-center gap-2 text-xs text-slate-600">
                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                </svg>
                <span>Conexión segura y cifrada</span>
            </div>
        </div>
    </div>
</template>
