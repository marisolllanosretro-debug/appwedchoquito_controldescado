<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePermissions } from '@/composables/usePermissions';
import { precio as actualizarPrecio } from '@/routes/catalogo/productos';
import { store as registrarPedido } from '@/routes/pedidos';
import { store as registrarProducto } from '@/routes/productos';

interface Producto {
    id: number;
    nombre: string;
    categoria: string;
    descripcion: string | null;
    precio_venta: number | string;
    stock: number;
    stock_minimo: number;
    unidad_medida: string;
}

interface Categoria {
    id: number;
    nombre: string;
    icono: string | null;
}
interface ItemCarrito {
    productoId: number;
    cantidad: number;
}

const props = defineProps<{ productos: Producto[]; categorias: Categoria[] }>();
const page = usePage();
const { can } = usePermissions();
const puedeGestionarPrecios = can('gestionar_productos');
const busqueda = ref('');
const categoriaActiva = ref('Todas');
const carrito = ref<ItemCarrito[]>([]);
const carritoAbierto = ref(false);
const precioAbierto = ref(false);
const productoAbierto = ref(false);
const productoEditando = ref<Producto | null>(null);
const precioForm = useForm({ precio_venta: '' });
const unidadesMedida = [
    'Unidad',
    'Kilogramo',
    'Libra',
    'Litro',
    'Quintal',
    'Arroba',
    'Caja',
    'Bolsa',
    'Saco',
    'Paquete',
    'Docena',
];
const productoForm = useForm({
    nombre: '',
    id_categoria: '' as string | number,
    descripcion: '',
    precio_venta: '',
    stock: '0',
    stock_minimo: '0',
    unidad_medida: 'Unidad',
});
const pedidoForm = useForm({
    cliente_nombre: page.props.auth.user?.name ?? '',
    cliente_email: page.props.auth.user?.email ?? '',
    cliente_telefono: '',
    direccion: '',
    metodo_pago: 'efectivo',
    items: [] as Array<{ producto_id: number; cantidad: number }>,
});

const productosFiltrados = computed(() => {
    const termino = busqueda.value.trim().toLocaleLowerCase();

    return props.productos.filter((producto) => {
        const coincideCategoria =
            categoriaActiva.value === 'Todas' ||
            producto.categoria === categoriaActiva.value;
        const coincideBusqueda =
            termino === '' ||
            producto.nombre.toLocaleLowerCase().includes(termino) ||
            producto.categoria.toLocaleLowerCase().includes(termino) ||
            producto.descripcion?.toLocaleLowerCase().includes(termino);

        return coincideCategoria && coincideBusqueda;
    });
});

const productosPorCategoria = computed(() => {
    const grupos = new Map<string, Producto[]>();

    productosFiltrados.value.forEach((producto) => {
        const productos = grupos.get(producto.categoria) ?? [];
        productos.push(producto);
        grupos.set(producto.categoria, productos);
    });

    return Array.from(grupos, ([nombre, productos]) => ({
        nombre,
        icono:
            props.categorias.find((categoria) => categoria.nombre === nombre)
                ?.icono ?? '📦',
        productos,
    }));
});

const lineasCarrito = computed(() =>
    carrito.value.flatMap((item) => {
        const producto = props.productos.find(
            (candidato) => candidato.id === item.productoId,
        );

        return producto ? [{ ...item, producto }] : [];
    }),
);
const cantidadCarrito = computed(() =>
    carrito.value.reduce((total, item) => total + item.cantidad, 0),
);
const totalCarrito = computed(() =>
    lineasCarrito.value.reduce(
        (total, item) =>
            total + Number(item.producto.precio_venta) * item.cantidad,
        0,
    ),
);
const primerErrorItems = computed(() => {
    const errors = pedidoForm.errors as Record<string, string>;

    return Object.entries(errors).find(([campo]) =>
        campo.startsWith('items'),
    )?.[1];
});

function formatCurrency(value: number | string): string {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
    }).format(Number(value));
}

function cantidadDe(productoId: number): number {
    return (
        carrito.value.find((item) => item.productoId === productoId)
            ?.cantidad ?? 0
    );
}

