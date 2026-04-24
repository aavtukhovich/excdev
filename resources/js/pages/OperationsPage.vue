<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';

import MainLayout from '@/layouts/MainLayout.vue';
import { apiRequest } from '@/lib/http';
import type {
    OperationItem,
    PaginationLink,
    PaginationMeta,
} from '@/types/api';

const operations = ref<OperationItem[]>([]);
const links = ref<PaginationLink[]>([]);
const meta = ref<PaginationMeta | null>(null);
const loading = ref(false);
const loadError = ref('');
const sort = ref<'desc' | 'asc'>('desc');
const page = ref(1);

function formatDate(value: string | null | undefined): string {
    if (value === null || value === undefined) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return new Intl.DateTimeFormat('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

function statusClass(status: OperationItem['status']): string {
    if (status === 'rejected') {
        return 'badge-danger-soft';
    }

    return 'badge-soft';
}

function typeLabel(operation: OperationItem): string {
    return (
        operation.type_label ??
        (operation.type === 'debit' ? 'Списание' : 'Начисление')
    );
}

function statusLabel(operation: OperationItem): string {
    if (operation.status_label) {
        return operation.status_label;
    }

    if (operation.status === 'completed') {
        return 'Проведена';
    }

    if (operation.status === 'rejected') {
        return 'Отклонена';
    }

    return 'В очереди';
}

function amountLabel(operation: OperationItem): string {
    return operation.amount_formatted ?? operation.amount;
}

function operationDate(operation: OperationItem): string | null {
    return (
        operation.date ?? operation.created_at ?? operation.processed_at ?? null
    );
}

function pageFromUrl(url: string | null): number | null {
    if (url === null) {
        return null;
    }

    const parsed = new URL(url, window.location.origin);
    const rawPage = parsed.searchParams.get('page');

    if (rawPage === null) {
        return 1;
    }

    const numericPage = Number(rawPage);

    return Number.isNaN(numericPage) ? null : numericPage;
}

async function loadOperations(): Promise<void> {
    loading.value = true;
    loadError.value = '';

    try {
        const query = new URLSearchParams({
            page: page.value.toString(),
            sort: sort.value,
            per_page: '15',
        });

        const payload = await apiRequest<{
            data: OperationItem[];
            meta: PaginationMeta & { links?: PaginationLink[] };
        }>(`/api/operations?${query.toString()}`);

        operations.value = Array.isArray(payload.data) ? payload.data : [];
        links.value = Array.isArray(payload.meta.links)
            ? payload.meta.links
            : [];
        meta.value = payload.meta;
    } catch {
        operations.value = [];
        links.value = [];
        meta.value = null;
        loadError.value = 'Не удалось загрузить операции.';
    } finally {
        loading.value = false;
    }
}

function changePage(url: string | null): void {
    const nextPage = pageFromUrl(url);

    if (nextPage === null || nextPage === page.value) {
        return;
    }

    page.value = nextPage;
    void loadOperations();
}

watch(sort, () => {
    page.value = 1;
    void loadOperations();
});

onMounted(() => {
    void loadOperations();
});
</script>

<template>
    <MainLayout>
        <section class="tool-panel p-md-4 p-3">
            <div
                class="d-flex align-items-end justify-content-between mb-3 flex-wrap gap-3"
            >
                <div>
                    <h1 class="h5 mb-1">История операций</h1>
                </div>
                <small class="text-secondary">
                    Всего: {{ meta?.total ?? 0 }} | Страница:
                    {{ meta?.current_page ?? 1 }} / {{ meta?.last_page ?? 1 }}
                </small>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-12 col-md-3">
                    <label class="form-label" for="sort-field"
                        >Сортировка по дате</label
                    >
                    <select id="sort-field" v-model="sort" class="form-select">
                        <option value="desc">Сначала новые</option>
                        <option value="asc">Сначала старые</option>
                    </select>
                </div>
            </div>

            <div v-if="loadError" class="alert alert-danger mb-3 py-2">
                {{ loadError }}
            </div>

            <div class="table-responsive">
                <table class="table-sm mb-0 table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Дата</th>
                            <th>Тип</th>
                            <th>Описание</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td
                                colspan="6"
                                class="text-secondary py-4 text-center"
                            >
                                Загружаем...
                            </td>
                        </tr>
                        <tr v-else-if="operations.length === 0">
                            <td
                                colspan="6"
                                class="text-secondary py-4 text-center"
                            >
                                Ничего не найдено
                            </td>
                        </tr>
                        <tr v-for="operation in operations" :key="operation.id">
                            <td>{{ operation.id }}</td>
                            <td>{{ formatDate(operationDate(operation)) }}</td>
                            <td>{{ typeLabel(operation) }}</td>
                            <td class="text-break">
                                {{ operation.description }}
                            </td>
                            <td>{{ amountLabel(operation) }}</td>
                            <td>
                                <span
                                    class="badge"
                                    :class="statusClass(operation.status)"
                                >
                                    {{ statusLabel(operation) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav class="mt-3" aria-label="Pagination">
                <ul class="pagination pagination-sm mb-0 flex-wrap">
                    <li
                        v-for="(link, index) in links"
                        :key="`${index}-${link.label}`"
                        class="page-item"
                        :class="{
                            active: link.active,
                            disabled: link.url === null,
                        }"
                    >
                        <button
                            type="button"
                            class="page-link"
                            :disabled="link.url === null"
                            @click="changePage(link.url)"
                            v-html="link.label"
                        />
                    </li>
                </ul>
            </nav>
        </section>
    </MainLayout>
</template>
