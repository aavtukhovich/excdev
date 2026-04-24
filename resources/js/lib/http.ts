import type { ValidationErrors } from '@/types/api';

interface RequestOptions {
    method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
    body?: unknown;
    headers?: Record<string, string>;
}

interface ErrorPayload {
    message?: string;
    errors?: ValidationErrors;
}

function readCookie(name: string): string | null {
    const escapedName = name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const match = document.cookie.match(
        new RegExp(`(?:^|; )${escapedName}=([^;]*)`),
    );

    return match ? decodeURIComponent(match[1]) : null;
}

export class ApiError extends Error {
    constructor(
        message: string,
        public readonly status: number,
        public readonly validationErrors: ValidationErrors = {},
    ) {
        super(message);
    }
}

export async function ensureCsrfCookie(): Promise<void> {
    await fetch('/sanctum/csrf-cookie', {
        method: 'GET',
        credentials: 'include',
        headers: {
            Accept: 'application/json',
        },
    });
}

export async function apiRequest<T>(
    url: string,
    options: RequestOptions = {},
): Promise<T> {
    const method = options.method ?? 'GET';
    const headers: Record<string, string> = {
        Accept: 'application/json',
        ...(options.headers ?? {}),
    };

    const xsrfToken = readCookie('XSRF-TOKEN');

    if (xsrfToken !== null) {
        headers['X-XSRF-TOKEN'] = xsrfToken;
    }

    if (method !== 'GET' && options.body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(url, {
        method,
        credentials: 'include',
        headers,
        body:
            options.body === undefined
                ? undefined
                : JSON.stringify(options.body),
    });

    if (response.status === 204) {
        return undefined as T;
    }

    const contentType = response.headers.get('content-type') ?? '';
    const payload = contentType.includes('application/json')
        ? await response.json()
        : null;

    if (!response.ok) {
        const errorPayload = payload as ErrorPayload | null;
        const message = errorPayload?.message ?? 'Ошибка запроса.';
        const validationErrors = errorPayload?.errors ?? {};

        throw new ApiError(message, response.status, validationErrors);
    }

    return payload as T;
}
