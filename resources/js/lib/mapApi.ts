import type { LatLng } from '@/lib/maps';
import axios from 'axios';

export interface FeeBreakdown {
    mode: 'fixed' | 'per_km';
    fee: number;
    base_fee: number;
    free_km: number;
    chargeable_km: number;
    rate_per_km: number;
    distance_fee: number;
    min_fee: number;
    min_applied: boolean;
}

export type FeeQuote =
    | {
          available: true;
          fee: number;
          fee_type: 'fixed' | 'per_km';
          distance_m: number | null;
          duration_s: number | null;
          breakdown: FeeBreakdown;
      }
    | { available: false; message: string };

export interface ShopRoute {
    distance_m: number;
    duration_s: number;
    geometry: { type: 'LineString'; coordinates: [number, number][] };
    steps: { instruction: string; distance_m: number; duration_s: number }[];
}

export interface OptimizedRoute {
    distance_m: number;
    duration_s: number;
    geometry: { type: 'LineString'; coordinates: [number, number][] };
    order: number[];
    stops: { index: number; sequence: number; lat: number; lng: number }[];
}

const to = (p: LatLng) => `${p.lng},${p.lat}`;

/** Delivery fee for a destination, priced on the server from the shop's own settings. */
export async function fetchFeeQuote(
    shopPublicId: string,
    destination: LatLng,
): Promise<FeeQuote> {
    try {
        const { data } = await axios.get<FeeQuote>(
            `/api/shops/${shopPublicId}/fee-quote`,
            { params: { to: to(destination) } },
        );

        return data;
    } catch (error) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            return {
                available: false,
                message:
                    error.response.data?.message ??
                    'Delivery is not available to that location.',
            };
        }

        throw error;
    }
}

export async function fetchShopRoute(
    shopPublicId: string,
    destination: LatLng,
): Promise<ShopRoute> {
    const { data } = await axios.get<ShopRoute>(
        `/api/shops/${shopPublicId}/route`,
        { params: { to: to(destination) } },
    );

    return data;
}

export async function optimizeStops(
    shopPublicId: string,
    stops: LatLng[],
): Promise<OptimizedRoute> {
    const { data } = await axios.post<OptimizedRoute>(
        `/api/shops/${shopPublicId}/optimize`,
        { stops },
    );

    return data;
}

/** A readable message for a failed map request (including "no route found"). */
export function mapErrorMessage(error: unknown): string {
    if (axios.isAxiosError(error)) {
        return (
            error.response?.data?.message ??
            'The map service is unavailable right now.'
        );
    }

    return 'Something went wrong with the map.';
}