function agregar(producto: Producto): void {
    const item = carrito.value.find(
        (candidato) => candidato.productoId === producto.id,
    );

    if (item) {
        if (item.cantidad < producto.stock) {
            item.cantidad++;
        }

        return;
    }

    if (producto.stock > 0) {
        carrito.value.push({ productoId: producto.id, cantidad: 1 });
    }
}

function disminuir(productoId: number): void {
    const item = carrito.value.find(
        (candidato) => candidato.productoId === productoId,
    );

    if (!item) {
        return;
    }

    if (item.cantidad === 1) {
        return quitar(productoId);
    }

    item.cantidad--;
}

function quitar(productoId: number): void {
    carrito.value = carrito.value.filter(
        (item) => item.productoId !== productoId,
    );
}

function abrirEdicionPrecio(producto: Producto): void {
    productoEditando.value = producto;
    precioForm.precio_venta = String(producto.precio_venta);
    precioForm.clearErrors();
    precioAbierto.value = true;
}

function guardarPrecio(): void {
    if (!productoEditando.value) {
        return;
    }

    precioForm.patch(actualizarPrecio.url(productoEditando.value), {
        preserveScroll: true,
        onSuccess: () => {
            precioAbierto.value = false;
            productoEditando.value = null;
            precioForm.reset();
        },
    });
}

function guardarProducto(): void {
    productoForm.post(registrarProducto.url(), {
        preserveScroll: true,
        onSuccess: () => {
            productoAbierto.value = false;
            productoForm.reset();
        },
    });
}

function efectuarCompra(): void {
    pedidoForm.items = carrito.value.map((item) => ({
        producto_id: item.productoId,
        cantidad: item.cantidad,
    }));
    pedidoForm.post(registrarPedido.url());
}
</script>

