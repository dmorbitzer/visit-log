import { renderHook, act } from '@testing-library/react';
import { toast } from 'sonner';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { useTrackingSlot } from '@/hooks/use-tracking-slot';
import axiosMock from '@/lib/axios';
import type { DisplaySlot } from '@/types/tracking';

// Mock axios
vi.mock('@/lib/axios', () => ({
    default: {
        patch: vi.fn(),
    },
}));

// Mock echo — not needed for most tests
vi.mock('@/lib/echo', () => ({
    getEcho: vi.fn(() => null),
}));

// Mock sonner toast
vi.mock('sonner', () => ({
    toast: { error: vi.fn() },
}));

const EVENT_ID = 1;
const DATE = '2026-05-14';

const virtualSlot: DisplaySlot = {
    id: null,
    slot_start: '09:00',
    slot_end: '09:30',
    visitor_count: 0,
};

const existingSlot: DisplaySlot = {
    id: 10,
    slot_start: '09:00',
    slot_end: '09:30',
    visitor_count: 5,
};

beforeEach(() => {
    vi.clearAllMocks();
    vi.useFakeTimers();
});

afterEach(() => {
    vi.useRealTimers();
});

describe('useTrackingSlot', () => {
    it('initialises count from slot visitor_count', () => {
        const { result } = renderHook(() =>
            useTrackingSlot(EVENT_ID, DATE, existingSlot),
        );
        expect(result.current.count).toBe(5);
    });

    it('increment increases count optimistically', () => {
        const { result } = renderHook(() =>
            useTrackingSlot(EVENT_ID, DATE, existingSlot),
        );

        act(() => result.current.track('increment'));
        expect(result.current.count).toBe(6);
    });

    it('decrement decreases count optimistically', () => {
        const { result } = renderHook(() =>
            useTrackingSlot(EVENT_ID, DATE, existingSlot),
        );

        act(() => result.current.track('decrement'));
        expect(result.current.count).toBe(4);
    });

    it('decrement does not go below zero', () => {
        const { result } = renderHook(() =>
            useTrackingSlot(EVENT_ID, DATE, virtualSlot),
        );

        act(() => result.current.track('decrement'));
        expect(result.current.count).toBe(0);
    });

    it('sends PATCH request after 500ms debounce', async () => {
        (axiosMock.patch as ReturnType<typeof vi.fn>).mockResolvedValue({
            data: { id: 10, visitor_count: 6 },
        });

        const { result } = renderHook(() =>
            useTrackingSlot(EVENT_ID, DATE, existingSlot),
        );

        act(() => result.current.track('increment'));
        expect(axiosMock.patch).not.toHaveBeenCalled();

        await act(async () => {
            vi.advanceTimersByTime(500);
        });

        expect(axiosMock.patch).toHaveBeenCalledOnce();
        expect(axiosMock.patch).toHaveBeenCalledWith(
            `/events/${EVENT_ID}/days/${DATE}/track`,
            expect.objectContaining({ action: 'set', value: 6 }),
        );
    });

    it('multiple rapid clicks debounce into a single request', async () => {
        (axiosMock.patch as ReturnType<typeof vi.fn>).mockResolvedValue({
            data: { id: 10, visitor_count: 8 },
        });

        const { result } = renderHook(() =>
            useTrackingSlot(EVENT_ID, DATE, existingSlot),
        );

        act(() => {
            result.current.track('increment'); // 6
            result.current.track('increment'); // 7
            result.current.track('increment'); // 8
        });

        await act(async () => {
            vi.advanceTimersByTime(500);
        });

        expect(axiosMock.patch).toHaveBeenCalledOnce();
        expect(axiosMock.patch).toHaveBeenCalledWith(
            expect.any(String),
            expect.objectContaining({ value: 8 }),
        );
    });

    it('rolls back count on API error and shows toast', async () => {
        (axiosMock.patch as ReturnType<typeof vi.fn>).mockRejectedValue(
            new Error('Server error'),
        );

        const { result } = renderHook(() =>
            useTrackingSlot(EVENT_ID, DATE, existingSlot),
        );

        act(() => result.current.track('increment'));
        expect(result.current.count).toBe(6); // optimistic

        await act(async () => {
            vi.advanceTimersByTime(500);
        });

        expect(result.current.count).toBe(5); // rolled back
        expect(toast.error).toHaveBeenCalledOnce();
    });

    it('updates slotId after successful track on virtual slot', async () => {
        (axiosMock.patch as ReturnType<typeof vi.fn>).mockResolvedValue({
            data: { id: 99, visitor_count: 1 },
        });

        const { result } = renderHook(() =>
            useTrackingSlot(EVENT_ID, DATE, virtualSlot),
        );

        act(() => result.current.track('increment'));

        await act(async () => {
            vi.advanceTimersByTime(500);
        });

        expect(result.current.count).toBe(1);
    });

    it('sets isPending to true while request is in flight', async () => {
        let resolvePatch!: (v: unknown) => void;
        (axiosMock.patch as ReturnType<typeof vi.fn>).mockReturnValue(
            new Promise((r) => (resolvePatch = r)),
        );

        const { result } = renderHook(() =>
            useTrackingSlot(EVENT_ID, DATE, existingSlot),
        );

        act(() => result.current.track('increment'));
        await act(async () => vi.advanceTimersByTime(500));

        expect(result.current.isPending).toBe(true);

        await act(async () =>
            resolvePatch({ data: { id: 10, visitor_count: 6 } }),
        );
        expect(result.current.isPending).toBe(false);
    });
});
