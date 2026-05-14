import { Minus, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTrackingSlot } from '@/hooks/use-tracking-slot';
import type { DisplaySlot } from '@/types/tracking';

type Props = {
    eventId: number;
    date: string;
    slot: DisplaySlot;
    canTrack?: boolean;
};

function isCurrentSlot(slotStart: string, slotEnd: string): boolean {
    const now = new Date();
    const pad = (n: number) => n.toString().padStart(2, '0');
    const currentTime = `${pad(now.getHours())}:${pad(now.getMinutes())}`;

    return currentTime >= slotStart && currentTime < slotEnd;
}

export default function TimeSlotCard({
    eventId,
    date,
    slot,
    canTrack = true,
}: Props) {
    const { count, track, isPending } = useTrackingSlot(eventId, date, slot);
    const active = isCurrentSlot(slot.slot_start, slot.slot_end);

    const decrementBtn = (
        <Button
            variant="outline"
            size="icon"
            className="size-8 shrink-0"
            onClick={() => track('decrement')}
            disabled={!canTrack || count === 0 || isPending}
            aria-label="Decrement"
        >
            <Minus className="size-4" />
        </Button>
    );

    const incrementBtn = (
        <Button
            size="icon"
            className={`size-8 shrink-0 ${active ? 'bg-primary text-primary-foreground hover:bg-primary/90' : ''}`}
            onClick={() => track('increment')}
            disabled={!canTrack || isPending}
            aria-label="Increment"
        >
            <Plus className="size-4" />
        </Button>
    );

    const countDisplay = (
        <span
            className={`text-2xl font-medium tabular-nums ${isPending ? 'opacity-60' : ''}`}
        >
            {count}
        </span>
    );

    return (
        <div
            className={`rounded-md border px-4 py-3 transition-colors ${
                active ? 'border-primary bg-primary/5' : 'border-border'
            }`}
        >
            {/* Mobile: time top, then [−] count [+] row */}
            <div className="flex flex-col gap-2 sm:hidden">
                <span className="text-xs text-muted-foreground">
                    {slot.slot_start} – {slot.slot_end}
                </span>
                <div className="flex items-center justify-between gap-2">
                    {decrementBtn}
                    {countDisplay}
                    {incrementBtn}
                </div>
            </div>

            {/* Desktop: [time] [count] [− +] */}
            <div className="hidden items-center sm:flex">
                <span className="w-28 text-sm text-muted-foreground">
                    {slot.slot_start} – {slot.slot_end}
                </span>
                <span className="flex-1 text-center">{countDisplay}</span>
                <div className="flex items-center gap-1">
                    {decrementBtn}
                    {incrementBtn}
                </div>
            </div>
        </div>
    );
}
