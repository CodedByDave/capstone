/** An address the customer saved in "My addresses". */
export interface SavedAddress {
    id: string;
    label: 'home' | 'work' | 'other';
    recipient_name: string;
    phone: string;
    address: string;
    latitude: number | null;
    longitude: number | null;
    is_default: boolean;
}

export const ADDRESS_LABELS: Record<SavedAddress['label'], string> = {
    home: 'Home',
    work: 'Work',
    other: 'Other',
};
