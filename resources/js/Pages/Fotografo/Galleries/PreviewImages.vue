<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    gallery: Object, // A galeria completa, que agora conterá as imagens
});

// Função para deletar uma imagem individual
const deleteImage = (imageId) => {
    if (confirm('Tem certeza que deseja deletar esta imagem? Esta ação é irreversível.')) {
        router.delete(route('fotografo.galleries.images.destroy', { gallery: props.gallery.id, image: imageId }), {
            onSuccess: () => {
                alert('Imagem deletada com sucesso!');
                // Recarrega os dados da página para atualizar a lista de imagens
                router.reload({ preserveScroll: true });
            },
            onError: (errors) => {
                console.error('Erro ao deletar imagem:', errors);
                alert('Erro ao deletar imagem. Verifique o console.');
            }
        });
    }
};

</script>

<template>
    <AppLayout>
        <Head :title="`Preview: ${gallery.title}`" />
        <template #title>
            <h1>
                Preview de: {{ gallery.title }}
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <Link :href="route('fotografo.galleries.index')"
                                  class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Voltar para Galerias
                            </Link>
                            <Link :href="route('fotografo.galleries.upload-images', gallery.id)" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Adicionar Mais Imagens
                            </Link>
                        </div>

                        <h3 class="text-lg font-medium text-gray-900 mb-4">Miniaturas</h3>

                        <div v-if="gallery.images && gallery.images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <div v-for="image in gallery.images" :key="image.id" class="relative group bg-gray-100 rounded-lg overflow-hidden shadow-sm">
                                <img :src="image.thumbnail_url" :alt="image.original_filename" class="w-full h-32 object-cover">
                                <div class="p-2 text-xs text-gray-700 truncate">
                                    {{ image.original_filename }}
                                </div>
                                <button
                                    @click="deleteImage(image.id)"
                                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                    title="Deletar Imagem"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm6 0a1 1 0 11-2 0v6a1 1 0 112 0V8z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div v-else class="p-4 text-center text-gray-500">
                            Nenhuma imagem encontrada para esta galeria.
                            <Link :href="route('fotografo.galleries.upload-images', gallery.id)" class="text-indigo-600 hover:text-indigo-900 ml-1">
                                Adicionar imagens agora!
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
