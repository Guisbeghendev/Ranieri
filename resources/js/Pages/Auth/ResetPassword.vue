<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Nova senha" />

        <!--<template #title>
            <h1>ConfirmPassword</h1>
        </template>-->

        <div class="min-h-screen flex">
            <div class="w-1/2 hidden lg:flex items-center justify-center bg-gradient-to-br from-laranja1 via-laranja2 to-roxo2 text-white p-10">
                <div class="text-center space-y-4">
                    <h2 class="text-3xl font-bold">Redefinir senha</h2>
                    <p class="text-lg">Informe uma nova senha para sua conta.</p>
                    <span class="text-6xl">🔐</span>
                </div>
            </div>

            <div class="w-full lg:w-1/2 flex items-center justify-center bg-white dark:bg-gray-900 p-6">
                <div class="w-full max-w-md">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="email" value="E-mail" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <InputError class="mt-2 text-sm text-red-600" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Senha" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2 text-sm text-red-600" :message="form.errors.password" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" value="Confirmar Senha" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2 text-sm text-red-600" :message="form.errors.password_confirmation" />
                        </div>

                        <div class="mt-4 flex justify-end">
                            <PrimaryButton
                                :class="{ 'opacity-25 cursor-not-allowed': form.processing }"
                                :disabled="form.processing"
                            >
                                Redefinir senha
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
