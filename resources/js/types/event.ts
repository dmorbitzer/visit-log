export type Event = {
    id: number;
    name: string;
    type: 'recurring' | 'date_range' | 'custom_days';
    status: 'active' | 'archived';
    recurrence_weekdays: number[] | null;
    start_date: string | null;
    end_date: string | null;
    tracking_start: string;
    tracking_end: string;
    slot_interval_minutes: number;
    created_at: string;
    updated_at: string;
};
