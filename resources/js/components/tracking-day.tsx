import { ChevronDown, StickyNote } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { toast } from 'sonner';
import TimeSlotCard from '@/components/time-slot-card';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Textarea } from '@/components/ui/textarea';
import { useTrackingDay } from '@/hooks/use-tracking-day';
import { useTranslation } from '@/hooks/use-translation';
import axios from '@/lib/axios';
import { getEcho } from '@/lib/echo';
import type { Event } from '@/types/event';

type Props = {
    event: Event;
    date: string;
    canTrack?: boolean;
};

export default function TrackingDay({ event, date, canTrack = true }: Props) {
    const { t } = useTranslation();
    const {
        slots,
        dayId,
        notes: initialNotes,
        loading,
        error,
    } = useTrackingDay(event, date);
    const [notesOpen, setNotesOpen] = useState(false);
    const [notesValue, setNotesValue] = useState('');
    const [saveState, setSaveState] = useState<'idle' | 'saving'>('idle');
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const isPendingRef = useRef(false);

    // Sync initial notes once loaded
    useEffect(() => {
        setNotesValue(initialNotes ?? ''); // eslint-disable-line react-hooks/set-state-in-effect
    }, [initialNotes]);

    const handleNotesChange = (value: string) => {
        setNotesValue(value);
        setSaveState('idle');
        isPendingRef.current = true;

        if (debounceRef.current) {
            clearTimeout(debounceRef.current);
        }

        debounceRef.current = setTimeout(async () => {
            if (!dayId) {
                return;
            }

            setSaveState('saving');

            try {
                await axios.patch(`/events/${event.id}/days/${date}`, {
                    notes: value,
                });
                setSaveState('idle');
                toast.success(t('Notes saved'));
            } catch {
                setSaveState('idle');
            } finally {
                isPendingRef.current = false;
            }
        }, 500);
    };

    // Reverb live sync für Notizen
    useEffect(() => {
        if (!dayId) {
            return;
        }

        const echo = getEcho();

        if (!echo) {
            return;
        }

        const channel = echo
            .channel(`day.${dayId}`)
            .listen('.DayNotesUpdated', (e: { notes: string | null }) => {
                if (isPendingRef.current) {
                    return;
                }

                setNotesValue(e.notes ?? '');
            });

        return () => {
            channel.stopListening('.DayNotesUpdated');
            echo.leave(`day.${dayId}`);
        };
    }, [dayId]);

    const label = new Date(date + 'T00:00:00').toLocaleDateString('de-DE', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });

    if (loading) {
        return (
            <div className="flex flex-col gap-2">
                {Array.from({ length: 4 }).map((_, i) => (
                    <div
                        key={i}
                        className="h-14 animate-pulse rounded-md border bg-muted"
                    />
                ))}
            </div>
        );
    }

    if (error) {
        return (
            <p className="text-sm text-destructive">
                {t('Tracking view coming soon.')}
            </p>
        );
    }

    return (
        <div className="flex flex-col gap-3">
            <Collapsible open={notesOpen} onOpenChange={setNotesOpen}>
                <div className="flex items-center justify-between">
                    <h2 className="text-sm font-medium">{label}</h2>
                    <CollapsibleTrigger asChild>
                        <Button
                            variant="ghost"
                            size="sm"
                            className="gap-1 text-xs text-muted-foreground"
                        >
                            <StickyNote className="size-3.5" />
                            {t('Notes')}
                            <ChevronDown
                                className={`size-3.5 transition-transform ${notesOpen ? 'rotate-180' : ''}`}
                            />
                        </Button>
                    </CollapsibleTrigger>
                </div>
                <CollapsibleContent className="mt-2 md:flex md:justify-end">
                    <div className="w-full md:max-w-sm">
                        <Textarea
                            value={notesValue}
                            onChange={(e) => handleNotesChange(e.target.value)}
                            placeholder={t('Add notes for this day…')}
                            className="min-h-20 w-full resize-none text-sm"
                            rows={3}
                            disabled={
                                !canTrack || (!dayId && notesValue === '')
                            }
                        />
                        {saveState === 'saving' && (
                            <p className="mt-1 text-right text-xs text-muted-foreground">
                                {t('Saving…')}
                            </p>
                        )}
                    </div>
                </CollapsibleContent>
            </Collapsible>

            <div className="flex flex-col gap-2">
                {slots.map((slot) => (
                    <TimeSlotCard
                        key={slot.slot_start}
                        eventId={event.id}
                        date={date}
                        slot={slot}
                        canTrack={canTrack}
                    />
                ))}
            </div>
        </div>
    );
}
