<script setup>
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { router } from '@inertiajs/vue3'

const props = defineProps({
    galleries: Array, // Lista de galerias
    groups: Array,    // Lista de grupos para o filtro
    filters: Object,  // Filtros aplicados (group_id, event_date)
});

// Função auxiliar para formatar a data para YYYY-MM-DD (para inputs type="date")
const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        // Verifica se a data é válida
        if (isNaN(date.getTime())) {
            console.warn('Data inválida fornecida para formatDateForInput:', dateString);
            return '';
        }
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    } catch (e) {
        console.error('Erro ao formatar data:', dateString, e);
        return '';
    }
};


// Formulário de filtros
const filterForm = useForm({
    group_id: props.filters.group_id || '',
    // Aplica a formatação YYYY-MM-DD ao valor inicial do filtro de data
    event_date: formatDateForInput(props.filters.event_date),
});

// Funções para aplicar e resetar filtros
const applyFilters = () => {
    router.get(route('fotografo.galleries.index'), filterForm.data(), {
        preserveState: true, // Mantém o estado da página
        replace: true,       // Substitui o histórico do navegador (sem criar nova entrada)
    });
};

const resetFilters = () => {
    filterForm.group_id = '';
    filterForm.event_date = '';
    applyFilters(); // Aplica os filtros (vazios) para resetar
};

// Observa mudanças nos campos de filtro e aplica automaticamente
watch(() => filterForm.group_id, () => applyFilters());
watch(() => filterForm.event_date, (newValue) => {
    // Aplica o filtro quando a data muda, mas só se o valor não estiver vazio para evitar chamadas desnecessárias ao resetar
    if (newValue !== null && newValue !== undefined) {
        applyFilters();
    }
});


// Função para formatar a data para exibição na tabela (padrão brasileiro)
const formatEventDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return 'Data inválida'; // Retorna uma mensagem de erro se a data for inválida
        }
        return date.toLocaleDateString('pt-BR'); // Formata para o padrão brasileiro
    } catch (e) {
        return 'Data inválida';
    }
};

// Função para deletar galeria (reutilizando a lógica existente)
const confirmDeleteGallery = (gallery) => {
    if (confirm(`Tem certeza que deseja deletar a galeria "${gallery.title}"? Todas as imagens e dados serão removidos.`)) {
        router.delete(route('fotografo.galleries.destroy', gallery.id), {
            onSuccess: () => {
                alert('Galeria excluída com sucesso!');
                // A Inertia fará um reload, buscando a lista atualizada
            },
            onError: (errors) => {
                alert('Erro ao excluir galeria: ' + Object.values(errors).join('\n'));
            }
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Minhas Galerias" />

        <template #title>
            <h1>Minhas Galerias</h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Listagem de Galerias</h3>
                            <Link :href="route('fotografo.galleries.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Criar Nova Galeria
                            </Link>
                        </div>

                        <div class="mb-6 p-4 border border-gray-200 rounded-md bg-gray-50 flex flex-wrap gap-4 items-end">
                            <div>
                                <label for="filter-group" class="block text-sm font-medium text-gray-700">Filtrar por Grupo:</label>
                                <SelectInput
                                    id="filter-group"
                                    v-model="filterForm.group_id"
                                    :options="groups"
                                    defaultOptionLabel="-- Todos os Grupos --"
                                    defaultOptionValue=""
                                />
                            </div>
                            <div>
                                <label for="filter-event-date" class="block text-sm font-medium text-gray-700">Filtrar por Data do Evento:</label>
                                <TextInput
                                    id="filter-event-date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="filterForm.event_date"
                                />
                            </div>
                            <div class="flex-grow"></div>
                            <PrimaryButton @click="applyFilters" class="self-end">Aplicar Filtros</PrimaryButton>
                            <PrimaryButton @click="resetFilters" class="self-end bg-gray-500 hover:bg-gray-600">Resetar Filtros</PrimaryButton>
                        </div>

                        <div v-if="galleries.length > 0" class="overflow-x-auto hidden md:block">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data do Evento</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grupos</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="gallery in galleries" :key="gallery.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ gallery.title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatEventDate(gallery.event_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="gallery.groups.length > 0">
                                                <span v-for="(group, index) in gallery.groups" :key="group.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mr-2">
                                                    {{ group.name }}
                                                </span>
                                            </span>
                                        <span v-else class="text-gray-400">Nenhum grupo</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link :href="route('fotografo.galleries.upload-images', gallery.id)" class="text-indigo-600 hover:text-indigo-900 mr-4">
                                            Upload Imagens
                                        </Link>
                                        <Link :href="route('fotografo.galleries.edit', gallery.id)" class="text-blue-600 hover:text-blue-900 mr-4">
                                            Editar
                                        </Link>
                                        <Link :href="route('fotografo.galleries.show', gallery.id)" class="text-green-600 hover:text-green-900 mr-4">
                                            Ver Imagens
                                        </Link>
                                        <button @click="confirmDeleteGallery(gallery)" class="text-red-600 hover:text-red-900">
                                            Deletar
                                        </button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="galleries.length > 0" class="md:hidden divide-y divide-gray-200 border border-gray-200 rounded-md shadow-sm">
                            <div v-for="gallery in galleries" :key="gallery.id" class="p-4 bg-white">
                                <div class="font-bold text-lg text-gray-900 mb-2">{{ gallery.title }}</div>
                                <div class="text-sm text-gray-700 mb-1">
                                    <span class="font-medium">Data do Evento:</span> {{ formatEventDate(gallery.event_date) }}
                                </div>
                                <div class="text-sm text-gray-700 mb-2">
                                    <span class="font-medium">Grupos:</span>
                                    <span v-if="gallery.groups.length > 0">
                                        <span v-for="(group, index) in gallery.groups" :key="group.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mr-2">
                                            {{ group.name }}
                                        </span>
                                    </span>
                                    <span v-else class="text-gray-400">Nenhum grupo</span>
                                </div>
                                <div class="flex flex-wrap gap-2 text-sm font-medium mt-3">
                                    <Link :href="route('fotografo.galleries.upload-images', gallery.id)" class="text-indigo-600 hover:text-indigo-900">Upload Imagens</Link>
                                    <Link :href="route('fotografo.galleries.edit', gallery.id)" class="text-blue-600 hover:text-blue-900">Editar</Link>
                                    <Link :href="route('fotografo.galleries.show', gallery.id)" class="text-green-600 hover:text-green-900">Ver Imagens</Link>
                                    <button @click="confirmDeleteGallery(gallery)" class="text-red-600 hover:text-red-900">Deletar</button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="p-4 text-center text-gray-500">
                            Nenhuma galeria encontrada.
                            <Link :href="route('fotografo.galleries.create')" class="text-indigo-600 hover:text-indigo-900 ml-1">
                                Crie uma agora!
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
