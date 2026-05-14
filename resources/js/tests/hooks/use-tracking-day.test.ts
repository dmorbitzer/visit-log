import { renderHook, waitFor } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useTrackingDay } from '@/hooks/use-tracking-day';
import axiosMock from '@/lib/axios';
import type { Event } from '@/types/event';

// Mock axios
vi.mock('@/lib/axios', () => ({
    default: {
        get: vi.fn(),
    },
}));

const mockEvent: Event = {
    id: 1,
    name: 'Test Event',
    type: 'recurring',
    status: 'active',
    recurrence_weekdays: [0],
    start_date: null,
    end_date: null,
    tracking_start: '09:00',
    tracking_end: '10:00',
    slot_interval_minutes: 30,
    created_at: '2026-01-01',
    updated_at: '2026-01-01',
};

const DATE = '2026-05-14';

beforeEach(() => {
    vi.clearAllMocks();
});

describe('useTrackingDay', () => {
    it('generates virtual slots from event config when day has no DB data', async () => {
        (axiosMock.get as ReturnType<typeof vi.fn>).mockResolvedValue({
            data: null,
        });

        const { result } = renderHook(() => useTrackingDay(mockEvent, DATE));

        await waitFor(() => expect(result.current.loading).toBe(false));

        // 09:00–09:30 and 09:30–10:00 → 2 virtual slots
        expect(result.current.slots).toHaveLength(2);
        expect(result.current.slots[0].id).toBeNull();
        expect(result.current.slots[0].slot_start).toBe('09:00');
        expect(result.current.slots[0].visitor_count).toBe(0);
        expect(result.current.slots[1].slot_start).toBe('09:30');
    });

    it('merges existing DB slots with virtual slots', async () => {
        (axiosMock.get as ReturnType<typeof vi.fn>).mockResolvedValue({
            data: {
                id: 42,
                date: DATE,
                notes: 'Test note',
                time_slots: [
                    {
                        id: 10,
                        slot_start: '09:00',
                        slot_end: '09:30',
                        visitor_count: 7,
                    },
                ],
            },
        });

        const { result } = renderHook(() => useTrackingDay(mockEvent, DATE));

        await waitFor(() => expect(result.current.loading).toBe(false));

        expect(result.current.slots).toHaveLength(2);
        // First slot: from DB with real id and count
        expect(result.current.slots[0].id).toBe(10);
        expect(result.current.slots[0].visitor_count).toBe(7);
        // Second slot: virtual (not tracked yet)
        expect(result.current.slots[1].id).toBeNull();
        expect(result.current.slots[1].visitor_count).toBe(0);
    });

    it('returns dayId from DB response', async () => {
        (axiosMock.get as ReturnType<typeof vi.fn>).mockResolvedValue({
            data: { id: 99, date: DATE, notes: null, time_slots: [] },
        });

        const { result } = renderHook(() => useTrackingDay(mockEvent, DATE));

        await waitFor(() => expect(result.current.loading).toBe(false));
        expect(result.current.dayId).toBe(99);
    });

    it('returns null dayId when day does not exist', async () => {
        (axiosMock.get as ReturnType<typeof vi.fn>).mockResolvedValue({
            data: null,
        });

        const { result } = renderHook(() => useTrackingDay(mockEvent, DATE));

        await waitFor(() => expect(result.current.loading).toBe(false));
        expect(result.current.dayId).toBeNull();
    });

    it('returns notes from DB response', async () => {
        (axiosMock.get as ReturnType<typeof vi.fn>).mockResolvedValue({
            data: { id: 1, date: DATE, notes: 'Busy day', time_slots: [] },
        });

        const { result } = renderHook(() => useTrackingDay(mockEvent, DATE));

        await waitFor(() => expect(result.current.loading).toBe(false));
        expect(result.current.notes).toBe('Busy day');
    });

    it('sets error state on API failure', async () => {
        (axiosMock.get as ReturnType<typeof vi.fn>).mockRejectedValue(
            new Error('Network error'),
        );

        const { result } = renderHook(() => useTrackingDay(mockEvent, DATE));

        await waitFor(() => expect(result.current.loading).toBe(false));
        expect(result.current.error).toBe(true);
    });

    it('generates correct slots for 60-minute interval', async () => {
        (axiosMock.get as ReturnType<typeof vi.fn>).mockResolvedValue({
            data: null,
        });

        const event60 = { ...mockEvent, slot_interval_minutes: 60 };
        const { result } = renderHook(() => useTrackingDay(event60, DATE));

        await waitFor(() => expect(result.current.loading).toBe(false));

        expect(result.current.slots).toHaveLength(1);
        expect(result.current.slots[0].slot_start).toBe('09:00');
        expect(result.current.slots[0].slot_end).toBe('10:00');
    });
});
