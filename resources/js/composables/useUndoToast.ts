import { createVNode, h, type VNode } from 'vue';
import { toast } from 'vue3-toastify';

/** How long the Undo toast stays up. */
export const UNDO_MS = 4500;

/**
 * A reversible action (archive, delete) happens at once, with no confirm dialog, and a toast offers
 * Undo for about 4.5 seconds. The toast is announced to screen readers as a status message.
 *
 * `undo` runs the opposite action (usually a restore request). The returned handle lets a caller
 * dismiss the toast early.
 */
export function useUndoToast() {
    function undoable(message: string, undo: () => void): void {
        const content = (): VNode =>
            createVNode(
                'div',
                {
                    role: 'status',
                    'aria-live': 'polite',
                    class: 'flex items-center justify-between gap-4',
                },
                [
                    h('span', message),
                    h(
                        'button',
                        {
                            type: 'button',
                            class: 'rounded px-2 py-1 text-sm font-semibold underline underline-offset-2 outline-none focus-visible:ring-2 focus-visible:ring-ring',
                            onClick: () => {
                                toast.remove(id);
                                undo();
                            },
                        },
                        'Undo',
                    ),
                ],
            );

        const id = toast(content, {
            autoClose: UNDO_MS,
            closeOnClick: false,
            pauseOnHover: true,
            type: 'default',
        });
    }

    return { undoable };
}
