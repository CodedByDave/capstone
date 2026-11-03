import { reactive } from 'vue';

export type ScanState = 'idle' | 'scanning' | 'found' | 'not_found' | 'expired';

export interface KycScanResult {
    tin: string | null;
    registered_name: string | null;
    registered_address: string | null;
    tax_type: 'vat' | 'non_vat' | null;
    expiry_date: string | null;
}

/**
 * Sends an uploaded KYC document to the OCR endpoint and reports what it read.
 * The caller decides where the values go, so the user can always review and
 * correct them.
 */
export function useKycExpiryScan() {
    const scanState = reactive<Record<string, ScanState>>({});
    const scanResults = reactive<Record<string, KycScanResult | null>>({});

    async function scanDocument(
        key: string,
        file: File,
    ): Promise<KycScanResult | null> {
        scanState[key] = 'scanning';
        scanResults[key] = null;

        try {
            const body = new FormData();
            body.append('document', file);

            const response = await fetch('/kyc/scan-expiry', {
                method: 'POST',
                body,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') ?? '',
                },
            });

            if (!response.ok) {
                scanState[key] = 'not_found';
                return null;
            }

            const data: KycScanResult & {
                available: boolean;
                expired: boolean;
            } = await response.json();

            if (!data.available) {
                scanState[key] = 'idle';
                return null;
            }

            scanState[key] = !data.expiry_date
                ? 'not_found'
                : data.expired
                  ? 'expired'
                  : 'found';
            scanResults[key] = data;

            return data;
        } catch {
            scanState[key] = 'not_found';
            return null;
        }
    }

    async function scanExpiry(key: string, file: File): Promise<string | null> {
        return (await scanDocument(key, file))?.expiry_date ?? null;
    }

    function resetScan(key: string) {
        scanState[key] = 'idle';
        scanResults[key] = null;
    }

    return { scanState, scanResults, scanDocument, scanExpiry, resetScan };
}
