<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink, useRouter } from 'vue-router';

import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();

const userCaption = computed(() => {
    if (auth.state.user === null) {
        return '';
    }

    return `${auth.state.user.login},`;
});

async function handleLogout(): Promise<void> {
    await auth.logout();
    await router.push({ name: 'login' });
}
</script>

<template>
    <div class="site-shell">
        <header class="site-header">
            <div
                class="content-wrap d-flex align-items-center justify-content-between py-3"
            >
                <div class="d-flex align-items-center gap-3">
                    <span class="brand-title">Баланс пользователя</span>
                    <nav class="d-flex gap-2">
                        <RouterLink
                            to="/dashboard"
                            class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-2"
                        >
                            <span>Главная</span>
                        </RouterLink>
                        <RouterLink
                            to="/operations"
                            class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-2"
                        >
                            <span>История</span>
                        </RouterLink>
                    </nav>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-secondary">{{ userCaption }}</small>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-2"
                        @click="handleLogout"
                    >
                        <span>Выйти</span>
                    </button>
                </div>
            </div>
        </header>
        <main class="content-wrap">
            <slot />
        </main>
    </div>
</template>
