import { useEffect, useState } from 'react';
import axios from '@/lib/axios';
import { generateSlots } from '@/lib/generate-slots';
import type { Event } from '@/types/event';
import type { DisplaySlot, TrackingDay } from '@/types/tracking';

type UseTrackingDayResult = {
    slots: DisplaySlot[];
    dayId: number | null;
    notes: string | null;
    loading: boolean;
    error: boolean;
};

export function useTrackingDay(
    event: Event,
    date: string,
): UseTrackingDayResult {
    const [day, setDay] = useState<TrackingDay | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(false);

    useEffect(() => {
        setLoading(true); // eslint-disable-line react-hooks/set-state-in-effect
        setError(false);

        axios
            .get(`/events/${event.id}/days/${date}`)
            .then((res) => setDay(res.data))
            .catch(() => setError(true))
            .finally(() => setLoading(false));
    }, [event.id, date]);

    const virtualSlots = generateSlots(
        event.tracking_start,
        event.tracking_end,
        event.slot_interval_minutes,
    );

    const slots: DisplaySlot[] = virtualSlots.map((virtual) => {
        const real = day?.time_slots.find(
            (s) => s.slot_start === virtual.slot_start,
        );

        return real ?? virtual;
    });

    return {
        slots,
        dayId: day?.id ?? null,
        notes: day?.notes ?? null,
        loading,
        error,
    };
}
