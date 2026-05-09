import { Head } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { Settings } from 'lucide-react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import EventConfig from '@/components/event-config';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { show as eventShow } from '@/routes/events';
import { create as eventsCreate } from '@/routes/events';
import type { Event } from '@/types/event';

type Props = {
    activeEvents: Event[];
    archivedEvents: Event[];
};

export default function EventsIndex({ activeEvents, archivedEvents }: Props) {
    return (
        <>
            <Head title="Events" />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-medium">Events</h1>
                    <Link href={eventsCreate()}>
                        <Button
                            size="icon"
                            variant="ghost"
                            className="cursor-pointer"
                        >
                            <Plus className="size-5" />
                        </Button>
                    </Link>
                </div>

                <Tabs defaultValue="active">
                    <TabsList className="w-full md:mx-auto md:h-10 md:w-auto md:px-1">
                        <TabsTrigger value="active" className="cursor-pointer">
                            Active ({activeEvents.length})
                        </TabsTrigger>
                        <TabsTrigger
                            value="archived"
                            className="cursor-pointer"
                        >
                            Archived ({archivedEvents.length})
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="active" className="mt-4">
                        <div className="grid gap-4">
                            {activeEvents.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    No active events.
                                </p>
                            ) : (
                                activeEvents.map((event) => (
                                    <EventCard key={event.id} event={event} />
                                ))
                            )}
                        </div>
                    </TabsContent>

                    <TabsContent value="archived" className="mt-4">
                        <div className="grid gap-4">
                            {archivedEvents.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    No archived events.
                                </p>
                            ) : (
                                archivedEvents.map((event) => (
                                    <EventCard
                                        key={event.id}
                                        event={event}
                                        archived
                                    />
                                ))
                            )}
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </>
    );
}

function EventCard({
    event,
    archived = false,
}: {
    event: Event;
    archived?: boolean;
}) {
    const [sheetOpen, setSheetOpen] = useState(false);
    const [justClosed, setJustClosed] = useState(false);

    const handleSheetOpenChange = (open: boolean) => {
        setSheetOpen(open);

        if (!open) {
            setJustClosed(true);
            setTimeout(() => setJustClosed(false), 100);
        }
    };

    return (
        <Card
            className={`cursor-pointer ${archived ? 'opacity-60' : ''}`}
            onClick={() => {
                if (sheetOpen || justClosed) {
                    return;
                }

                router.visit(eventShow({ event: event.id }));
            }}
        >
            <CardHeader className="pb-2">
                <div className="flex items-center justify-between">
                    <CardTitle className="text-lg font-medium">
                        {event.name}
                    </CardTitle>
                    <div className="flex items-center gap-2">
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
                        <Sheet
                            open={sheetOpen}
                            onOpenChange={handleSheetOpenChange}
                        >
                            <SheetTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="size-7 cursor-pointer"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        setSheetOpen(true);
                                    }}
                                >
                                    <Settings className="size-3.5" />
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="right">
                                <SheetHeader>
                                    <SheetTitle>{event.name}</SheetTitle>
                                    <SheetDescription>
                                        Event configuration details
                                    </SheetDescription>
                                </SheetHeader>
                                <EventConfig event={event} />
                            </SheetContent>
                        </Sheet>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <p className="text-sm text-muted-foreground capitalize">
                    {event.type.replace('_', ' ')}
                </p>
            </CardContent>
        </Card>
    );
}
