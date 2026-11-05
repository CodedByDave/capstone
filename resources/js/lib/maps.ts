import axios from 'axios';

export type LatLng = { lat: number; lng: number };

export interface PlaceResult {
    label: string;
    name: string;
    lat: number;
    lng: number;
}

/** Address search through the Laravel Mapbox proxy (Philippines only, cached server-side). */
export async function searchPlaces(
    query: string,
    near?: LatLng | null,
): Promise<PlaceResult[]> {
    const { data } = await axios.get<{ results: PlaceResult[] }>(
        '/api/geocode',
        { params: { q: query, lat: near?.lat, lng: near?.lng } },
    );

    return data.results;
}

export interface PinDetails {
    address: string | null;
    /** City or municipality. */
    place: string | null;
    locality: string | null;
    neighborhood: string | null;
    street: string | null;
}

/** The address at a pinned location, split into the parts forms need. */
export async function reverseGeocodeDetails(
    lat: number,
    lng: number,
): Promise<PinDetails> {
    const { data } = await axios.get<PinDetails>('/api/reverse-geocode', {
        params: { lat, lng },
    });

    return data;
}

/** The address at a pinned location, or null when none is known. */
export async function reverseGeocode(
    lat: number,
    lng: number,
): Promise<string | null> {
    return (await reverseGeocodeDetails(lat, lng)).address;
}

export function formatPeso(value: number): string {
    return `₱${value.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}
