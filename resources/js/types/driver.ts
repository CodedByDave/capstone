export interface DriverInfo {
    name: string;
    phone: string | null;
    vehicle_type: string | null;
    is_online: boolean;
    shop_name: string | null;
}

export interface JobStop {
    kind: 'customer' | 'shop';
    label: string | null;
    address: string | null;
    lat: number | null;
    lng: number | null;
}

export interface DriverJob {
    public_id: string;
    type: 'pickup' | 'delivery';
    status: 'assigned' | 'picked_up' | 'delivered' | 'failed';
    order_number: string | null;
    service: string | null;
    customer_name: string;
    customer_phone: string | null;
    address: string | null;
    notes: string | null;
    distance_km: number | null;
    assigned_at: string | null;
    finished_at: string | null;
    target: JobStop;
    next_status: 'picked_up' | 'delivered' | null;
    action_label: string | null;
    checklist: string[];
}

export interface EarningsSummary {
    mode: 'none' | 'delivery_fee' | 'daily_rate';
    enabled: boolean;
    amount: number | null;
}
