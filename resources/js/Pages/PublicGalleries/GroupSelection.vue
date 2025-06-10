<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    userGroups: Array, // Grupos aos quais o usuário logado pertence
});
</script>

<template>
    <AppLayout>
        <Head title="Meus Grupos de Galerias" />

        <!--<template #title>
            <h1>
                Selecione um Grupo de Galerias
            </h1>
        </template>-->

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Grupos aos quais você pertence:</h3>

                        <div v-if="userGroups.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="group in userGroups" :key="group.id" class="p-6 bg-blue-100 rounded-lg shadow hover:shadow-lg transition cursor-pointer">
                                <Link :href="route('public.galleries.list-by-group', group.id)" class="block">
                                    <h4 class="text-blue-700 font-semibold mb-2">{{ group.name }}</h4>
                                    <p class="text-sm text-blue-600">Clique para ver as galerias deste grupo.</p>
                                </Link>
                            </div>
                        </div>
                        <div v-else class="p-4 text-center text-gray-500">
                            Você não pertence a nenhum grupo de galerias.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

