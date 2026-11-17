import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();
    const auth = computed(() => page.props.auth ?? {});

    const permisos = computed(() => auth.value.permisos ?? {});

    const can = (permiso: string): boolean => {
        if (auth.value.user?.id_rol === 1) {
            return true;
        }

        return (permisos.value as Record<string, boolean>)[permiso] ?? false;
    };

    return { can, permisos };
}
