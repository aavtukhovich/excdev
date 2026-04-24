<script setup lang="ts">
import { RefreshCw } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { RouterLink } from 'vue-router';

import MainLayout from '@/layouts/MainLayout.vue';
import { apiRequest } from '@/lib/http';
import type { DashboardPayload, OperationItem } from '@/types/api';

const loading = ref(false);
const dashboard = ref<DashboardPayload | null>(null);
let refreshTimerId: number | null = null;

function formatDate(value: string | null): string {
    if (value === null) {
        return '—';
    }

    return new Intl.DateTimeFormat('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

function statusClass(operation: OperationItem): string {
    if (operation.status === 'rejected') {
        return 'badge-danger-soft';
    }

    return 'badge-soft';
}

async function loadDashboard(): Promise<void> {
    loading.value = true;

    try {
        const payload = await apiRequest<{ data: DashboardPayload }>(
            '/api/dashboard',
        );
        dashboard.value = payload.data;

        if (refreshTimerId !== null) {
            window.clearInterval(refreshTimerId);
        }

        refreshTimerId = window.setInterval(() => {
            void loadDashboard();
        }, Math.max(payload.data.refresh_interval_seconds, 2) * 1000);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    void loadDashboard();
});

onUnmounted(() => {
    if (refreshTimerId !== null) {
        window.clearInterval(refreshTimerId);
    }
});
</script>

<template>
    <MainLayout>
        <div class="d-grid gap-3">
            <section class="tool-panel p-4">
                <div
                    class="d-flex align-items-center justify-content-between flex-wrap gap-3"
                >
                    <div>
                        <small class="text-secondary d-block mb-1"
                            >Текущий баланс</small
                        >
                        <div class="money-value">
                            {{
                                dashboard?.balance.current_formatted ??
                                Number(dashboard?.balance.current ?? 0).toFixed(2)
                            }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button
                            type="button"
                            class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
                            :disabled="loading"
                            @click="loadDashboard"
                        >
                            <RefreshCw :size="16" />
                            <span>{{
                                loading ? 'Обновляем...' : 'Обновить'
                            }}</span>
                        </button>
                        <RouterLink to="/operations" class="btn btn-brand"
                            >История операций</RouterLink
                        >
                    </div>
                </div>
            </section>

            <section class="tool-panel p-md-4 p-3">
                <div
                    class="d-flex align-items-center justify-content-between mb-3"
                >
                    <h2 class="h5 mb-0">Пять последних операций</h2>
                </div>

                <div class="table-responsive">
                    <table class="table-sm mb-0 table align-middle">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Тип</th>
                                <th>Описание</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-if="
                                    (dashboard?.recent_operations.length ??
                                        0) === 0
                                "
                            >
                                <td
                                    colspan="5"
                                    class="text-secondary py-4 text-center"
                                >
                                    Операций пока нет
                                </td>
                            </tr>
                            <tr
                                v-for="operation in dashboard?.recent_operations ??
                                []"
                                :key="operation.id"
                            >
                                <td>{{ formatDate(operation.date) }}</td>
                                <td>
                                    {{
                                        operation.type_label ??
                                        (operation.type === 'debit'
                                            ? 'Списание'
                                            : 'Начисление')
                                    }}
                                </td>
                                <td class="text-break">
                                    {{ operation.description }}
                                </td>
                                <td>
                                    {{
                                        operation.amount_formatted ??
                                        Number(operation.amount).toFixed(2)
                                    }}
                                </td>
                                <td>
                                    <span
                                        class="badge"
                                        :class="statusClass(operation)"
                                    >
                                        {{
                                            operation.status_label ??
                                            (operation.status === 'rejected'
                                                ? 'Отклонена'
                                                : operation.status === 'completed'
                                                  ? 'Проведена'
                                                  : 'В очереди')
                                        }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </MainLayout>
</template>
