import { describe, it, expect } from 'vitest';
import axios from '@/lib/axios';

describe('axios instance', () => {
    it('has correct base configuration', () => {
        expect(axios.defaults.withCredentials).toBe(true);
        expect(axios.defaults.headers['X-Requested-With']).toBe(
            'XMLHttpRequest',
        );
    });

    it('has request and response interceptors', () => {
        expect(axios.interceptors.request).toBeDefined();
        expect(axios.interceptors.response).toBeDefined();
    });
});
