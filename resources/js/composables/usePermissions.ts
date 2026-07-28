import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();
    const auth = computed(() => page.props.auth ?? {});

    const permisos = computed(() => auth.value.permisos ?? {});

    const can = (permiso: string): boolean => {
        return (permisos.value as Record<string, boolean>)[permiso] ?? false;
    };

    return { can, permisos };
}
