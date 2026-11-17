<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { catalogo } from '@/routes';

interface DetallePedido {
    id: number;
    producto_nombre: string;
    cantidad: number;
    precio_unitario: number | string;
    subtotal: number | string;
}

interface Pedido {
    numero: string;
    cliente_nombre: string;
    cliente_email: string;
    cliente_telefono: string | null;
    direccion: string | null;
    total: number | string;
    metodo_pago: string;
    estado: string;
    completado_at: string;
    detalles: DetallePedido[];
    registrado_por: { name: string } | null;
}

defineProps<{ pedido: Pedido }>();

function formatCurrency(value: number | string): string {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));
}

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('es-BO', {
        dateStyle: 'long',
        timeStyle: 'short',
    }).format(new Date(value));
}
</script>

<template>
    <Head :title="`Compra ${pedido.numero}`" />

    <main
        class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-5 p-3 sm:p-6"
    >
        <section class="overflow-hidden rounded-2xl border bg-card shadow-xl">
            <header
                class="bg-gradient-to-br from-emerald-700 to-emerald-500 p-6 text-center text-white sm:p-9"
            >
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-3xl text-emerald-700 shadow-lg"
                >
                    ✓
                </div>
                <p
                    class="mt-4 text-sm font-semibold tracking-[0.2em] text-emerald-50 uppercase"
                >
                    Compra efectuada
                </p>
                <h1 class="mt-1 text-2xl font-black sm:text-3xl">
                    Pedido {{ pedido.numero }}
                </h1>
                <p class="mt-2 text-sm text-emerald-50">
                    {{ formatDate(pedido.completado_at) }}
                </p>
            </header>

            <div class="space-y-6 p-4 sm:p-8">
                <div class="grid gap-4 sm:grid-cols-2">
                    <section class="rounded-xl border p-4">
                        <h2
                            class="text-xs font-bold tracking-wide text-muted-foreground uppercase"
                        >
                            Comprador
                        </h2>
                        <p class="mt-2 font-semibold">
                            {{ pedido.cliente_nombre }}
                        </p>
                        <p class="text-sm break-all text-muted-foreground">
                            {{ pedido.cliente_email }}
                        </p>
                        <p
                            v-if="pedido.cliente_telefono"
                            class="text-sm text-muted-foreground"
                        >
                            {{ pedido.cliente_telefono }}
                        </p>
                        <p v-if="pedido.direccion" class="mt-2 text-sm">
                            {{ pedido.direccion }}
                        </p>
                    </section>
                    <section class="rounded-xl border p-4">
                        <h2
                            class="text-xs font-bold tracking-wide text-muted-foreground uppercase"
                        >
                            Pago y registro
                        </h2>
                        <p class="mt-2 font-semibold capitalize">
                            {{ pedido.metodo_pago }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Estado:
                            <span
                                class="font-semibold text-emerald-600 capitalize"
                                >{{ pedido.estado }}</span
                            >
                        </p>
                        <p
                            v-if="pedido.registrado_por"
                            class="mt-2 text-sm text-muted-foreground"
                        >
                            Registrado por {{ pedido.registrado_por.name }}
                        </p>
                    </section>
                </div>

                <section>
                    <h2 class="mb-3 text-lg font-bold">Detalle del pedido</h2>
                    <div class="space-y-3 sm:hidden">
                        <article
                            v-for="detalle in pedido.detalles"
                            :key="detalle.id"
                            class="rounded-xl border p-4"
                        >
                            <p class="font-semibold">
                                {{ detalle.producto_nombre }}
                            </p>
                            <div
                                class="mt-2 flex justify-between text-sm text-muted-foreground"
                            >
                                <span
                                    >{{ detalle.cantidad }} ×
                                    {{
                                        formatCurrency(detalle.precio_unitario)
                                    }}</span
                                ><strong class="text-foreground">{{
                                    formatCurrency(detalle.subtotal)
                                }}</strong>
                            </div>
                        </article>
                    </div>
                    <div
                        class="hidden overflow-hidden rounded-xl border sm:block"
                    >
                        <table class="w-full">
                            <thead
                                class="bg-muted/60 text-left text-xs tracking-wide text-muted-foreground uppercase"
                            >
                                <tr>
                                    <th class="px-4 py-3">Producto</th>
                                    <th class="px-4 py-3 text-center">
                                        Cantidad
                                    </th>
                                    <th class="px-4 py-3 text-right">Precio</th>
                                    <th class="px-4 py-3 text-right">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="detalle in pedido.detalles"
                                    :key="detalle.id"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ detalle.producto_nombre }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        {{ detalle.cantidad }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        {{
                                            formatCurrency(
                                                detalle.precio_unitario,
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-semibold"
                                    >
                                        {{ formatCurrency(detalle.subtotal) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <div
                    class="flex items-center justify-between rounded-xl bg-emerald-500/10 p-4 sm:p-5"
                >
                    <span class="text-lg font-bold">Total pagado</span>
                    <strong
                        class="text-2xl font-black text-emerald-700 sm:text-3xl"
                        >{{ formatCurrency(pedido.total) }}</strong
                    >
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <Button as-child variant="outline"
                        ><Link :href="catalogo.url()"
                            >Volver al catálogo</Link
                        ></Button
                    >
                    <Button
                        type="button"
                        class="bg-emerald-600 hover:bg-emerald-500"
                        @click="window.print()"
                        >Imprimir comprobante</Button
                    >
                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>
@media print {
    button,
    a {
        display: none !important;
    }
}
</style>
