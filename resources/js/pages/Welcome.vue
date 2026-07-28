<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { login } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: false,
    },
);
</script>

<template>
    <Head title="El Chochito - Panadería & Pizzería" />

    <div class="flex min-h-screen flex-col bg-[#0a0a0a] text-white">
        <!-- Navbar -->
        <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-[#0a0a0a]/80 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg shadow-amber-500/30">
                        <span class="text-lg">🥖</span>
                    </div>
                    <div>
                        <span class="text-lg font-bold tracking-tight">El Chochito</span>
                        <span class="ml-2 text-xs text-amber-400/80">Panadería & Pizzería</span>
                    </div>
                </div>
                <nav class="flex items-center gap-4">
                    <a href="#inicio" class="text-sm text-gray-400 transition-colors hover:text-amber-400">Inicio</a>
                    <a href="#productos" class="text-sm text-gray-400 transition-colors hover:text-amber-400">Productos</a>
                    <a href="#nosotros" class="text-sm text-gray-400 transition-colors hover:text-amber-400">Nosotros</a>
                    <a href="#contacto" class="text-sm text-gray-400 transition-colors hover:text-amber-400">Contacto</a>
                        <Link
                            v-if="$page.props.auth.user"
                            href="/dashboard"
                            class="rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-amber-500/30 transition-all hover:scale-105 hover:from-amber-600 hover:to-orange-700"
                        >
                            Dashboard
                        </Link>
                        <Link
                            v-else
                            :href="login()"
                            class="rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-amber-500/30 transition-all hover:scale-105 hover:from-amber-600 hover:to-orange-700"
                        >
                            Iniciar Sesión
                        </Link>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <!-- Hero Section -->
            <section id="inicio" class="relative flex min-h-screen items-center overflow-hidden">
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute left-1/4 top-1/4 h-96 w-96 rounded-full bg-amber-500/10 blur-3xl" />
                    <div class="absolute bottom-1/4 right-1/4 h-80 w-80 rounded-full bg-orange-500/10 blur-3xl" />
                    <div class="absolute left-1/3 top-1/2 h-64 w-64 rounded-full bg-red-500/5 blur-3xl" />
                </div>
                <div class="mx-auto max-w-7xl px-6 pt-32">
                    <div class="grid items-center gap-12 lg:grid-cols-2">
                        <div class="space-y-8">
                            <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
                                <span class="h-2 w-2 animate-pulse rounded-full bg-amber-400" />
                                Horneando con amor desde 2024
                            </div>
                            <h1 class="text-5xl font-bold leading-tight tracking-tight lg:text-7xl">
                                El sabor
                                <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">tradicional</span>
                                que amas
                            </h1>
                            <p class="max-w-xl text-lg leading-relaxed text-gray-400">
                                Pan artesanal, pizzas horneadas en piedra y los mejores postres. 
                                Todo hecho con ingredientes frescos y el cariño de siempre.
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <Link
                                    v-if="!$page.props.auth.user"
                                    :href="login()"
                                    class="rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-8 py-4 font-bold shadow-xl shadow-amber-500/30 transition-all hover:scale-105 hover:from-amber-600 hover:to-orange-700"
                                >
                                    Ver Menú
                                </Link>
                                <Link
                                    v-else
                                    href="/dashboard"
                                    class="rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-8 py-4 font-bold shadow-xl shadow-amber-500/30 transition-all hover:scale-105 hover:from-amber-600 hover:to-orange-700"
                                >
                                    Ir al Panel
                                </Link>
                                <a
                                    href="#productos"
                                    class="rounded-xl border border-white/20 px-8 py-4 font-bold text-white/80 transition-all hover:border-amber-500/50 hover:text-amber-400"
                                >
                                    Conocer Más
                                </a>
                            </div>
                            <div class="flex items-center gap-8 pt-4">
                                <div class="text-center">
                                    <p class="text-3xl font-bold text-amber-400">15+</p>
                                    <p class="text-sm text-gray-500">Años de tradición</p>
                                </div>
                                <div class="h-12 w-px bg-white/10" />
                                <div class="text-center">
                                    <p class="text-3xl font-bold text-amber-400">50+</p>
                                    <p class="text-sm text-gray-500">Productos artesanales</p>
                                </div>
                                <div class="h-12 w-px bg-white/10" />
                                <div class="text-center">
                                    <p class="text-3xl font-bold text-amber-400">100%</p>
                                    <p class="text-sm text-gray-500">Frescura garantizada</p>
                                </div>
                            </div>
                        </div>
                        <div class="relative hidden lg:block">
                            <div class="absolute -inset-4 rounded-3xl bg-gradient-to-br from-amber-500/20 via-orange-500/10 to-transparent blur-2xl" />
                            <div class="relative grid grid-cols-2 gap-4">
                                <div class="space-y-4">
                                    <div class="overflow-hidden rounded-2xl">
                                        <img src="https://images.unsplash.com/photo-1549931319-a545753467f4?w=400&q=80" alt="Pan artesanal" class="h-48 w-full object-cover transition-transform duration-700 hover:scale-110" />
                                    </div>
                                    <div class="overflow-hidden rounded-2xl">
                                        <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&q=80" alt="Pizza artesanal" class="h-64 w-full object-cover transition-transform duration-700 hover:scale-110" />
                                    </div>
                                </div>
                                <div class="mt-12 space-y-4">
                                    <div class="overflow-hidden rounded-2xl">
                                        <img src="https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80" alt="Pan francés" class="h-64 w-full object-cover transition-transform duration-700 hover:scale-110" />
                                    </div>
                                    <div class="overflow-hidden rounded-2xl">
                                        <img src="https://images.unsplash.com/photo-1551103782-8ab07afd45c1?w=400&q=80" alt="Pastelería" class="h-48 w-full object-cover transition-transform duration-700 hover:scale-110" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Products Section -->
            <section id="productos" class="border-t border-white/5 py-24">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="mb-16 text-center">
                        <span class="mb-4 inline-block rounded-full bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">Nuestros Productos</span>
                        <h2 class="text-4xl font-bold tracking-tight">Hechos con los mejores ingredientes</h2>
                        <p class="mt-4 text-gray-400">Cada producto es preparado artesanalmente para brindarte la mejor experiencia</p>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition-all hover:border-amber-500/30 hover:bg-white/10 hover:shadow-xl hover:shadow-amber-500/10">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 text-3xl">
                                🥖
                            </div>
                            <h3 class="mb-2 text-lg font-bold">Pan Artesanal</h3>
                            <p class="text-sm text-gray-400">Pan francés, integral, bolillos, conchas y más. Horneados diariamente.</p>
                        </div>
                        <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition-all hover:border-amber-500/30 hover:bg-white/10 hover:shadow-xl hover:shadow-amber-500/10">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 text-3xl">
                                🍕
                            </div>
                            <h3 class="mb-2 text-lg font-bold">Pizzas Artesanales</h3>
                            <p class="text-sm text-gray-400">Masa madre, horneadas en piedra. Ingredientes frescos y gourmet.</p>
                        </div>
                        <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition-all hover:border-amber-500/30 hover:bg-white/10 hover:shadow-xl hover:shadow-amber-500/10">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 text-3xl">
                                🥐
                            </div>
                            <h3 class="mb-2 text-lg font-bold">Repostería</h3>
                            <p class="text-sm text-gray-400">Croissants, donas, pasteles y postres. Delicias que enamoran.</p>
                        </div>
                        <div class="group relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-6 transition-all hover:border-amber-500/30 hover:bg-white/10 hover:shadow-xl hover:shadow-amber-500/10">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 text-3xl">
                                ☕
                            </div>
                            <h3 class="mb-2 text-lg font-bold">Bebidas</h3>
                            <p class="text-sm text-gray-400">Café de especialidad, chocolate caliente y refrescos artesanales.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- About Section -->
            <section id="nosotros" class="border-t border-white/5 py-24">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid items-center gap-16 lg:grid-cols-2">
                        <div class="relative">
                            <div class="absolute -inset-4 rounded-3xl bg-gradient-to-br from-amber-500/10 to-orange-500/5 blur-2xl" />
                            <div class="relative grid grid-cols-2 gap-3">
                                <div class="overflow-hidden rounded-2xl">
                                    <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=400&q=80" alt="Panadería" class="h-56 w-full object-cover transition-transform duration-700 hover:scale-110" />
                                </div>
                                <div class="mt-8 overflow-hidden rounded-2xl">
                                    <img src="https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=400&q=80" alt="Pizza" class="h-48 w-full object-cover transition-transform duration-700 hover:scale-110" />
                                </div>
                                <div class="-mt-8 overflow-hidden rounded-2xl">
                                    <img src="https://images.unsplash.com/photo-1509365465985-25d11c17e812?w=400&q=80" alt="Pan" class="h-48 w-full object-cover transition-transform duration-700 hover:scale-110" />
                                </div>
                                <div class="overflow-hidden rounded-2xl">
                                    <img src="https://images.unsplash.com/photo-1486427944544-d2c246c4c7c5?w=400&q=80" alt="Pastel" class="h-56 w-full object-cover transition-transform duration-700 hover:scale-110" />
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <span class="inline-block rounded-full bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">Nuestra Historia</span>
                            <h2 class="text-4xl font-bold tracking-tight">Más de 15 años endulzando tu vida</h2>
                            <p class="leading-relaxed text-gray-400">
                                En <strong class="text-white">El Chochito</strong> comenzamos como un pequeño negocio familiar 
                                con un horno de leña y muchas ganas de compartir nuestra pasión por la panadería 
                                artesanal. Hoy, seguimos horneando con la misma receta de siempre: 
                                ingredientes de calidad, amor por lo que hacemos y el mejor servicio.
                            </p>
                            <p class="leading-relaxed text-gray-400">
                                Desde 2024, combinamos la tradición panadera con la auténtica pizza italiana, 
                                ofreciendo una experiencia única donde cada bocado te transporta a los sabores 
                                de siempre.
                            </p>
                            <div class="flex items-center gap-6 pt-4">
                                <div class="flex -space-x-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-[#0a0a0a] bg-gradient-to-br from-amber-400 to-orange-500 text-base font-bold text-white">M</div>
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-[#0a0a0a] bg-gradient-to-br from-amber-400 to-orange-500 text-base font-bold text-white">A</div>
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-[#0a0a0a] bg-gradient-to-br from-amber-400 to-orange-500 text-base font-bold text-white">R</div>
                                </div>
                                <p class="text-sm text-gray-400">Familia <span class="font-bold text-white">Chochito</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features / Stats -->
            <section class="border-t border-white/5 py-20">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid gap-8 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-8 text-center transition-all hover:border-amber-500/30">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 text-3xl">
                                🥖
                            </div>
                            <h3 class="text-lg font-bold">Horneado Diario</h3>
                            <p class="mt-2 text-sm text-gray-400">Pan fresco todas las mañanas, horneado desde las 5:00 AM</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-8 text-center transition-all hover:border-amber-500/30">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 text-3xl">
                                🍕
                            </div>
                            <h3 class="text-lg font-bold">Pizza en Piedra</h3>
                            <p class="mt-2 text-sm text-gray-400">Masa madre con 48 hrs de fermentación, horneada en horno de piedra</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-8 text-center transition-all hover:border-amber-500/30">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500/20 to-orange-500/20 text-3xl">
                                🎂
                            </div>
                            <h3 class="text-lg font-bold">Pedidos Especiales</h3>
                            <p class="mt-2 text-sm text-gray-400">Pasteles personalizados para tus eventos y celebraciones</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact CTA -->
            <section id="contacto" class="border-t border-white/5 py-24">
                <div class="mx-auto max-w-4xl px-6 text-center">
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-600 via-orange-600 to-amber-700 p-12 shadow-2xl">
                        <div class="absolute inset-0 opacity-10">
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white blur-3xl" />
                            <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-white blur-3xl" />
                        </div>
                        <div class="relative">
                            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-white/20 text-4xl backdrop-blur-sm">
                                🥖
                            </div>
                            <h2 class="text-3xl font-bold tracking-tight md:text-4xl">¿Listo para probar?</h2>
                            <p class="mx-auto mt-4 max-w-lg text-lg text-white/80">
                                Visítanos y descubre por qué somos la panadería y pizzería favorita de la colonia.
                            </p>
                            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                                <div class="flex items-center gap-3 rounded-xl bg-white/10 px-6 py-3 backdrop-blur-sm">
                                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                    <span class="text-sm font-medium">+502 1234-5678</span>
                                </div>
                                <div class="flex items-center gap-3 rounded-xl bg-white/10 px-6 py-3 backdrop-blur-sm">
                                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span class="text-sm font-medium">Col. Centro, Zona 1</span>
                                </div>
                            </div>
                            <div class="mt-6">
                                <div class="flex items-center justify-center gap-2 text-sm text-white/70">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    <span>Lun-Sáb: 6:00 AM - 9:00 PM | Dom: 7:00 AM - 6:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-white/10 py-12">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid gap-8 md:grid-cols-4">
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-base shadow-lg shadow-amber-500/30">
                                <span>🥖</span>
                            </div>
                            <span class="font-bold">El Chochito</span>
                        </div>
                        <p class="text-sm text-gray-500">
                            Panadería y pizzería artesanal. Tradición y sabor en cada bocado.
                        </p>
                    </div>
                    <div>
                        <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">Productos</h4>
                        <ul class="space-y-2 text-sm text-gray-500">
                            <li>Pan Artesanal</li>
                            <li>Pizzas</li>
                            <li>Repostería</li>
                            <li>Bebidas</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">Horarios</h4>
                        <ul class="space-y-2 text-sm text-gray-500">
                            <li>Lun - Sáb: 6:00 AM - 9:00 PM</li>
                            <li>Domingo: 7:00 AM - 6:00 PM</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-400">Contacto</h4>
                        <ul class="space-y-2 text-sm text-gray-500">
                            <li>+502 1234-5678</li>
                            <li>Col. Centro, Zona 1</li>
                            <li>info@elchochito.com</li>
                        </ul>
                    </div>
                </div>
                <div class="mt-12 border-t border-white/10 pt-8 text-center text-sm text-gray-600">
                    &copy; {{ new Date().getFullYear() }} El Chochito. Todos los derechos reservados.
                </div>
            </div>
        </footer>
    </div>
</template>
