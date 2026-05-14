import type { VirtualSlot } from '@/types/tracking';

export function generateSlots(
    trackingStart: string,
    trackingEnd: string,
    slotIntervalMinutes: number,
): VirtualSlot[] {
    const slots: VirtualSlot[] = [];

    const [startH, startM] = trackingStart.split(':').map(Number);
    const [endH, endM] = trackingEnd.split(':').map(Number);

    let currentMinutes = startH * 60 + startM;
    const endMinutes = endH * 60 + endM;

    while (currentMinutes < endMinutes) {
        const nextMinutes = currentMinutes + slotIntervalMinutes;

        slots.push({
            id: null,
            slot_start: formatTime(currentMinutes),
            slot_end: formatTime(nextMinutes),
            visitor_count: 0,
        });

        currentMinutes = nextMinutes;
    }

    return slots;
}

function formatTime(totalMinutes: number): string {
    const h = Math.floor(totalMinutes / 60)
        .toString()
        .padStart(2, '0');
    const m = (totalMinutes % 60).toString().padStart(2, '0');

    return `${h}:${m}`;
}
