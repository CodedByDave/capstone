export type ServiceCategory =
    | 'self_service'
    | 'full_service'
    | 'dry_cleaning'
    | 'ironing_pressing'
    | 'pickup_delivery'
    | 'specialty_bulk';

export const serviceCategories: Array<{
    value: ServiceCategory;
    label: string;
    description: string;
}> = [
    {
        value: 'self_service',
        label: 'Self-Service (Coin-Operated / Laundromat)',
        description:
            'Customers load, wash, and dry their clothes using the shop machines and pay per cycle.',
    },
    {
        value: 'full_service',
        label: 'Full-Service (Wash, Dry, and Fold)',
        description:
            'Shop staff sort, wash, dry, and fold laundry that customers drop off, usually priced by weight.',
    },
    {
        value: 'dry_cleaning',
        label: 'Dry Cleaning',
        description:
            'Specialized solvent-based cleaning for delicate fabrics, formalwear, and garments unsuitable for regular washing.',
    },
    {
        value: 'ironing_pressing',
        label: 'Ironing and Pressing Only',
        description:
            'Professional ironing or pressing for clothing that the customer has already washed.',
    },
    {
        value: 'pickup_delivery',
        label: 'Pickup and Delivery (Mobile Laundry)',
        description:
            'Laundry is collected from the customer and returned after it has been cleaned and folded.',
    },
    {
        value: 'specialty_bulk',
        label: 'Specialty or Bulk Care',
        description:
            'Special handling for bulky items, linens, uniforms, commercial loads, and other specialty laundry.',
    },
];

export function serviceCategoryLabel(category: ServiceCategory): string {
    return (
        serviceCategories.find((option) => option.value === category)?.label ??
        category
    );
}
