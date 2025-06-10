<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Login" />

        <template #title>
            <h1>Login no sistema</h1>
        </template>

        <div class="min-h-screen flex">
            <!-- Lado esquerdo com imagem ou cor -->
            <div class="w-1/2 hidden lg:flex items-center justify-center bg-gradient-to-br from-laranja1 via-laranja2 to-roxo2 text-white p-10">
                <div class="text-center space-y-4">
                    <h2 class="text-3xl font-bold">Bem-vindo de volta!</h2>
                    <p class="text-lg">Faça login para continuar acessando</p>
                    <span class="text-6xl">👋</span>
                </div>
            </div>

            <!-- Lado direito com formulário -->
            <div class="w-full lg:w-1/2 flex items-center justify-center bg-white dark:bg-gray-900 p-6">
                <div class="w-full max-w-md space-y-6">
                    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">{{ status }}</div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-1 text-sm text-red-600" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Senha" />
                            <TextInput
                                id="password"
                                type="password"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-1 text-sm text-red-600" :message="form.errors.password" />
                        </div>

                        <div class="block">
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" />
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-300">Lembrar-me</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-gray-600 underline hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                            >
                                Esqueceu sua senha?
                            </Link>

                            <PrimaryButton
                                class="ms-4"
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                :disabled="form.processing"
                            >
                                Entrar
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
