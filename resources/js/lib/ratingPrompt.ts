/**
 * Remembers on this device that a customer chose "Maybe later" for a rating prompt, so the popup
 * does not reopen every time they visit the page. Storage can be blocked or full (private
 * windows), so every access is guarded and the popup simply shows again when it fails.
 */

const POSTPONE_HOURS = 24;

function key(id: string): string {
    return `rating-later:${id}`;
}

export function isPostponed(id: string): boolean {
    try {
        const stored = window.localStorage.getItem(key(id));
        if (!stored) return false;

        return Date.now() - Number(stored) < POSTPONE_HOURS * 3_600_000;
    } catch {
        return false;
    }
}

export function postpone(id: string): void {
    try {
        window.localStorage.setItem(key(id), String(Date.now()));
    } catch {
        // Nothing to do: the popup will just show again next visit.
    }
}
