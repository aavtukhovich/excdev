import { reactive } from 'vue';

import { apiRequest, ensureCsrfCookie } from '@/lib/http';
import type { AuthenticatedUser } from '@/types/api';

interface AuthState {
    initialized: boolean;
    loading: boolean;
    user: AuthenticatedUser | null;
}

const state = reactive<AuthState>({
    initialized: false,
    loading: false,
    user: null,
});

export function useAuthStore() {
    async function bootstrap(): Promise<void> {
        if (state.initialized || state.loading) {
            return;
        }

        state.loading = true;

        try {
            const payload = await apiRequest<{ data: AuthenticatedUser }>('/api/auth/user');
            state.user = payload.data;
        } catch {
            state.user = null;
        } finally {
            state.loading = false;
            state.initialized = true;
        }
    }

    async function login(login: string, password: string, remember: boolean): Promise<void> {
        await ensureCsrfCookie();

        const payload = await apiRequest<{ data: AuthenticatedUser }>('/api/auth/login', {
            method: 'POST',
            body: { login, password, remember },
        });

        state.user = payload.data;
        state.initialized = true;
    }

    async function logout(): Promise<void> {
        await ensureCsrfCookie();
        await apiRequest<{ data: { status: string } }>('/api/auth/logout', {
            method: 'POST',
        });

        state.user = null;
        state.initialized = true;
    }

    return {
        state,
        bootstrap,
        login,
        logout,
    };
}
