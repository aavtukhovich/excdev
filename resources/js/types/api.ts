export interface UserBalance {
    current: string;
    current_formatted: string;
}

export interface AuthenticatedUser {
    id: number;
    name: string;
    login: string;
    email: string | null;
    balance: UserBalance;
}

export interface OperationItem {
    id: number;
    type: 'credit' | 'debit';
    type_label?: string;
    status: 'pending' | 'completed' | 'rejected';
    status_label?: string;
    amount: string;
    amount_formatted?: string;
    description: string;
    balance_before: string | null;
    balance_before_formatted?: string | null;
    balance_after: string | null;
    balance_after_formatted?: string | null;
    failure_reason: string | null;
    date: string | null;
    created_at?: string | null;
    processed_at: string | null;
}

export interface DashboardPayload {
    balance: UserBalance;
    recent_operations: OperationItem[];
    refresh_interval_seconds: number;
    refreshed_at: string | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

export interface ValidationErrors {
    [key: string]: string[];
}
