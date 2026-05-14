import { router } from '@inertiajs/react';
import { Archive, Pencil, Trash2, UserMinus, UserPlus } from 'lucide-react';
import { useEffect, useState } from 'react';
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    archive as archiveRoute,
    destroy as destroyRoute,
    edit as editRoute,
} from '@/routes/events';
import {
    destroy as removeUserRoute,
    store as assignUserRoute,
    update as updatePermissionRoute,
} from '@/routes/events/users';
import type { User } from '@/types';
import type { Event } from '@/types/event';

type AssignedUser = {
    id: number;
    name: string;
    username: string;
    permission: 'viewer' | 'tracker';
};

type Props = {
    event: Event;
    canManage?: boolean;
    allUsers?: Pick<User, 'id' | 'name' | 'username'>[];
};

export default function EventConfig({
    event,
    canManage = false,
    allUsers = [],
}: Props) {
    const [archiveOpen, setArchiveOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [assignedUsers, setAssignedUsers] = useState<AssignedUser[]>([]);
    const [addUserId, setAddUserId] = useState('');
    const [addPermission, setAddPermission] = useState<'viewer' | 'tracker'>(
        'tracker',
    );

    useEffect(() => {
        if (!canManage) {
            return;
        }

        fetch(`/events/${event.id}/users`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((r) => r.json())
            .then(setAssignedUsers)
            .catch(() => {});
    }, [event.id, canManage]);

    const unassignedUsers = allUsers.filter(
        (u) => !assignedUsers.some((a) => a.id === u.id),
    );

    const handleAssign = () => {
        if (!addUserId) {
            return;
        }

        router.post(
            assignUserRoute.url(event.id),
            { user_id: Number(addUserId), permission: addPermission },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    const user = allUsers.find(
                        (u) => u.id === Number(addUserId),
                    );

                    if (user) {
                        setAssignedUsers((prev) => [
                            ...prev,
                            { ...user, permission: addPermission },
                        ]);
                    }

                    setAddUserId('');
                },
            },
        );
    };

    const handlePermissionChange = (
        userId: number,
        permission: 'viewer' | 'tracker',
    ) => {
        router.patch(
            updatePermissionRoute.url({ event: event.id, user: userId }),
            { permission },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () =>
                    setAssignedUsers((prev) =>
                        prev.map((u) =>
                            u.id === userId ? { ...u, permission } : u,
                        ),
                    ),
            },
        );
    };

    const handleRemoveUser = (userId: number) => {
        router.delete(removeUserRoute.url({ event: event.id, user: userId }), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () =>
                setAssignedUsers((prev) => prev.filter((u) => u.id !== userId)),
        });
    };

    const handleArchive = () => {
        router.patch(archiveRoute(event.id));
    };

    const handleDelete = () => {
        router.delete(destroyRoute(event.id));
    };

    return (
        <div className="flex h-full flex-col px-4 py-4">
            {/* Config rows */}
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

            {/* Users section */}
            {canManage && (
                <div className="mt-6 flex flex-col gap-3">
                    <p className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                        Users
                    </p>

                    {assignedUsers.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No users assigned.
                        </p>
                    ) : (
                        <div className="flex flex-col gap-2">
                            {assignedUsers.map((u) => (
                                <div
                                    key={u.id}
                                    className="flex items-center gap-2"
                                >
                                    <span className="min-w-0 flex-1 truncate text-sm">
                                        {u.name}
                                    </span>
                                    <Select
                                        value={u.permission}
                                        onValueChange={(v) =>
                                            handlePermissionChange(
                                                u.id,
                                                v as 'viewer' | 'tracker',
                                            )
                                        }
                                    >
                                        <SelectTrigger className="h-7 w-24 text-xs">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="tracker">
                                                Tracker
                                            </SelectItem>
                                            <SelectItem value="viewer">
                                                Viewer
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="size-7 shrink-0 text-muted-foreground hover:text-destructive"
                                        onClick={() => handleRemoveUser(u.id)}
                                    >
                                        <UserMinus className="size-3.5" />
                                    </Button>
                                </div>
                            ))}
                        </div>
                    )}

                    {unassignedUsers.length > 0 && (
                        <div className="flex items-center gap-2 border-t pt-3">
                            <Select
                                value={addUserId}
                                onValueChange={setAddUserId}
                            >
                                <SelectTrigger className="h-7 flex-1 text-xs">
                                    <SelectValue placeholder="Add user…" />
                                </SelectTrigger>
                                <SelectContent>
                                    {unassignedUsers.map((u) => (
                                        <SelectItem
                                            key={u.id}
                                            value={String(u.id)}
                                        >
                                            {u.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Select
                                value={addPermission}
                                onValueChange={(v) =>
                                    setAddPermission(v as 'viewer' | 'tracker')
                                }
                            >
                                <SelectTrigger className="h-7 w-24 text-xs">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="tracker">
                                        Tracker
                                    </SelectItem>
                                    <SelectItem value="viewer">
                                        Viewer
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="size-7 shrink-0"
                                disabled={!addUserId}
                                onClick={handleAssign}
                            >
                                <UserPlus className="size-3.5" />
                            </Button>
                        </div>
                    )}
                </div>
            )}

            {/* Admin actions */}
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
