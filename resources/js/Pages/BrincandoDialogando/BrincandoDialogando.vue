<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3'; // Importar Link para usar o link externo

// Este componente agora exibe informações sobre o canal "Brincando & Dialogando"
// e fornece um link direto para o YouTube.

</script>

<template>
    <AppLayout>
        <Head title="Brincando e Dialogando" />

        <template #title>
            <h1>Brincando e Dialogando</h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 lg:p-8 text-center">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Bem-vindo ao Canal Brincando&Dialogando!</h2>
                    <p class="text-gray-700 leading-relaxed mb-6">
                        Explore nosso conteúdo educativo e divertido no YouTube,
                        onde o aprendizado acontece de forma lúdica e interativa.
                        Clique no botão abaixo para nos visitar e se inscrever!
                    </p>

                    <div class="mt-8">
                        <!-- Link estilizado para o canal do YouTube -->
                        <a href="https://www.youtube.com/@BrincandoeDialogando"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                            <!-- Ícone do YouTube (SVG inline) -->
                            <svg class="-ml-1 mr-3 h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12.016 3.167c-1.897 0-3.693.447-5.32 1.341-1.627.893-2.986 2.186-4.077 3.873-1.091 1.687-1.748 3.593-1.748 5.626 0 2.033.657 3.94 1.748 5.626 1.091 1.687 2.45 2.98 4.077 3.873 1.627.894 3.423 1.341 5.32 1.341s3.693-.447 5.32-1.341c1.627-.893 2.986-2.186 4.077-3.873 1.091-1.686 1.748-3.593 1.748-5.626 0-2.033-.657-3.94-1.748-5.626-1.091-1.687-2.45-2.98-4.077-3.873-1.627-.894-3.423-1.341-5.32-1.341zm-4.321 6.836l6.634 3.771-6.634 3.772v-7.543z" />
                            </svg>
                            Visitar o Canal no YouTube
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Adicione estilos específicos para esta página se necessário */
</style>
