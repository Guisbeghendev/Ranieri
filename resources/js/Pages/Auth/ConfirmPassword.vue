<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AppLayout>
        <Head title="ConfirmPassword" />

        <!--<template #title>
            <h1>ConfirmPassword</h1>
        </template>-->

        <div class="min-h-screen flex">
            <div class="w-1/2 hidden lg:flex items-center justify-center bg-gradient-to-br from-laranja1 via-laranja2 to-roxo2 text-white p-10">
                <div class="text-center space-y-4">
                    <h2 class="text-3xl font-bold">Área Segura</h2>
                    <p class="text-lg">Por favor, confirme sua senha antes de continuar.</p>
                    <span class="text-6xl">🔒</span>
                </div>
            </div>

            <div class="w-full lg:w-1/2 flex items-center justify-center bg-white dark:bg-gray-900 p-6">
                <div class="w-full max-w-md">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="password" value="Senha" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                autofocus
                            />
                            <InputError class="mt-2 text-sm text-red-600" :message="form.errors.password" />
                        </div>

                        <div class="mt-4 flex justify-end">
                            <PrimaryButton
                                class="ms-4"
                                :class="{ 'opacity-25 cursor-not-allowed': form.processing }"
                                :disabled="form.processing"
                            >
                                Confirmar
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
