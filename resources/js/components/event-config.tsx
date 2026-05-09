import type { Event } from '@/types/event';

export default function EventConfig({ event }: { event: Event }) {
    return (
        <div className="flex flex-col gap-3 px-4 py-4">
            <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Type</span>
                <span className="capitalize">
                    {event.type.replace('_', ' ')}
                </span>
            </div>
            <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Tracking hours</span>
                <span>
                    {event.tracking_start} – {event.tracking_end}
                </span>
            </div>
            <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Slot interval</span>
                <span>{event.slot_interval_minutes} min</span>
            </div>
            {event.start_date && (
                <div className="flex justify-between text-sm">
                    <span className="text-muted-foreground">Start date</span>
                    <span>
                        {new Date(event.start_date).toLocaleDateString('de-DE')}
                    </span>
                </div>
            )}
            {event.end_date && (
                <div className="flex justify-between text-sm">
                    <span className="text-muted-foreground">End date</span>
                    <span>
                        {new Date(event.end_date).toLocaleDateString('de-DE')}
                    </span>
                </div>
            )}
            {event.recurrence_weekdays && (
                <div className="flex justify-between text-sm">
                    <span className="text-muted-foreground">Recurrence</span>
                    <span>
                        {event.recurrence_weekdays
                            .map(
                                (d: number) =>
                                    [
                                        'Sun',
                                        'Mon',
                                        'Tue',
                                        'Wed',
                                        'Thu',
                                        'Fri',
                                        'Sat',
                                    ][d],
                            )
                            .join(', ')}
                    </span>
                </div>
            )}
        </div>
    );
}
