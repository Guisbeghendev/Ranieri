<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3'; // Certifique-se que 'Link' está importado
</script>

<template>
    <AppLayout>
        <Head title="Painel do Fotógrafo" />

        <template #title>
            <h1>Painel do Fotógrafo</h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="mb-4">Bem-vindo(a) ao painel do fotógrafo!</p>
                        <p class="mb-4">Aqui você poderá gerenciar suas galerias e muito mais.</p>
                        <div class="mt-6 flex flex-col sm:flex-row gap-4"> <Link :href="route('fotografo.galleries.create')"
                                                                                 class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Criar Nova Galeria
                        </Link>

                            <Link :href="route('fotografo.galleries.index')"
                                  class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Ver Minhas Galerias
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
