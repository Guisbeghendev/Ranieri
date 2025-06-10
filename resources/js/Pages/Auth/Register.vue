<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Registro/Cadastro" />

        <template #title>
            <h1>Cadastro de novo usuário</h1>
        </template>

        <div class="min-h-screen flex">
            <!-- Lado esquerdo com imagem ou cor -->
            <div class="w-1/2 hidden lg:flex items-center justify-center bg-gradient-to-br from-laranja1 via-laranja2 to-roxo2 text-white p-10">
                <div class="text-center space-y-4">
                    <h2 class="text-3xl font-bold">Bem-vindo!</h2>
                    <p class="text-lg">Crie sua conta para acessar o sistema</p>
                    <span class="text-6xl">👋</span>
                </div>
            </div>

            <!-- Lado direito com formulário -->
            <div class="w-full lg:w-1/2 flex items-center justify-center bg-white dark:bg-gray-900 p-6">
                <div class="w-full max-w-md space-y-6">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Nome" />
                            <TextInput
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-1 text-sm text-red-600" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
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
                                autocomplete="new-password"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-1 text-sm text-red-600" :message="form.errors.password" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" value="Confirmar Senha" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-1 text-sm text-red-600" :message="form.errors.password_confirmation" />
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <Link
                                :href="route('login')"
                                class="text-sm text-gray-600 underline hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                            >
                                Já tem cadastro?
                            </Link>

                            <PrimaryButton
                                class="ms-4"
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                :disabled="form.processing"
                            >
                                Cadastrar
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
