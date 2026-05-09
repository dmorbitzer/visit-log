import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
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
                </div>

                <Tabs defaultValue="active">
                    <TabsList className="w-full md:mx-auto md:h-10 md:w-auto md:px-1">
                        <TabsTrigger value="active">
                            Active ({activeEvents.length})
                        </TabsTrigger>
                        <TabsTrigger value="archived">
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
    return (
        <Card className={archived ? 'opacity-60' : ''}>
            <CardHeader className="pb-2">
                <div className="flex items-center justify-between">
                    <CardTitle className="text-lg font-medium">
                        {event.name}
                    </CardTitle>
                    <Badge variant={archived ? 'secondary' : 'default'}>
                        {archived ? 'Archived' : 'Active'}
                    </Badge>
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
