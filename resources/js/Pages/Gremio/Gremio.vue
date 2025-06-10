<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

// Este é um componente de página simples para o Grêmio, indicando que está em construção.
// Você pode expandir este conteúdo conforme o projeto avançar.

</script>

<template>
    <AppLayout>
        <Head title="Grêmio" />

        <template #title>
            <h1>Grêmio</h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8 text-center">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Em Breve: Grêmio Estudantil Ranieri!</h2>
                    <p class="text-gray-700 leading-relaxed mb-6">
                        Estamos trabalhando para construir a página do Grêmio Estudantil,
                        um espaço dedicado aos alunos da nossa escola.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        Fique atento para novidades sobre eventos, notícias e como se engajar!
                    </p>

                    <div class="mt-8">
                        <!-- Imagem de placeholder para indicar "em construção" -->
                        <img src="https://placehold.co/400x250/AED6F1/2E86C1?text=PAGINA+EM+CONSTRUCAO"
                             alt="Página em construção"
                             class="mx-auto rounded-lg shadow-md max-w-full h-auto"
                             onerror="this.onerror=null;this.src='https://placehold.co/400x250/cccccc/000000?text=Imagem+Nao+Disponivel';">
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Adicione estilos específicos para esta página se necessário */
</style>
