import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    CalendarCheck,
    CalendarPlus,
    ChevronLeft,
    ChevronRight,
    Settings,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import DatePicker from '@/components/date-picker';
import EventConfig from '@/components/event-config';
import TrackingDay from '@/components/tracking-day';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useTranslation } from '@/hooks/use-translation';
import axios from '@/lib/axios';
import { isTrackingDay } from '@/lib/is-tracking-day';
import { index as eventsIndex } from '@/routes/events';
import type { User } from '@/types';
import type { Event } from '@/types/event';

type RangeType = 'today' | 'week' | 'month';

type Props = {
    event: Event;
    canManage: boolean;
    allUsers: Pick<User, 'id' | 'name' | 'username'>[];
};

function formatDate(date: Date): string {
    return date.toISOString().slice(0, 10);
}

function getISOWeek(date: Date): { week: number; year: number } {
    const tmp = new Date(date.getTime());
    tmp.setHours(0, 0, 0, 0);
    tmp.setDate(tmp.getDate() + 3 - ((tmp.getDay() + 6) % 7));
    const week1 = new Date(tmp.getFullYear(), 0, 4);
    const week =
        1 +
        Math.round(
            ((tmp.getTime() - week1.getTime()) / 86400000 -
                3 +
                ((week1.getDay() + 6) % 7)) /
                7,
        );

    return { week, year: tmp.getFullYear() };
}

function getMondayOfWeek(date: Date): Date {
    const day = date.getDay();
    const monday = new Date(date);
    monday.setDate(date.getDate() - (day === 0 ? 6 : day - 1));
    monday.setHours(0, 0, 0, 0);

    return monday;
}

function getTrackingDates(rangeType: RangeType, offset: number): string[] {
    const now = new Date();

    if (rangeType === 'today') {
        return [formatDate(now)];
    }

    if (rangeType === 'week') {
        const base = new Date(now);
        base.setDate(base.getDate() + offset * 7);
        const monday = getMondayOfWeek(base);

        return Array.from({ length: 7 }, (_, i) => {
            const d = new Date(monday);
            d.setDate(monday.getDate() + i);

            return formatDate(d);
        });
    }

    if (rangeType === 'month') {
        const year = now.getFullYear();
        const month = now.getMonth() + offset;
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const days: string[] = [];

        for (
            let d = new Date(firstDay);
            d <= lastDay;
            d.setDate(d.getDate() + 1)
        ) {
            days.push(formatDate(new Date(d)));
        }

        return days;
    }

    return [formatDate(now)];
}

