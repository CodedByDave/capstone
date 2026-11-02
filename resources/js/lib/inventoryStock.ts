/** What is needed to describe an item's stock in the packages it is bought in. */
export interface StockItem {
    quantity: number | string;
    unit: string;
    /** How the item is bought (e.g. "bottle"). */
    package_unit?: string | null;
    /** How much of `unit` one package holds (e.g. 5000). */
    package_size?: number | null;
}

export function formatQuantity(quantity: number | string): string {
    return Number(quantity).toLocaleString('en-PH', {
        maximumFractionDigits: 2,
    });
}

export function plural(word: string, count: number): string {
    if (count === 1) return word;
    return /(s|x|ch|sh)$/i.test(word) ? `${word}es` : `${word}s`;
}

/**
 * Stock in the packages it is bought in: whole packages plus what is left of an opened one,
 * e.g. 17,500 ml at 5,000 ml per bottle is "3 bottles + 2,500 ml". Null when no package is set.
 */
export function packageStock(item: StockItem) {
    const size = Number(item.package_size) || 0;
    const name = item.package_unit?.trim();
    if (!name || size <= 0) return null;

    const quantity = Number(item.quantity) || 0;
    const full = Math.floor(quantity / size);
    const remainder = Math.round((quantity - full * size) * 100) / 100;

    return {
        full: `${full.toLocaleString('en-PH')} ${plural(name, full)}`,
        remainder:
            remainder > 0 ? `${formatQuantity(remainder)} ${item.unit}` : null,
        perPackage: `${formatQuantity(size)} ${item.unit} per ${name}`,
    };
}
