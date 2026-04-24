<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';

import { ApiError } from '@/lib/http';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();

const login = ref('');
const password = ref('');
const formError = ref('');
const loading = ref(false);

async function submit(): Promise<void> {
    formError.value = '';
    loading.value = true;

    try {
        await auth.login(login.value, password.value);
        await router.push({ name: 'dashboard' });
    } catch (error) {
        if (error instanceof ApiError) {
            formError.value =
                error.validationErrors.login?.[0] ?? error.message;
        } else {
            formError.value = 'Не удалось выполнить вход.';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="auth-surface">
        <section class="tool-panel p-md-5 p-4">
            <h1 class="auth-title">Вход</h1>
            <p class="text-secondary mb-4">
                Используйте логин и пароль пользователя.
            </p>

            <form class="d-grid gap-3" @submit.prevent="submit">
                <div>
                    <label class="form-label" for="login">Логин</label>
                    <input
                        id="login"
                        v-model.trim="login"
                        type="text"
                        class="form-control"
                        autocomplete="username"
                        required
                    />
                </div>

                <div>
                    <label class="form-label" for="password">Пароль</label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        class="form-control"
                        autocomplete="current-password"
                        required
                    />
                </div>

                <div v-if="formError" class="alert alert-danger mb-0 py-2">
                    {{ formError }}
                </div>

                <button
                    type="submit"
                    class="btn btn-brand w-100"
                    :disabled="loading"
                >
                    {{ loading ? 'Входим...' : 'Войти' }}
                </button>
            </form>
        </section>
    </div>
</template>
