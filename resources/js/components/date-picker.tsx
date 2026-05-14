import { format } from 'date-fns';
import { CalendarIcon } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

type Props = {
    value?: string;
    onChange: (value: string) => void;
    placeholder?: string;
    fromDate?: Date;
    toDate?: Date;
};

export default function DatePicker({
    value,
    onChange,
    placeholder = 'Pick a date',
    fromDate,
    toDate,
}: Props) {
    const [open, setOpen] = useState(false);
    const selected = value ? new Date(value) : undefined;

    const disabled = [
        fromDate ? { before: fromDate } : undefined,
        toDate ? { after: toDate } : undefined,
    ].filter(Boolean) as { before: Date }[] | { after: Date }[];

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    className={cn(
                        'w-full justify-start text-left font-normal',
                        !selected && 'text-muted-foreground',
                    )}
                >
                    <CalendarIcon className="mr-2 size-4" />
                    {selected ? format(selected, 'dd.MM.yyyy') : placeholder}
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0" align="start">
                <Calendar
                    mode="single"
                    selected={selected}
                    startMonth={fromDate}
                    endMonth={toDate}
                    disabled={disabled.length ? disabled : undefined}
                    onSelect={(date) => {
                        onChange(date ? format(date, 'yyyy-MM-dd') : '');
                        setOpen(false);
                    }}
                />
            </PopoverContent>
        </Popover>
    );
}
