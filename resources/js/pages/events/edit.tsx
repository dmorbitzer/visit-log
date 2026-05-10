import EventForm from '@/components/event-form';
import type { Event } from '@/types/event';

type Props = {
    event: Event;
};

export default function EventsEdit({ event }: Props) {
    return <EventForm event={event} />;
}