<template>
    <Head title="Catálogo y pedidos" />
    <main
        class="mx-auto flex w-full max-w-screen-2xl flex-1 flex-col gap-5 p-3 sm:p-6"
    >
        <section
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-950 via-red-800 to-amber-600 p-5 text-white shadow-xl sm:p-8"
        >
            <div
                class="absolute -top-16 -right-12 h-56 w-56 rounded-full bg-yellow-300/20 blur-3xl"
            />
            <div
                class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
            >
                <div class="max-w-2xl">
                    <span
                        class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur"
                        >Panadería El Choquito</span
                    >
                    <h1
                        class="mt-3 text-2xl font-black tracking-tight sm:text-4xl"
                    >
                        Catálogo y pedidos
                    </h1>
                    <p class="mt-2 text-sm text-amber-50 sm:text-base">
                        Selecciona productos, confirma las cantidades y completa
                        la compra en pocos pasos.
                    </p>
                </div>
                <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                    <Button
                        v-if="puedeGestionarPrecios"
                        class="h-12 w-full bg-amber-400 text-black hover:bg-amber-300 lg:w-auto"
                        @click="productoAbierto = true"
                        >＋ Nuevo producto</Button
                    >
                    <Button
                        class="h-12 w-full bg-white text-red-900 hover:bg-amber-50 lg:w-auto"
                        @click="carritoAbierto = true"
                    >
                        🛒 Ver carrito
                        <span
                            class="ml-2 rounded-full bg-red-700 px-2 py-0.5 text-xs text-white"
                            >{{ cantidadCarrito }}</span
                        >
                    </Button>
                </div>
            </div>
        </section>

        <section class="space-y-3">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <span
                        class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2"
                        >🔎</span
                    >
                    <Input
                        v-model="busqueda"
                        class="h-11 pl-10"
                        placeholder="Buscar por producto o categoría..."
                    />
                </div>
                <Button
                    v-if="cantidadCarrito > 0"
                    class="h-11 sm:hidden"
                    @click="carritoAbierto = true"
                    >Continuar compra ·
                    {{ formatCurrency(totalCarrito) }}</Button
                >
            </div>
            <div class="flex gap-2 overflow-x-auto pb-1">
                <button
                    v-for="categoria in [
                        { id: 0, nombre: 'Todas', icono: '✨' },
                        ...categorias,
                    ]"
                    :key="categoria.id"
                    type="button"
                    class="shrink-0 rounded-full border px-4 py-2 text-sm font-medium transition"
                    :class="
                        categoriaActiva === categoria.nombre
                            ? 'border-amber-500 bg-amber-500 text-black'
                            : 'border-border bg-card hover:border-amber-500'
                    "
                    @click="categoriaActiva = categoria.nombre"
                >
                    {{ categoria.icono || '📦' }} {{ categoria.nombre }}
                </button>
            </div>
        </section>

        <div v-if="productosPorCategoria.length" class="space-y-7">
            <section
                v-for="grupo in productosPorCategoria"
                :key="grupo.nombre"
                class="space-y-3"
            >
                <header class="flex items-center gap-3 border-b pb-3">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/15 text-2xl"
                        >{{ grupo.icono }}</span
                    >
                    <div>
                        <h2 class="text-xl font-black">{{ grupo.nombre }}</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ grupo.productos.length }} producto{{
                                grupo.productos.length === 1 ? '' : 's'
                            }}
                        </p>
                    </div>
                </header>
                <div
                    class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
                >
                    <article
                        v-for="producto in grupo.productos"
                        :key="producto.id"
                        class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-border/70 bg-card shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                    >
                        <div
                            class="flex min-h-32 items-center justify-center bg-gradient-to-br from-amber-100 to-orange-200 p-5 dark:from-amber-950/40 dark:to-red-950/30"
                        >
                            <span class="text-6xl" aria-hidden="true">{{
                                categorias.find(
                                    (categoria) =>
                                        categoria.nombre === producto.categoria,
                                )?.icono || '🥖'
                            }}</span>
                        </div>
                        <div class="flex flex-1 flex-col gap-3 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p
                                        class="text-xs font-semibold tracking-wide text-amber-600 uppercase"
                                    >
                                        {{ producto.categoria }}
                                    </p>
                                    <h2 class="truncate text-lg font-bold">
                                        {{ producto.nombre }}
                                    </h2>
                                </div>
                                <button
                                    v-if="puedeGestionarPrecios"
                                    type="button"
                                    class="shrink-0 rounded-lg border px-2.5 py-1.5 text-xs font-semibold hover:border-amber-500 hover:bg-amber-500/10"
                                    :aria-label="`Editar precio de ${producto.nombre}`"
                                    @click="abrirEdicionPrecio(producto)"
                                >
                                    ✏️ Precio
                                </button>
                            </div>
                            <p
                                v-if="producto.descripcion"
                                class="line-clamp-2 min-h-10 text-sm text-muted-foreground"
                            >
                                {{ producto.descripcion }}
                            </p>
                            <div
                                v-else
                                class="min-h-10 text-sm text-muted-foreground"
                            >
                                Producto disponible para pedido.
                            </div>
                            <div
                                class="mt-auto flex items-end justify-between gap-3"
                            >
                                <div>
                                    <p
                                        class="text-2xl font-black text-amber-600"
                                    >
                                        {{
                                            formatCurrency(
                                                producto.precio_venta,
                                            )
                                        }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        por
                                        {{
                                            producto.unidad_medida.toLocaleLowerCase()
                                        }}
                                    </p>
                                </div>
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="
                                        producto.stock === 0
                                            ? 'bg-red-500/10 text-red-600'
                                            : producto.stock <=
                                                producto.stock_minimo
                                              ? 'bg-amber-500/10 text-amber-700'
                                              : 'bg-emerald-500/10 text-emerald-700'
                                    "
                                    >{{
                                        producto.stock === 0
                                            ? 'Agotado'
                                            : `${producto.stock} disponibles`
                                    }}</span
                                >
                            </div>
                            <div
                                v-if="cantidadDe(producto.id)"
                                class="grid grid-cols-[2.75rem_1fr_2.75rem] items-center overflow-hidden rounded-xl border"
                            >
                                <button
                                    type="button"
                                    class="h-11 text-xl hover:bg-muted"
                                    @click="disminuir(producto.id)"
                                >
                                    −</button
                                ><span class="text-center font-bold"
                                    >{{ cantidadDe(producto.id) }} en
                                    carrito</span
                                ><button
                                    type="button"
                                    class="h-11 text-xl hover:bg-muted disabled:opacity-40"
                                    :disabled="
                                        cantidadDe(producto.id) >=
                                        producto.stock
                                    "
                                    @click="agregar(producto)"
                                >
                                    +
                                </button>
                            </div>
                            <Button
                                v-else
                                class="h-11 w-full bg-amber-500 text-black hover:bg-amber-400"
                                :disabled="producto.stock === 0"
                                @click="agregar(producto)"
                                >{{
                                    producto.stock === 0
                                        ? 'Sin existencias'
                                        : 'Agregar al pedido'
                                }}</Button
                            >
                        </div>
                    </article>
                </div>
            </section>
        </div>
        <section
            v-else
            class="rounded-2xl border border-dashed bg-card p-10 text-center"
        >
            <div class="text-5xl">🔍</div>
            <h2 class="mt-3 text-lg font-bold">No encontramos productos</h2>
            <p class="text-sm text-muted-foreground">
                Prueba otra búsqueda o elige una categoría diferente.
            </p>
        </section>
        <button
            v-if="cantidadCarrito > 0"
            type="button"
            class="fixed right-5 bottom-5 z-30 hidden items-center gap-3 rounded-full bg-red-800 px-5 py-3 text-sm font-bold text-white shadow-2xl transition hover:scale-105 sm:flex"
            @click="carritoAbierto = true"
        >
            🛒 {{ cantidadCarrito }} · {{ formatCurrency(totalCarrito) }}
        </button>
    </main>

    <Dialog :open="productoAbierto" @update:open="productoAbierto = $event">
        <DialogContent class="max-h-[92vh] overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Agregar producto al catálogo</DialogTitle>
                <DialogDescription
                    >Registra lo que vas a vender y selecciona la categoría
                    donde aparecerá.</DialogDescription
                >
            </DialogHeader>
            <form class="space-y-4" @submit.prevent="guardarProducto">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2 sm:col-span-2">
                        <Label for="producto-nombre">Nombre del producto</Label>
                        <Input
                            id="producto-nombre"
                            v-model="productoForm.nombre"
                            maxlength="255"
                            placeholder="Ej. Pan de queso"
                            required
                            autofocus
                        />
                        <InputError :message="productoForm.errors.nombre" />
                    </div>
                    <div class="space-y-2">
                        <Label for="producto-categoria">Categoría</Label>
                        <select
                            id="producto-categoria"
                            v-model="productoForm.id_categoria"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            required
                        >
                            <option value="" disabled>
                                Selecciona una categoría
                            </option>
                            <option
                                v-for="categoria in categorias"
                                :key="categoria.id"
                                :value="categoria.id"
                            >
                                {{ categoria.icono || '📦' }}
                                {{ categoria.nombre }}
                            </option>
                        </select>
                        <InputError
                            :message="productoForm.errors.id_categoria"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="producto-unidad">Unidad de venta</Label>
                        <select
                            id="producto-unidad"
                            v-model="productoForm.unidad_medida"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            required
                        >
                            <option
                                v-for="unidad in unidadesMedida"
                                :key="unidad"
                                :value="unidad"
                            >
                                {{ unidad }}
                            </option>
                        </select>
                        <InputError
                            :message="productoForm.errors.unidad_medida"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="producto-precio"
                            >Precio de venta (Bs)</Label
                        >
                        <Input
                            id="producto-precio"
                            v-model="productoForm.precio_venta"
                            type="number"
                            min="0.01"
                            max="99999999.99"
                            step="0.01"
                            placeholder="0.00"
                            required
                        />
                        <InputError
                            :message="productoForm.errors.precio_venta"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="producto-stock">Cantidad disponible</Label>
                        <Input
                            id="producto-stock"
                            v-model="productoForm.stock"
                            type="number"
                            min="0"
                            step="1"
                            required
                        />
                        <InputError :message="productoForm.errors.stock" />
                    </div>
                    <div class="space-y-2">
                        <Label for="producto-stock-minimo"
                            >Avisar cuando queden</Label
                        >
                        <Input
                            id="producto-stock-minimo"
                            v-model="productoForm.stock_minimo"
                            type="number"
                            min="0"
                            step="1"
                            required
                        />
                        <InputError
                            :message="productoForm.errors.stock_minimo"
                        />
                    </div>
                    <div class="space-y-2 sm:col-span-2">
                        <Label for="producto-descripcion">Descripción</Label>
                        <textarea
                            id="producto-descripcion"
                            v-model="productoForm.descripcion"
                            rows="3"
                            maxlength="500"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            placeholder="Descripción opcional del producto"
                        />
                        <InputError
                            :message="productoForm.errors.descripcion"
                        />
                    </div>
                </div>
                <DialogFooter class="gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="productoAbierto = false"
                        >Cancelar</Button
                    >
                    <Button
                        type="submit"
                        class="bg-amber-500 text-black hover:bg-amber-400"
                        :disabled="productoForm.processing"
                        >{{
                            productoForm.processing
                                ? 'Guardando...'
                                : 'Agregar al catálogo'
                        }}</Button
                    >
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog :open="precioAbierto" @update:open="precioAbierto = $event">
        <DialogContent class="sm:max-w-md"
            ><DialogHeader
                ><DialogTitle>Actualizar precio de venta</DialogTitle
                ><DialogDescription
                    >{{ productoEditando?.nombre }}. El nuevo precio se aplicará
                    a los próximos pedidos.</DialogDescription
                ></DialogHeader
            >
            <form class="space-y-4" @submit.prevent="guardarPrecio">
                <div class="space-y-2">
                    <Label for="precio_venta">Precio en bolivianos</Label
                    ><Input
                        id="precio_venta"
                        v-model="precioForm.precio_venta"
                        type="number"
                        min="0.01"
                        max="9999999999.99"
                        step="0.01"
                        required
                        autofocus
                    /><InputError :message="precioForm.errors.precio_venta" />
                </div>
                <DialogFooter
                    ><Button
                        type="button"
                        variant="outline"
                        @click="precioAbierto = false"
                        >Cancelar</Button
                    ><Button type="submit" :disabled="precioForm.processing">{{
                        precioForm.processing
                            ? 'Guardando...'
                            : 'Guardar precio'
                    }}</Button></DialogFooter
                >
            </form>
        </DialogContent>
    </Dialog>

    <Dialog :open="carritoAbierto" @update:open="carritoAbierto = $event">
        <DialogContent class="max-h-[92vh] overflow-y-auto sm:max-w-3xl"
            ><DialogHeader
                ><DialogTitle>Finalizar pedido</DialogTitle
                ><DialogDescription
                    >Revisa el carrito y registra los datos del
                    comprador.</DialogDescription
                ></DialogHeader
            >
            <div
                v-if="lineasCarrito.length"
                class="grid gap-6 lg:grid-cols-[1fr_1.15fr]"
            >
                <section class="space-y-3">
                    <h3 class="font-semibold">Resumen de compra</h3>
                    <div
                        v-for="item in lineasCarrito"
                        :key="item.producto.id"
                        class="rounded-xl border p-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold">
                                    {{ item.producto.nombre }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{
                                        formatCurrency(
                                            item.producto.precio_venta,
                                        )
                                    }}
                                    c/u
                                </p>
                            </div>
                            <button
                                type="button"
                                class="text-xs font-semibold text-red-600 hover:underline"
                                @click="quitar(item.producto.id)"
                            >
                                Quitar
                            </button>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <div
                                class="flex items-center overflow-hidden rounded-lg border"
                            >
                                <button
                                    type="button"
                                    class="h-9 w-9 hover:bg-muted"
                                    @click="disminuir(item.producto.id)"
                                >
                                    −</button
                                ><span
                                    class="w-10 text-center text-sm font-bold"
                                    >{{ item.cantidad }}</span
                                ><button
                                    type="button"
                                    class="h-9 w-9 hover:bg-muted disabled:opacity-40"
                                    :disabled="
                                        item.cantidad >= item.producto.stock
                                    "
                                    @click="agregar(item.producto)"
                                >
                                    +
                                </button>
                            </div>
                            <strong>{{
                                formatCurrency(
                                    Number(item.producto.precio_venta) *
                                        item.cantidad,
                                )
                            }}</strong>
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between rounded-xl bg-amber-500/10 p-4 text-lg"
                    >
                        <span class="font-semibold">Total</span
                        ><strong class="text-xl text-amber-700">{{
                            formatCurrency(totalCarrito)
                        }}</strong>
                    </div>
                </section>
                <form
                    id="pedido-form"
                    class="space-y-4"
                    @submit.prevent="efectuarCompra"
                >
                    <h3 class="font-semibold">Datos del comprador</h3>
                    <div class="space-y-2">
                        <Label for="cliente_nombre">Nombre completo</Label
                        ><Input
                            id="cliente_nombre"
                            v-model="pedidoForm.cliente_nombre"
                            autocomplete="name"
                            required
                        /><InputError
                            :message="pedidoForm.errors.cliente_nombre"
                        />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="cliente_email">Correo electrónico</Label
                            ><Input
                                id="cliente_email"
                                v-model="pedidoForm.cliente_email"
                                type="email"
                                autocomplete="email"
                                required
                            /><InputError
                                :message="pedidoForm.errors.cliente_email"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="cliente_telefono">Teléfono</Label
                            ><Input
                                id="cliente_telefono"
                                v-model="pedidoForm.cliente_telefono"
                                autocomplete="tel"
                                placeholder="Opcional"
                            /><InputError
                                :message="pedidoForm.errors.cliente_telefono"
                            />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="direccion">Dirección</Label
                        ><Input
                            id="direccion"
                            v-model="pedidoForm.direccion"
                            autocomplete="street-address"
                            placeholder="Opcional"
                        /><InputError :message="pedidoForm.errors.direccion" />
                    </div>
                    <fieldset class="space-y-2">
                        <legend class="text-sm font-medium">
                            Método de pago
                        </legend>
                        <div class="grid grid-cols-2 gap-3">
                            <label
                                class="cursor-pointer rounded-xl border p-3 text-sm"
                                :class="
                                    pedidoForm.metodo_pago === 'efectivo'
                                        ? 'border-amber-500 bg-amber-500/10'
                                        : ''
                                "
                                ><input
                                    v-model="pedidoForm.metodo_pago"
                                    class="mr-2"
                                    type="radio"
                                    value="efectivo"
                                />
                                Efectivo</label
                            ><label
                                class="cursor-pointer rounded-xl border p-3 text-sm"
                                :class="
                                    pedidoForm.metodo_pago === 'transferencia'
                                        ? 'border-amber-500 bg-amber-500/10'
                                        : ''
                                "
                                ><input
                                    v-model="pedidoForm.metodo_pago"
                                    class="mr-2"
                                    type="radio"
                                    value="transferencia"
                                />
                                Transferencia</label
                            >
                        </div>
                        <InputError :message="pedidoForm.errors.metodo_pago" />
                    </fieldset>
                    <InputError
                        :message="pedidoForm.errors.items || primerErrorItems"
                    />
                </form>
            </div>
            <div v-else class="rounded-xl border border-dashed p-8 text-center">
                <div class="text-4xl">🛒</div>
                <p class="mt-2 font-semibold">Tu carrito está vacío</p>
                <p class="text-sm text-muted-foreground">
                    Agrega al menos un producto para continuar.
                </p>
            </div>
            <DialogFooter v-if="lineasCarrito.length" class="gap-2"
                ><Button
                    type="button"
                    variant="outline"
                    @click="carritoAbierto = false"
                    >Seguir comprando</Button
                ><Button
                    form="pedido-form"
                    type="submit"
                    class="bg-emerald-600 hover:bg-emerald-500"
                    :disabled="pedidoForm.processing"
                    >{{
                        pedidoForm.processing
                            ? 'Procesando...'
                            : `Efectuar compra · ${formatCurrency(totalCarrito)}`
                    }}</Button
                ></DialogFooter
            >
        </DialogContent>
    </Dialog>
</template>
