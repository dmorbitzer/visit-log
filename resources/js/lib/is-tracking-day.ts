import type { Event } from '@/types/event';

export function isTrackingDay(event: Event, dateStr: string): boolean {
    const date = new Date(dateStr + 'T00:00:00');

    if (event.type === 'recurring') {
        if (!event.recurrence_weekdays?.length) {
            return false;
        }

        const dayOfWeek = date.getDay();

        if (!event.recurrence_weekdays.includes(dayOfWeek)) {
            return false;
        }

        if (
            event.start_date &&
            date < new Date(event.start_date + 'T00:00:00')
        ) {
            return false;
        }

        if (event.end_date && date > new Date(event.end_date + 'T00:00:00')) {
            return false;
        }

        return true;
    }

    if (event.type === 'date_range') {
        if (!event.start_date || !event.end_date) {
            return false;
        }

        const start = new Date(event.start_date + 'T00:00:00');
        const end = new Date(event.end_date + 'T00:00:00');

        return date >= start && date <= end;
    }

    // custom_days — only explicitly added days (managed separately)
    return false;
}
