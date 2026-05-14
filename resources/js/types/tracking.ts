export type TimeSlot = {
    id: number;
    slot_start: string;
    slot_end: string;
    visitor_count: number;
};

export type VirtualSlot = {
    id: null;
    slot_start: string;
    slot_end: string;
    visitor_count: 0;
};

export type DisplaySlot = TimeSlot | VirtualSlot;

export type TrackingDay = {
    id: number;
    date: string;
    notes: string | null;
    time_slots: TimeSlot[];
};
