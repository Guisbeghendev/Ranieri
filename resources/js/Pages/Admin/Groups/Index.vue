<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// Define as props que este componente espera receber
const props = defineProps({
    groups: Array, // Espera um array de grupos
});

// Função para excluir um grupo
const deleteGroup = (groupId) => {
    if (confirm('Tem certeza de que deseja excluir este grupo? Esta ação não pode ser desfeita.')) {
        router.delete(route('admin.groups.destroy', groupId), {
            onSuccess: () => {
                // Redireciona para a mesma página, que recarregará a lista
                // ou pode ser feito com uma requisição XHR para atualizar a lista sem recarregar a página inteira
            },
            onError: (errors) => {
                console.error('Erro ao excluir grupo:', errors);
                alert('Erro ao excluir grupo. Verifique o console para mais detalhes.');
            },
        });
    }
};
</script>

<template>
    <Head title="Gerenciar Grupos" />

    <AppLayout>
        <template #title>
            <h1>Gerenciar Grupos</h1>
        </template>

        <template #default>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Lista de Grupos</h3>

                            <div v-if="groups.length > 0">
                                <ul class="divide-y divide-gray-200">
                                    <li v-for="group in groups" :key="group.id" class="py-4 flex justify-between items-center">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">{{ group.name }}</div>
                                            <div class="text-sm text-gray-500">{{ group.description }}</div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <Link :href="route('admin.groups.edit', group.id)" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                Editar
                                            </Link>
                                            <button
                                                @click="deleteGroup(group.id)"
                                                class="text-red-600 hover:text-red-900 text-sm"
                                            >
                                                Excluir
                                            </button>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div v-else class="text-gray-600">
                                Nenhum grupo cadastrado.
                            </div>

                            <div class="mt-6 flex items-center space-x-4">
                                <Link :href="route('admin.groups.create')" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Criar Novo Grupo
                                </Link>

                                <Link :href="route('admin.dashboard')" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Voltar ao Painel
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>
