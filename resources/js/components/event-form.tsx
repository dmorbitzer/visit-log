import { useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import DatePicker from '@/components/date-picker';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index as eventsIndex } from '@/routes/events';
import type { Event } from '@/types/event';

type Props = {
    event?: Event;
};

const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

export default function EventForm({ event }: Props) {
    const isEdit = !!event;

    const { data, setData, post, patch, processing, errors } = useForm({
        name: event?.name ?? '',
        type: event?.type ?? 'recurring',
        status: event?.status ?? 'active',
        recurrence_weekdays: event?.recurrence_weekdays ?? [0],
        start_date: event?.start_date ?? '',
        end_date: event?.end_date ?? '',
        tracking_start: event?.tracking_start ?? '12:00',
        tracking_end: event?.tracking_end ?? '16:00',
        slot_interval_minutes: event?.slot_interval_minutes ?? 30,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        if (isEdit) {
            patch(`/events/${event.id}`);
        } else {
            post('/events');
        }
    };

    const toggleWeekday = (day: number) => {
        const current = data.recurrence_weekdays ?? [];

        if (current.includes(day)) {
            setData(
                'recurrence_weekdays',
                current.filter((d) => d !== day),
            );
        } else {
            setData('recurrence_weekdays', [...current, day].sort());
        }
    };

    return (
        <>
            <Head title={isEdit ? `Edit ${event.name}` : 'New Event'} />

            <div className="flex flex-col gap-6 p-4 md:p-6">
                {/* Header */}
                <div className="flex items-center gap-3">
                    <Link
                        href={eventsIndex()}
                        className="text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft className="size-5" strokeWidth={2.5} />
                    </Link>
                    <h1 className="text-2xl font-medium">
                        {isEdit ? `Edit ${event.name}` : 'New Event'}
                    </h1>
                </div>

                <form onSubmit={submit} className="flex flex-col gap-4">
                    {/* Name */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base font-medium">
                                General
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    placeholder="e.g. Sunday Service"
                                />
                                {errors.name && (
                                    <p className="text-sm text-red-500">
                                        {errors.name}
                                    </p>
                                )}
                            </div>

                            <div className="flex flex-col gap-2">
                                <Label htmlFor="type">Type</Label>
                                <Select
                                    value={data.type}
                                    onValueChange={(v) =>
                                        setData('type', v as Event['type'])
                                    }
                                >
                                    <SelectTrigger id="type">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="recurring">
                                            Recurring
                                        </SelectItem>
                                        <SelectItem value="date_range">
                                            Date Range
                                        </SelectItem>
                                        <SelectItem value="custom_days">
                                            Custom Days
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.type && (
                                    <p className="text-sm text-red-500">
                                        {errors.type}
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Type-specific fields */}
                    {data.type === 'recurring' && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base font-medium">
                                    Recurrence
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4">
                                <div className="flex flex-col gap-2">
                                    <Label>Weekdays</Label>
                                    <div className="flex flex-wrap gap-2">
                                        {WEEKDAYS.map((day, index) => (
                                            <button
                                                key={day}
                                                type="button"
                                                onClick={() =>
                                                    toggleWeekday(index)
                                                }
                                                className={`cursor-pointer rounded-md border px-3 py-1.5 text-sm transition-colors ${
                                                    data.recurrence_weekdays?.includes(
                                                        index,
                                                    )
                                                        ? 'border-blue-500 bg-blue-500 text-white'
                                                        : 'border-border text-muted-foreground hover:border-foreground'
                                                }`}
                                            >
                                                {day}
                                            </button>
                                        ))}
                                    </div>
                                    {errors.recurrence_weekdays && (
                                        <p className="text-sm text-red-500">
                                            {errors.recurrence_weekdays}
                                        </p>
                                    )}
                                </div>

                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="start_date">
                                        Start date (optional)
                                    </Label>
                                    <DatePicker
                                        value={data.start_date ?? ''}
                                        onChange={(v) =>
                                            setData('start_date', v)
                                        }
                                    />
                                </div>

                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="end_date">
                                        End date (optional)
                                    </Label>
                                    <DatePicker
                                        value={data.end_date ?? ''}
                                        onChange={(v) => setData('end_date', v)}
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {data.type === 'date_range' && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base font-medium">
                                    Date Range
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4">
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="start_date">
                                        Start date
                                    </Label>
                                    <DatePicker
                                        value={data.start_date ?? ''}
                                        onChange={(v) =>
                                            setData('start_date', v)
                                        }
                                    />
                                    {errors.start_date && (
                                        <p className="text-sm text-red-500">
                                            {errors.start_date}
                                        </p>
                                    )}
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="end_date">End date</Label>
                                    <DatePicker
                                        value={data.end_date ?? ''}
                                        onChange={(v) => setData('end_date', v)}
                                    />
                                    {errors.end_date && (
                                        <p className="text-sm text-red-500">
                                            {errors.end_date}
                                        </p>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Tracking Config */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base font-medium">
                                Tracking
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="tracking_start">
                                        Start time
                                    </Label>
                                    <Input
                                        id="tracking_start"
                                        type="time"
                                        value={data.tracking_start}
                                        onChange={(e) =>
                                            setData(
                                                'tracking_start',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    {errors.tracking_start && (
                                        <p className="text-sm text-red-500">
                                            {errors.tracking_start}
                                        </p>
                                    )}
                                </div>
                                <div className="flex flex-col gap-2">
                                    <Label htmlFor="tracking_end">
                                        End time
                                    </Label>
                                    <Input
                                        id="tracking_end"
                                        type="time"
                                        value={data.tracking_end}
                                        onChange={(e) =>
                                            setData(
                                                'tracking_end',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    {errors.tracking_end && (
                                        <p className="text-sm text-red-500">
                                            {errors.tracking_end}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="flex flex-col gap-2">
                                <Label htmlFor="slot_interval_minutes">
                                    Slot interval (minutes)
                                </Label>
                                <Select
                                    value={String(data.slot_interval_minutes)}
                                    onValueChange={(v) =>
                                        setData(
                                            'slot_interval_minutes',
                                            Number(v),
                                        )
                                    }
                                >
                                    <SelectTrigger id="slot_interval_minutes">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="15">
                                            15 min
                                        </SelectItem>
                                        <SelectItem value="30">
                                            30 min
                                        </SelectItem>
                                        <SelectItem value="60">
                                            60 min
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.slot_interval_minutes && (
                                    <p className="text-sm text-red-500">
                                        {errors.slot_interval_minutes}
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Submit */}
                    <div className="flex justify-end gap-3">
                        <Link href={eventsIndex()}>
                            <Button
                                type="button"
                                className="cursor-pointer"
                                variant="outline"
                            >
                                Cancel
                            </Button>
                        </Link>
                        <Button
                            type="submit"
                            disabled={processing}
                            className="cursor-pointer"
                        >
                            {isEdit ? 'Save changes' : 'Create event'}
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}
