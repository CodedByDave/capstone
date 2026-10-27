export type StatusTone = 'success' | 'warning' | 'danger' | 'info' | 'neutral';

export const statusToneClasses: Record<
    StatusTone,
    { pill: string; dot: string }
> = {
    success: {
        pill: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        dot: 'bg-emerald-500',
    },
    warning: {
        pill: 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300',
        dot: 'bg-orange-500',
    },
    danger: {
        pill: 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-300',
        dot: 'bg-red-500',
    },
    info: {
        pill: 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        dot: 'bg-blue-500',
    },
    neutral: {
        pill: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        dot: 'bg-slate-400',
    },
};

const toneByStatus: Record<string, StatusTone> = {
    active: 'success',
    approved: 'success',
    completed: 'success',
    complete: 'success',
    delivered: 'success',
    paid: 'success',
    present: 'success',
    resolved: 'success',
    released: 'success',
    success: 'success',
    succeeded: 'success',
    in_stock: 'success',
    healthy: 'success',
    open: 'info',
    processing: 'info',
    in_progress: 'info',
    picked_up: 'info',
    ready: 'info',
    scheduled: 'info',
    under_review: 'info',
    pending: 'warning',
    partial: 'warning',
    partially_paid: 'warning',
    late: 'warning',
    low: 'warning',
    low_stock: 'warning',
    on_leave: 'warning',
    expiring: 'warning',
    draft: 'neutral',
    inactive: 'neutral',
    archived: 'neutral',
    closed: 'neutral',
    cancelled: 'danger',
    canceled: 'danger',
    disabled: 'danger',
    expired: 'danger',
    failed: 'danger',
    absent: 'danger',
    overdue: 'danger',
    rejected: 'danger',
    refunded: 'neutral',
    unpaid: 'danger',
    out_of_stock: 'danger',
    critical: 'danger',
};

/** A sensible default tone for common status words; pages may pass their own tone instead. */
export function statusTone(status: string | null | undefined): StatusTone {
    if (!status) return 'neutral';

    return (
        toneByStatus[status.toLowerCase().replace(/[\s-]+/g, '_')] ?? 'neutral'
    );
}

/** "partially_paid" -> "Partially paid". */
export function statusLabel(status: string | null | undefined): string {
    if (!status) return 'Unknown';
    const text = status.replace(/[_-]+/g, ' ').trim();

    return text.charAt(0).toUpperCase() + text.slice(1);
}
