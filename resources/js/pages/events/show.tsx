import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ChevronLeft, ChevronRight, Settings } from 'lucide-react';
import { useState } from 'react';
import EventConfig from '@/components/event-config';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { index as eventsIndex } from '@/routes/events';
import type { User } from '@/types';
import type { Event } from '@/types/event';

type RangeType = 'today' | 'week' | 'month';

type Props = {
    event: Event;
    canManage: boolean;
    allUsers: Pick<User, 'id' | 'name' | 'username'>[];
};

export default function EventsShow({ event, canManage, allUsers }: Props) {
    const [rangeType, setRangeType] = useState<RangeType>('today');
    const [offset, setOffset] = useState(0);

    const rangeLabel = () => {
        const now = new Date();

        if (rangeType === 'today') {
            return null;
        }

        if (rangeType === 'week') {
            const date = new Date(now);
            date.setDate(date.getDate() + offset * 7);
            const startOfWeek = new Date(date);
            startOfWeek.setDate(date.getDate() - date.getDay() + 1);
            const week = Math.ceil(startOfWeek.getDate() / 7);

            return `KW ${week} ${startOfWeek.getFullYear()}`;
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
    };

    const handleRangeTypeChange = (value: RangeType) => {
        setRangeType(value);
        setOffset(0);
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
                            {event.status}
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
                                <SheetTitle>Event Configuration</SheetTitle>
                                <SheetDescription>
                                    Event configuration details
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

                {/* Toolbar */}
                <div className="flex items-center gap-2">
                    <Select
                        value={rangeType}
                        onValueChange={handleRangeTypeChange}
                    >
                        <SelectTrigger className="w-auto cursor-pointer gap-1">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="today">Today</SelectItem>
                            <SelectItem value="week">Week</SelectItem>
                            <SelectItem value="month">Month</SelectItem>
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
                        </div>
                    )}
                </div>

                {/* Tracking Days Placeholder */}
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base font-medium">
                            Tracking
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-sm text-muted-foreground">
                            Tracking view coming soon.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
