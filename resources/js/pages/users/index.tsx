import { router, useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import { MoreHorizontal, Pencil, Plus, Trash2 } from 'lucide-react';
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
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useTranslation } from '@/hooks/use-translation';
import {
    destroy as destroyRoute,
    store as storeRoute,
    update as updateRoute,
} from '@/routes/users';
import type { User } from '@/types';

type Props = {
    users: User[];
};

type UserFormData = {
    name: string;
    username: string;
    role: 'admin' | 'tracker';
    password: string;
};

export default function UsersIndex({ users }: Props) {
    const [sheetOpen, setSheetOpen] = useState(false);
    const [editingUser, setEditingUser] = useState<User | null>(null);

    const { t } = useTranslation();
    const { data, setData, post, patch, processing, errors, reset } =
        useForm<UserFormData>({
            name: '',
            username: '',
            role: 'tracker',
            password: '',
        });

    const openCreate = () => {
        reset();
        setEditingUser(null);
        setSheetOpen(true);
    };

    const openEdit = (user: User) => {
        setData({
            name: user.name,
            username: user.username,
            role: user.role,
            password: '',
        });
        setEditingUser(user);
        setSheetOpen(true);
    };

    const submit = (e: React.SyntheticEvent) => {
        e.preventDefault();

        if (editingUser) {
            patch(updateRoute.url(editingUser.id), {
                onSuccess: () => setSheetOpen(false),
            });
        } else {
            post(storeRoute.url(), {
                onSuccess: () => setSheetOpen(false),
            });
        }
    };

    return (
        <>
            <Head title={t('Users')} />

            <div className="flex flex-col gap-6 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-medium">{t('Users')}</h1>
                    <Button
                        size="sm"
                        className="cursor-pointer gap-1"
                        onClick={openCreate}
                    >
                        <Plus className="size-4" />
                        {t('New user')}
                    </Button>
                </div>

                <div className="overflow-x-auto rounded-md border">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                <th className="hidden px-4 py-3 text-left sm:table-cell">
                                    {t('Name')}
                                </th>
                                <th className="px-4 py-3 text-left">
                                    {t('Username')}
                                </th>
                                <th className="px-4 py-3 text-left">
                                    {t('Role')}
                                </th>
                                <th className="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((user) => (
                                <tr
                                    key={user.id}
                                    className="group border-b last:border-0 hover:bg-muted/50"
                                >
                                    <td className="hidden px-4 py-3 font-medium sm:table-cell">
                                        {user.name}
                                    </td>
                                    <td className="px-4 py-3">
                                        {user.username}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge
                                            variant={
                                                user.role === 'admin'
                                                    ? 'default'
                                                    : 'secondary'
                                            }
                                        >
                                            {t(user.role)}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex items-center justify-end">
                                            <AlertDialog>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger
                                                        asChild
                                                    >
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            className="size-7 cursor-pointer sm:opacity-0 sm:group-hover:opacity-100"
                                                        >
                                                            <MoreHorizontal className="size-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem
                                                            className="cursor-pointer"
                                                            onClick={() =>
                                                                openEdit(user)
                                                            }
                                                        >
                                                            <Pencil className="mr-2 size-3.5" />
                                                            {t('Edit')}
                                                        </DropdownMenuItem>
                                                        <AlertDialogTrigger
                                                            asChild
                                                        >
                                                            <DropdownMenuItem className="cursor-pointer text-destructive focus:text-destructive">
                                                                <Trash2 className="mr-2 size-3.5" />
                                                                {t('Delete')}
                                                            </DropdownMenuItem>
                                                        </AlertDialogTrigger>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                                <AlertDialogContent>
                                                    <AlertDialogHeader>
                                                        <AlertDialogTitle>
                                                            {t('Delete user?')}
                                                        </AlertDialogTitle>
                                                        <AlertDialogDescription>
                                                            {t(
                                                                ':name will be permanently deleted.',
                                                                {
                                                                    name: user.name,
                                                                },
                                                            )}
                                                        </AlertDialogDescription>
                                                    </AlertDialogHeader>
                                                    <AlertDialogFooter>
                                                        <AlertDialogCancel>
                                                            {t('Cancel')}
                                                        </AlertDialogCancel>
                                                        <AlertDialogAction
                                                            className="bg-destructive text-white hover:bg-destructive/90"
                                                            onClick={() =>
                                                                router.delete(
                                                                    destroyRoute(
                                                                        user.id,
                                                                    ),
                                                                )
                                                            }
                                                        >
                                                            {t('Delete')}
                                                        </AlertDialogAction>
                                                    </AlertDialogFooter>
                                                </AlertDialogContent>
                                            </AlertDialog>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <Sheet open={sheetOpen} onOpenChange={setSheetOpen}>
                <SheetContent side="right">
                    <SheetHeader>
                        <SheetTitle>
                            {editingUser ? t('Edit user') : t('New user')}
                        </SheetTitle>
                    </SheetHeader>

                    <form
                        onSubmit={submit}
                        className="flex h-full flex-col px-4 py-4"
                    >
                        <div className="flex flex-col gap-4">
                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="name">{t('Name')}</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                />
                                {errors.name && (
                                    <p className="text-xs text-destructive">
                                        {errors.name}
                                    </p>
                                )}
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="username">
                                    {t('Username')}
                                </Label>
                                <Input
                                    id="username"
                                    value={data.username}
                                    onChange={(e) =>
                                        setData('username', e.target.value)
                                    }
                                />
                                {errors.username && (
                                    <p className="text-xs text-destructive">
                                        {errors.username}
                                    </p>
                                )}
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="role">{t('Role')}</Label>
                                <Select
                                    value={data.role}
                                    onValueChange={(v) =>
                                        setData(
                                            'role',
                                            v as 'admin' | 'tracker',
                                        )
                                    }
                                >
                                    <SelectTrigger id="role">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="admin">
                                            {t('admin')}
                                        </SelectItem>
                                        <SelectItem value="tracker">
                                            {t('tracker')}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.role && (
                                    <p className="text-xs text-destructive">
                                        {errors.role}
                                    </p>
                                )}
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <Label htmlFor="password">
                                    {editingUser
                                        ? t('New password (optional)')
                                        : t('Password')}
                                </Label>
                                <Input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) =>
                                        setData('password', e.target.value)
                                    }
                                />
                                {errors.password && (
                                    <p className="text-xs text-destructive">
                                        {errors.password}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="mt-auto pt-4">
                            <Button
                                type="submit"
                                className="w-full cursor-pointer"
                                disabled={processing}
                            >
                                {editingUser
                                    ? t('Save changes')
                                    : t('New user')}
                            </Button>
                        </div>
                    </form>
                </SheetContent>
            </Sheet>
        </>
    );
}
