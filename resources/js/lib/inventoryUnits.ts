/**
 * Which consumption (usage) units make sense for each purchase unit, e.g. a bottle holds liquid
 * (ml, liters) or by weight (kg), while a sack only holds g or kg. The same table drives both
 * dropdowns: picking a purchase unit narrows the consumption units, and picking a consumption
 * unit narrows the purchase units.
 */
export const USAGE_UNITS_BY_PURCHASE_UNIT: Record<string, string[]> = {
    pcs: ['pcs'],
    bottle: ['ml', 'g'],
    gallon: ['ml', 'g'],
    sack: ['g', 'pcs'],
    pack: ['pcs', 'g', 'ml'],
    box: ['pcs', 'g', 'ml'],
    roll: ['pcs'],
};

/** Consumption units allowed for a purchase unit, or null when there is no restriction. */
export function usageUnitsFor(purchaseUnit: string): string[] | null {
    return USAGE_UNITS_BY_PURCHASE_UNIT[purchaseUnit] ?? null;
}

/** Purchase units that fit a consumption unit, or null when the unit is unknown (older items). */
export function purchaseUnitsFor(usageUnit: string): string[] | null {
    const matches = Object.entries(USAGE_UNITS_BY_PURCHASE_UNIT)
        .filter(([, usageUnits]) => usageUnits.includes(usageUnit))
        .map(([purchaseUnit]) => purchaseUnit);

    return matches.length > 0 ? matches : null;
}
