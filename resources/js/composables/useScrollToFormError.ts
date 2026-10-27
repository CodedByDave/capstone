import { nextTick } from 'vue';

/**
 * Brings the first validation message on the page into view, and focuses the field it belongs to.
 * Messages are the red helper texts the forms render under a field, in page order, so the first one
 * is the one nearest the top.
 */
export async function scrollToFirstError(): Promise<void> {
    await nextTick();

    const message = document.querySelector<HTMLElement>(
        'form p.text-red-500, form [data-field-error]',
    );
    if (!message) return;

    message.scrollIntoView({ behavior: 'smooth', block: 'center' });

    const field = message
        .closest('div')
        ?.querySelector<HTMLElement>('input, select, textarea, button');
    field?.focus({ preventScroll: true });
}
