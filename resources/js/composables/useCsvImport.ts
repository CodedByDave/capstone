import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

/**
 * Uploads a chosen CSV file to an import endpoint and reports the result.
 *
 * Bind `input` to a hidden `<input type="file" accept=".csv,text/csv">` and call `choose()` from a
 * menu item; the file is posted as soon as it is picked. Row errors come back as `errors` and are
 * shown in a toast, and the page props listed in `reload` are refreshed after a successful import.
 */
export function useCsvImport(url: () => string, reload: string[] = []) {
    const input = ref<HTMLInputElement | null>(null);
    const importing = ref(false);
    const errors = ref<string[]>([]);

    function choose() {
        input.value?.click();
    }

    async function onFileChange(event: Event) {
        const target = event.target as HTMLInputElement;
        const file = target.files?.[0];

        if (!file || importing.value) return;

        importing.value = true;
        errors.value = [];

        try {
            const body = new FormData();
            body.append('csv_file', file);
            const { data } = await axios.post(url(), body);
            toast.success(data.message ?? 'Import complete.');
            router.reload(reload.length ? { only: reload } : {});
        } catch (error) {
            if (axios.isAxiosError(error) && error.response?.status === 422) {
                const list: string[] =
                    error.response.data?.errors instanceof Array
                        ? error.response.data.errors
                        : Object.values(
                              error.response.data?.errors ?? {},
                          ).flat() as string[];
                errors.value = list;
                const shown = list.slice(0, 3).join(' ');
                const more = list.length > 3 ? ` (+${list.length - 3} more)` : '';
                toast.error(`${shown}${more}` || 'The CSV could not be imported.', {
                    autoClose: 8000,
                });
            } else {
                toast.error('Failed to import the CSV. Please try again.');
            }
        } finally {
            importing.value = false;
            target.value = '';
        }
    }

    return { input, importing, errors, choose, onFileChange };
}
