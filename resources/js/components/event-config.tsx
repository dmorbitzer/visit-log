import { router } from '@inertiajs/react';
import { Pencil, Archive, Trash2 } from 'lucide-react';
import { useState } from 'react';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    archive as archiveRoute,
    destroy as destroyRoute,
    edit as editRoute,
} from '@/routes/events';
import type { Event } from '@/types/event';

type Props = {
    event: Event;
    canManage?: boolean;
};

export default function EventConfig({ event, canManage = false }: Props) {
    const [archiveOpen, setArchiveOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);

    const handleArchive = () => {
        router.patch(archiveRoute(event.id));
    };

    const handleDelete = () => {
        router.delete(destroyRoute(event.id));
    };

    return (
        <div className="flex h-full flex-col px-4 py-4">
            <div className="flex flex-col gap-3">
                <div className="flex justify-between text-sm">
                    <span className="text-muted-foreground">Type</span>
                    <span className="capitalize">
                        {event.type.replace('_', ' ')}
                    </span>
                </div>
                <div className="flex justify-between text-sm">
                    <span className="text-muted-foreground">
                        Tracking hours
                    </span>
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
                        <span className="text-muted-foreground">
                            Start date
                        </span>
                        <span>
                            {new Date(event.start_date).toLocaleDateString(
                                'de-DE',
                            )}
                        </span>
                    </div>
                )}
                {event.end_date && (
                    <div className="flex justify-between text-sm">
                        <span className="text-muted-foreground">End date</span>
                        <span>
                            {new Date(event.end_date).toLocaleDateString(
                                'de-DE',
                            )}
                        </span>
                    </div>
                )}
                {event.recurrence_weekdays && (
                    <div className="flex justify-between text-sm">
                        <span className="text-muted-foreground">
                            Recurrence
                        </span>
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

            {canManage && (
                <div className="mt-auto flex flex-col gap-2 border-t pt-4">
                    <Button
                        variant="outline"
                        className="w-full justify-start gap-2"
                        onClick={() => router.visit(editRoute(event.id))}
                    >
                        <Pencil className="size-4" />
                        Edit event
                    </Button>

                    {event.status === 'active' && (
                        <AlertDialog
                            open={archiveOpen}
                            onOpenChange={setArchiveOpen}
                        >
                            <AlertDialogTrigger asChild>
                                <Button
                                    variant="outline"
                                    className="w-full justify-start gap-2"
                                >
                                    <Archive className="size-4" />
                                    Archive event
                                </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle>
                                        Archive event?
                                    </AlertDialogTitle>
                                    <AlertDialogDescription>
                                        "{event.name}" wird archiviert und ist
                                        für Tracker nicht mehr sichtbar.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel>
                                        Cancel
                                    </AlertDialogCancel>
                                    <AlertDialogAction onClick={handleArchive}>
                                        Archive
                                    </AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
                    )}

                    <AlertDialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                        <AlertDialogTrigger asChild>
                            <Button
                                variant="ghost"
                                className="w-full justify-start gap-2 text-destructive hover:text-destructive"
                            >
                                <Trash2 className="size-4" />
                                Delete event
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    Event löschen?
                                </AlertDialogTitle>
                                <AlertDialogDescription>
                                    "{event.name}" wird permanent gelöscht.
                                    Diese Aktion kann nicht rückgängig gemacht
                                    werden.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction
                                    className="bg-destructive text-white hover:bg-destructive/90"
                                    onClick={handleDelete}
                                >
                                    Delete
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            )}
        </div>
    );
}
