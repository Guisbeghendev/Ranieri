<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <AppLayout>
        <Head title="Confirme seu email"/>

        <!--<template #title>
            <h1>ConfirmPassword</h1>
        </template>-->

        <div class="min-h-screen flex">
            <div
                class="w-1/2 hidden lg:flex items-center justify-center bg-gradient-to-br from-laranja1 via-laranja2 to-roxo2 text-white p-10">
                <div class="text-center space-y-4">
                    <h2 class="text-3xl font-bold">Confirme seu e-mail</h2>
                    <p class="text-lg">Antes de começar, por favor, confirme seu endereço de e-mail clicando no link que
                        enviamos. Se não recebeu, podemos enviar outro.</p>
                    <span class="text-6xl">📧</span>
                </div>
            </div>

            <div class="w-full lg:w-1/2 flex flex-col justify-center bg-white dark:bg-gray-900 p-6">
                <div v-if="verificationLinkSent" class="mb-4 text-sm font-medium text-green-600">
                    Um novo link de verificação foi enviado para o e-mail cadastrado.
                </div>

                <form @submit.prevent="submit" class="flex items-center justify-between mt-4">
                    <PrimaryButton
                        :class="{ 'opacity-25 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        Reenviar e-mail de verificação
                    </PrimaryButton>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-300 dark:hover:text-white"
                    >
                        Sair
                    </Link>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
