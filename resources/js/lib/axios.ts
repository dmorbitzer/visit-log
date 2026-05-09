import axios from 'axios';
import { toast } from 'sonner';

const instance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
});

instance.interceptors.request.use((config) => {
    const token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    if (token) {
        config.headers['X-CSRF-TOKEN'] = token;
    }

    return config;
});

instance.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status;

        switch (status) {
            case 401:
                toast.error('Session expired. Please log in again.');
                window.location.href = '/login';
                break;
            case 403:
                toast.error(
                    'You do not have permission to perform this action.',
                );
                break;
            case 422:
                toast.error(error.response.data.message ?? 'Validation error.');
                break;
            case 500:
                toast.error('Server error. Please try again later.');
                break;
            default:
                if (!error.response) {
                    toast.error('No connection to server.');
                }

                break;
        }

        return Promise.reject(error);
    },
);

export default instance;
