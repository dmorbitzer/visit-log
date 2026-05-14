import { useCallback, useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import axios from '@/lib/axios';
import { getEcho } from '@/lib/echo';
import type { DisplaySlot } from '@/types/tracking';

type TrackAction = 'increment' | 'decrement';

type UseTrackingSlotResult = {
    count: number;
    track: (action: TrackAction) => void;
    isPending: boolean;
};

export function useTrackingSlot(
    eventId: number,
    date: string,
    initialSlot: DisplaySlot,
): UseTrackingSlotResult {
    const [count, setCount] = useState(initialSlot.visitor_count);
    const [slotId, setSlotId] = useState<number | null>(initialSlot.id);
    const [isPending, setIsPending] = useState(false);

    const pendingUntilRef = useRef<number>(0);
    const pendingCountRef = useRef(initialSlot.visitor_count);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const sendToServer = useCallback(
        (targetCount: number) => {
            setIsPending(true);
            pendingUntilRef.current = Date.now() + 1000;

            axios
                .patch(`/events/${eventId}/days/${date}/track`, {
                    slot_start: initialSlot.slot_start,
                    slot_end: initialSlot.slot_end,
                    action: 'set',
                    value: targetCount,
                })
                .then((res) => {
                    setSlotId(res.data.id);
                    setCount(res.data.visitor_count);
                    pendingCountRef.current = res.data.visitor_count;
                })
                .catch(() => {
                    setCount(pendingCountRef.current);
                    toast.error('Tracking fehlgeschlagen');
                })
                .finally(() => setIsPending(false));
        },
        [eventId, date, initialSlot.slot_start, initialSlot.slot_end],
    );

    const track = useCallback(
        (action: TrackAction) => {
            setCount((prev) => {
                const next =
                    action === 'increment' ? prev + 1 : Math.max(0, prev - 1);

                if (debounceRef.current) {
                    clearTimeout(debounceRef.current);
                }

                debounceRef.current = setTimeout(() => sendToServer(next), 500);

                return next;
            });
        },
        [sendToServer],
    );

    // Reverb live sync — re-subscribes when slot gets an id after first track
    useEffect(() => {
        if (!slotId) {
            return;
        }

        const echo = getEcho();

        if (!echo) {
            return;
        }

        const channel = echo
            .channel(`slot.${slotId}`)
            .listen('.SlotUpdated', (event: { visitor_count: number }) => {
                if (Date.now() < pendingUntilRef.current) {
                    return;
                }

                setCount(event.visitor_count);
                pendingCountRef.current = event.visitor_count;
            });

        return () => {
            channel.stopListening('.SlotUpdated');
            echo.leave(`slot.${slotId}`);
        };
    }, [slotId]);

    return { count, track, isPending };
}
