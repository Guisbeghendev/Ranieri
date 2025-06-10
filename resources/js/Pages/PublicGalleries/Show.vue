<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    gallery: Object,
    images: Object, // Agora é um objeto de paginação (Inertia links, data, etc.)
});

const isModalOpen = ref(false);
// currentImageIndex agora se refere ao índice DENTRO DA PÁGINA ATUAL
const currentImageIndex = ref(0);

// Computed property para obter a imagem atual no modal da página atual
const currentImage = computed(() => {
    if (props.images && props.images.data.length > 0) {
        return props.images.data[currentImageIndex.value];
    }
    return null;
});

const openModal = (index) => {
    currentImageIndex.value = index;
    isModalOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    isModalOpen.value = false;
    document.body.style.overflow = '';
};

// Funções de navegação do modal ajustadas para a página atual
const nextImage = () => {
    if (props.images && props.images.data.length > 1) { // Verifica se há mais de uma imagem na página
        if (currentImageIndex.value < props.images.data.length - 1) {
            currentImageIndex.value++;
        } else {
            // Se for a última imagem da página, não faz nada (Opção A)
        }
    }
};

const prevImage = () => {
    if (props.images && props.images.data.length > 1) { // Verifica se há mais de uma imagem na página
        if (currentImageIndex.value > 0) {
            currentImageIndex.value--;
        } else {
            // Se for a primeira imagem da página, não faz nada
        }
    }
};

const formatEventDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
};

// FUNÇÃO PARA FORMATAR O RÓTULO DO LINK DE PAGINAÇÃO: CORRIGIDA PARA AS CHAVES LITERAIS
const formatPaginationLabel = (label) => {
    if (label === 'pagination.previous') { // Agora verifica a chave literal
        return 'Anterior';
    } else if (label === 'pagination.next') { // Agora verifica a chave literal
        return 'Próximo';
    }
    return label; // Retorna o número da página ou outro texto como está
};
</script>

<template>
    <AppLayout>
        <Head :title="gallery.title" />

        <!--<template #title>
            <h2 >
                Galeria: {{ gallery.title }}
            </h2>
        </template>-->

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-6">
                            <Link :href="route('public.galleries.index')"
                                  class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Voltar para Grupos
                            </Link>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Detalhes da Galeria</h3>
                            <p class="text-gray-700"><strong>Nome da Galeria:</strong> {{ gallery.title }}</p>
                            <p class="text-gray-700"><strong>Descrição:</strong> {{ gallery.description || 'Sem descrição' }}</p>
                            <p class="text-gray-700"><strong>Data do Evento:</strong> {{ formatEventDate(gallery.event_date) }}</p>
                            <p class="text-gray-700">
                                <strong>Grupos:</strong>
                                <span v-if="gallery.groups && gallery.groups.length > 0">
                                    <span v-for="group in gallery.groups" :key="group.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                        {{ group.name }}
                                    </span>
                                </span>
                                <span v-else>Nenhum</span>
                            </p>
                        </div>

                        <h3 class="text-lg font-medium text-gray-900 mb-4">Imagens da Galeria</h3>

                        <div v-if="images && images.data.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <div v-for="(image, index) in images.data" :key="image.id" class="relative group bg-gray-100 rounded-lg overflow-hidden shadow-sm cursor-pointer thumbnail-container" @click="openModal(index)">
                                <img :src="image.watermarked_url || image.path_original" :alt="image.original_filename" class="w-full h-full object-contain thumbnail-image">
                                <div class="p-2 text-xs text-gray-700 truncate">
                                    {{ image.original_filename }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-4 text-center text-gray-500">
                            Nenhuma imagem nesta galeria.
                        </div>

                        <div v-if="images && images.links && images.links.length > 3" class="mt-8 flex justify-center">
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <template v-for="(link, key) in images.links" :key="key">
                                    <Link v-if="link.url"
                                          :href="link.url"
                                          class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                          :class="{ 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active, 'border-gray-300 text-gray-500 bg-white hover:bg-gray-50': !link.active }"
                                    >
                                        {{ formatPaginationLabel(link.label) }}
                                    </Link>
                                    <span v-else
                                          class="relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-not-allowed"
                                          :class="{ 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active, 'border-gray-300 text-gray-400 bg-gray-100': !link.active }"
                                    >
                                        {{ formatPaginationLabel(link.label) }}
                                    </span>
                                </template>
                            </nav>
                        </div>
                        <div v-else-if="images && images.data.length === 0" class="p-4 text-center text-gray-500">
                            Nenhuma imagem encontrada.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 flex items-center justify-center z-50 modal-overlay-dark" @click.self="closeModal">
            <div class="relative flex flex-col items-center justify-center max-w-4xl max-h-full w-full h-full p-4 modal-content-area">
                <div class="flex items-center justify-center w-full image-display-area">
                    <img :src="currentImage?.watermarked_url || currentImage?.path_original" :alt="currentImage?.original_filename" class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
                </div>

                <div class="mt-4 flex justify-center space-x-4 w-full modal-buttons-area">
                    <button v-if="images && images.data.length > 1" @click="prevImage" class="p-3 rounded-full bg-gray-800 text-white text-xl hover:bg-gray-700 transition shadow-lg">
                        &#10094; </button>

                    <button @click="closeModal" class="p-3 rounded-full bg-red-600 text-white text-xl font-bold hover:bg-red-700 transition shadow-lg">
                        &times; </button>

                    <button v-if="images && images.data.length > 1" @click="nextImage" class="p-3 rounded-full bg-gray-800 text-white text-xl hover:bg-gray-700 transition shadow-lg">
                        &#10095; </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.modal-overlay-dark {
    background-color: rgba(0, 0, 0, 0.95);
}

/* ESTILOS PARA UNIFORMIZAR AS MINIATURAS (JÁ EXISTENTES) */
.thumbnail-container {
    height: 140px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    background-color: #f3f4f6;
    border-radius: 0.5rem;
}

.thumbnail-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
}

/* NOVOS ESTILOS PARA AJUSTAR A ALTURA DO MODAL E VISIBILIDADE DOS BOTÕES */
.modal-content-area {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
}

.image-display-area {
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding-bottom: 1rem;
    max-height: calc(90vh - 80px);
}

.modal-buttons-area {
    flex-shrink: 0;
}
</style>
