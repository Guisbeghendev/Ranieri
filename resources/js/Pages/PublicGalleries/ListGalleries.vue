<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    galleries: Array, // Lista de galerias que o usuário pode visualizar
    selectedGroupName: { // Novo prop para exibir o nome do grupo selecionado
        type: String,
        default: 'Todas as Galerias Públicas'
    }
});

// Função para formatar a data para exibição
const formatEventDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR'); // Formata para o padrão brasileiro
};
</script>

<template>
    <AppLayout>
        <Head :title="selectedGroupName" />

        <template #title>
            <h1>
                Galerias: {{ selectedGroupName }}
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <Link v-if="$page.props.auth.user" :href="route('public.galleries.index')"
                                  class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Voltar para Seleção de Grupos
                            </Link>
                        </div>

                        <h3 class="text-lg font-medium text-gray-900 mb-4">Galerias Disponíveis</h3>

                        <div v-if="galleries.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            <div v-for="gallery in galleries" :key="gallery.id" class="bg-gray-50 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                <Link :href="route('public.galleries.show', gallery.id)">
                                    <img :src="(gallery.images && gallery.images.length > 0) ? gallery.images[0].thumbnail_url : 'https://placehold.co/400x250/E2E8F0/64748B?text=Sem+Imagem'" :alt="gallery.title" class="w-full h-48 object-cover">
                                    <div class="p-4">
                                        <h4 class="font-bold text-lg text-gray-800 truncate">{{ gallery.title }}</h4>
                                        <p class="text-sm text-gray-600 mb-2 truncate">{{ gallery.description || 'Sem descrição' }}</p>
                                        <p class="text-xs text-gray-500">Data do Evento: {{ formatEventDate(gallery.event_date) }}</p>
                                        <div class="mt-2 text-xs text-gray-500">
                                            Grupos:
                                            <span v-if="gallery.groups.length > 0">
                                                <span v-for="group in gallery.groups" :key="group.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                                    {{ group.name }}
                                                </span>
                                            </span>
                                            <span v-else>Nenhum</span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                        <div v-else class="p-4 text-center text-gray-500">
                            Nenhuma galeria disponível no momento.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
