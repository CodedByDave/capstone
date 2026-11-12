import type { StatusTone } from '@/components/data-table';

export type PurchaseOrderStatus =
    | 'draft'
    | 'sent'
    | 'confirmed'
    | 'declined'
    | 'received'
    | 'cancelled';

/** How each purchase order status reads to the shop: a label and a badge color. */
export const PO_STATUS: Record<
    PurchaseOrderStatus,
    { label: string; tone: StatusTone }
> = {
    draft: { label: 'Draft', tone: 'neutral' },
    sent: { label: 'Waiting for supplier', tone: 'info' },
    confirmed: { label: 'Confirmed', tone: 'warning' },
    declined: { label: 'Declined', tone: 'danger' },
    received: { label: 'Received', tone: 'success' },
    cancelled: { label: 'Cancelled', tone: 'neutral' },
};

export function peso(value: string | number | null | undefined): string {
    return `₱${Number(value ?? 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

/** "5", "2.5": quantities without trailing zeros. */
export function qty(value: string | number | null | undefined): string {
    return Number(value ?? 0).toLocaleString('en-PH', {
        maximumFractionDigits: 2,
    });
}

export function shortDate(value: string | null | undefined): string {
    return value
        ? new Date(value).toLocaleDateString('en-PH', {
              month: 'short',
              day: 'numeric',
              year: 'numeric',
          })
        : '—';
}