export default function EventsShow({ event, canManage, allUsers }: Props) {
    const { t } = useTranslation();
    const [rangeType, setRangeType] = useState<RangeType>('today');
    const [offset, setOffset] = useState(0);

    // custom_days: list of dates managed manually
    const [customDates, setCustomDates] = useState<string[]>([]);
    const [addDateValue, setAddDateValue] = useState('');
    const isCustomDays = event.type === 'custom_days';

    useEffect(() => {
        if (!isCustomDays) {
            return;
        }

        axios
            .get(`/events/${event.id}/days`)
            .then((res) => {
                const dates: string[] = res.data.map((d: { date: string }) =>
                    d.date.slice(0, 10),
                );
                setCustomDates(dates);
            })
            .catch(() => {});
    }, [event.id, isCustomDays]);

    const rangeLabel = () => {
        const now = new Date();

        if (rangeType === 'week') {
            const base = new Date(now);
            base.setDate(base.getDate() + offset * 7);
            const monday = getMondayOfWeek(base);
            const { week, year } = getISOWeek(monday);

            return `KW ${week} ${year}`;
        }

        if (rangeType === 'month') {
            const date = new Date(
                now.getFullYear(),
                now.getMonth() + offset,
                1,
            );

            return date.toLocaleDateString('de-DE', {
                month: 'long',
                year: 'numeric',
            });
        }

        return null;
    };

    const handleRangeTypeChange = (value: RangeType) => {
        setRangeType(value);
        setOffset(0);
    };

    const rangeDates = getTrackingDates(rangeType, offset);
    const dates = isCustomDays
        ? customDates.filter((d) => rangeDates.includes(d))
        : rangeDates.filter((d) => isTrackingDay(event, d));

    const handleAddCustomDate = (date: string) => {
        if (!date || customDates.includes(date)) {
            return;
        }

        setCustomDates((prev) => [...prev, date].sort());
        setAddDateValue('');
    };

    return (
        <>
            <Head title={event.name} />

            <div className="flex flex-col gap-4 p-4 md:p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href={eventsIndex()}
                            className="cursor-pointer text-muted-foreground transition-colors hover:text-foreground"
                        >
                            <ArrowLeft className="size-5" strokeWidth={2.5} />
                        </Link>
                        <h1 className="text-xl font-medium md:text-2xl">
                            {event.name}
                        </h1>
                        <Badge
                            className={
                                event.status === 'active'
                                    ? 'border-primary bg-primary text-white'
                                    : ''
                            }
                            variant={
                                event.status === 'archived'
                                    ? 'secondary'
                                    : 'default'
                            }
                        >
                            {t(event.status)}
                        </Badge>
                    </div>

                    <Sheet>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="cursor-pointer"
                            >
                                <Settings className="size-4" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="right">
                            <SheetHeader>
                                <SheetTitle>
                                    {t('Event Configuration')}
                                </SheetTitle>
                                <SheetDescription>
                                    {t('Event configuration details')}
                                </SheetDescription>
                            </SheetHeader>
                            <EventConfig
                                event={event}
                                canManage={canManage}
                                allUsers={allUsers}
                            />
                        </SheetContent>
                    </Sheet>
                </div>

                {/* custom_days: date picker toolbar */}
                {isCustomDays && event.status === 'active' && (
                    <div className="flex items-center gap-2">
                        <CalendarPlus className="size-4 shrink-0 text-muted-foreground" />
                        <DatePicker
                            value={addDateValue}
                            onChange={handleAddCustomDate}
                            placeholder={t('Add day')}
                            fromDate={
                                event.start_date
                                    ? new Date(event.start_date)
                                    : undefined
                            }
                            toDate={
                                event.end_date
                                    ? new Date(event.end_date)
                                    : undefined
                            }
                        />
                    </div>
                )}

                {/* Range selector */}
                <div className="flex items-center gap-2">
                    <Select
                        value={rangeType}
                        onValueChange={handleRangeTypeChange}
                    >
                        <SelectTrigger className="w-auto cursor-pointer gap-1">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="today">{t('Today')}</SelectItem>
                            <SelectItem value="week">{t('Week')}</SelectItem>
                            <SelectItem value="month">{t('Month')}</SelectItem>
                        </SelectContent>
                    </Select>

                    {rangeType !== 'today' && (
                        <div className="flex items-center gap-1">
                            <Button
                                variant="outline"
                                size="icon"
                                className="size-8 cursor-pointer"
                                onClick={() => setOffset(offset - 1)}
                            >
                                <ChevronLeft className="size-4" />
                            </Button>
                            <span className="min-w-28 text-center text-sm">
                                {rangeLabel()}
                            </span>
                            <Button
                                variant="outline"
                                size="icon"
                                className="size-8 cursor-pointer"
                                onClick={() => setOffset(offset + 1)}
                                disabled={offset >= 0}
                            >
                                <ChevronRight className="size-4" />
                            </Button>
                            {offset !== 0 && (
                                <Button
                                    variant="outline"
                                    size="icon"
                                    className="size-8 cursor-pointer"
                                    onClick={() => setOffset(0)}
                                    title={t('Today')}
                                >
                                    <CalendarCheck className="size-4" />
                                </Button>
                            )}
                        </div>
                    )}
                </div>

                {/* Tracking */}
                {dates.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        {isCustomDays
                            ? t('No tracking days added yet.')
                            : rangeType === 'today'
                              ? t('No tracking scheduled for today.')
                              : t('No tracking days in this range.')}
                    </p>
                ) : (
                    <div className="flex flex-col gap-6">
                        {dates.map((date) => (
                            <TrackingDay
                                key={date}
                                event={event}
                                date={date}
                                canTrack={event.status === 'active'}
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
