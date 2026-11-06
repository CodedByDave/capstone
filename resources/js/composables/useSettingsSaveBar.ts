import type { InertiaForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import {
    computed,
    inject,
    onBeforeUnmount,
    onMounted,
    provide,
    ref,
    shallowReactive,
    type InjectionKey,
} from 'vue';
import { toast } from 'vue3-toastify';

/** One part of a settings page that can hold unsaved changes. */
export interface SettingsSection {
    key: string;
    label: string;
    isDirty: () => boolean;
    /** Resolves true when the section saved (or had nothing to save). */
    save: () => Promise<boolean>;
    discard: () => void;
}

interface SettingsSaveBar {
    register: (section: SettingsSection) => void;
    unregister: (key: string) => void;
}

const KEY: InjectionKey<SettingsSaveBar> = Symbol('settings-save-bar');

/**
 * Called once by the settings page. Every section on it registers here, so the page can show a
 * single "Save all changes" bar instead of a Save button on each card.
 */
export function provideSettingsSaveBar() {
    const sections = shallowReactive(new Map<string, SettingsSection>());
    const saving = ref(false);

    provide(KEY, {
        register: (section) => sections.set(section.key, section),
        unregister: (key) => sections.delete(key),
    });

    const dirty = computed(() =>
        [...sections.values()].filter((section) => section.isDirty()),
    );

    async function saveAll() {
        if (saving.value || dirty.value.length === 0) return;
        saving.value = true;

        const failed: string[] = [];
        // One after another: each save reloads the page props, so they must not overlap.
        for (const section of [...dirty.value]) {
            if (!(await section.save())) failed.push(section.label);
        }

        saving.value = false;

        if (failed.length === 0) {
            toast.success('Settings saved.');
        } else {
            toast.error(
                `Not saved: ${failed.join(', ')}. Check the highlighted fields.`,
            );
        }
    }

    function discardAll() {
        dirty.value.forEach((section) => section.discard());
    }

    // Leaving the page (or closing the tab) with unsaved changes asks first.
    const removeBefore = router.on('before', (event) => {
        const visit = event.detail.visit;
        if (
            dirty.value.length === 0 ||
            saving.value ||
            visit.method !== 'get' ||
            visit.preserveState === true
        ) {
            return;
        }

        if (
            !window.confirm(
                'You have unsaved changes. Leave this page without saving?',
            )
        ) {
            return false;
        }
    });
    const warnOnClose = (event: BeforeUnloadEvent) => {
        if (dirty.value.length > 0 && !saving.value) {
            event.preventDefault();
        }
    };
    onMounted(() => window.addEventListener('beforeunload', warnOnClose));
    onBeforeUnmount(() => {
        removeBefore();
        window.removeEventListener('beforeunload', warnOnClose);
    });

    return { sections, dirty, saving, saveAll, discardAll };
}

/** Registers a card or block with the page's save bar for as long as it is on screen. */
export function useSettingsSection(section: SettingsSection) {
    const bar = inject(KEY, null);

    onMounted(() => bar?.register(section));
    onBeforeUnmount(() => bar?.unregister(section.key));
}

/** Saves an Inertia form with PATCH and reports whether it worked. Typed values stay in place. */
export function patchSettingsForm(
    form: InertiaForm<any>,
    url: string,
): Promise<boolean> {
    return new Promise((resolve) => {
        form.patch(url, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                // The saved values are the new starting point, so the section stops counting as changed.
                form.defaults();
                resolve(true);
            },
            onError: () => resolve(false),
            onCancel: () => resolve(false),
        });
    });
}
