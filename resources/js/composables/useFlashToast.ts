import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue3-toastify';

interface ToastMessage {
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
}

/** Shows the server's flashed `toast` prop whenever a page receives one. */
export function useFlashToast(): void {
    const page = usePage();

    watch(
        () => page.props.toast as ToastMessage | undefined,
        (notification) => {
            if (!notification?.message) return;

            const showToast = toast[notification.type] ?? toast;
            showToast(notification.message);
        },
        { immediate: true },
    );
}
