<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3'; // Não precisa de useForm aqui
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue'; // Para exibir erros de upload
import { Link } from '@inertiajs/vue3'; // Para o botão de voltar

import axios from 'axios';

const props = defineProps({
    gallery: Object, // A galeria será passada como prop via Inertia
    // Ou, se você passar apenas o ID: galleryId: Number
});

const files = ref([]); // Para gerenciar os arquivos selecionados
const uploadErrors = ref([]); // Para erros específicos de upload de arquivos
const isUploading = ref(false); // Para controlar o estado de upload
const uploadCompleted = ref(false); // Indica se todos os uploads terminaram

// Para ter acesso ao input de arquivo via ref
const fileInput = ref(null);

// Marcas d'água disponíveis (para a escolha do usuário, se necessário)
const availableWatermarks = ref([]);
const selectedWatermark = ref(null);

onMounted(() => {
    fetchWatermarks();
    if (!props.gallery || !props.gallery.id) {
        alert('ID da galeria não fornecido. Redirecionando.');
        window.location.href = route('fotografo.dashboard'); // Redireciona se não tiver ID
    }
});

const fetchWatermarks = async () => {
    try {
        const response = await axios.get(route('fotografo.galleries.watermarks.index'));
        availableWatermarks.value = response.data;
        if (availableWatermarks.value.length > 0) {
            selectedWatermark.value = availableWatermarks.value[0].name; // Pre-seleciona a primeira
        }
    } catch (error) {
        console.error('Erro ao buscar marcas d\'água:', error.response ? error.response.data : error.message);
    }
};

const handleFileChange = (event) => {
    uploadErrors.value = []; // Limpa erros anteriores
    const newFiles = Array.from(event.target.files).map(file => {
        return {
            id: file.name + '-' + file.size + '-' + Date.now(), // ID único para o Vue
            name: file.name,
            size: file.size,
            type: file.type,
            file: file, // O objeto File original
            progress: 0, // Progresso do upload (0-100)
            status: 'pending', // 'pending', 'uploading', 'success', 'error'
            message: '', // Mensagem de erro ou sucesso
            thumb: URL.createObjectURL(file) // URL para pré-visualização
        };
    });
    files.value = [...files.value, ...newFiles]; // Adiciona novos arquivos à lista existente
};

const handleDrop = (event) => {
    event.preventDefault();
    event.stopPropagation();
    // Limpa erros anteriores de upload se novos arquivos forem adicionados por drop
    uploadErrors.value = [];
    const droppedFiles = Array.from(event.dataTransfer.files).map(file => {
        return {
            id: file.name + '-' + file.size + '-' + Date.now(),
            name: file.name,
            size: file.size,
            type: file.type,
            file: file,
            progress: 0,
            status: 'pending',
            message: '',
            thumb: URL.createObjectURL(file)
        };
    });
    files.value = [...files.value, ...droppedFiles];
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const removeFile = (fileToRemove) => {
    files.value = files.value.filter(file => file.id !== fileToRemove.id);
    if (fileToRemove.thumb) {
        URL.revokeObjectURL(fileToRemove.thumb); // Libera a URL do objeto para evitar vazamento de memória
    }
};

const uploadImages = async () => {
    if (files.value.length === 0) {
        alert('Nenhuma imagem selecionada para upload.');
        return;
    }

    isUploading.value = true;
    uploadErrors.value = [];
    uploadCompleted.value = false;

    const uploadPromises = files.value.map(async (fileItem) => {
        if (fileItem.status === 'success' || fileItem.status === 'uploading') {
            return; // Já foi enviado ou está em progresso
        }

        fileItem.status = 'uploading';
        fileItem.progress = 0;
        fileItem.message = '';

        const formData = new FormData();
        formData.append('file', fileItem.file);
        formData.append('gallery_id', props.gallery.id); // Certifique-se de enviar o gallery_id

        try {
            // A rota para upload de imagens
            const response = await axios.post(
                route('fotografo.galleries.images.store', { gallery: props.gallery.id }),
                formData,
                {
                    headers: {
                        'X-Watermark-File': selectedWatermark.value || '', // Envia a marca d'água selecionada
                        'Content-Type': 'multipart/form-formdata',
                    },
                    withCredentials: true, // Importante para sessões e CSRF
                    onUploadProgress: (progressEvent) => {
                        fileItem.progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    }
                }
            );
            fileItem.status = 'success';
            fileItem.message = response.data.message || 'Upload bem-sucedido!';
        } catch (error) {
            fileItem.status = 'error';
            fileItem.progress = 0; // Resetar progresso em caso de erro
            fileItem.message = error.response?.data?.message || error.message || 'Erro desconhecido no upload.';
            uploadErrors.value.push(`Falha no upload de "${fileItem.name}": ${fileItem.message}`);
            console.error(`Erro ao enviar "${fileItem.name}":`, error.response ? error.response.data : error.message);
        }
    });

    await Promise.allSettled(uploadPromises); // Espera que todos os uploads (sucesso ou falha) terminem
    isUploading.value = false;
    uploadCompleted.value = true;

    if (uploadErrors.value.length > 0) {
        alert('Algumas imagens falharam no upload. Verifique os detalhes na tela.');
    } else {
        alert('Todas as imagens foram enfileiradas para processamento com sucesso!');
    }
};

const returnToDashboard = () => {
    window.location.href = route('fotografo.dashboard');
};
</script>

<template>
    <AppLayout>
        <Head :title="`Enviar Imagens para Galeria: ${gallery ? gallery.title : 'Carregando...'}`"/>

        <template #title>
            <h1>
                Enviar Imagens para Galeria: <span v-if="gallery">{{ gallery.title }}</span>
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="!gallery" class="text-center text-red-500">
                            Carregando detalhes da galeria ou ID inválido...
                        </div>

                        <div v-else>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Galeria Selecionada: {{ gallery.title }} (ID: {{ gallery.id }})
                            </h3>

                            <div class="mt-4">
                                <InputLabel for="watermark_select" value="Escolha a Marca D'água para o Upload:"/>
                                <select
                                    id="watermark_select"
                                    v-model="selectedWatermark"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                    :disabled="isUploading"
                                >
                                    <option :value="null" disabled>-- Sem Marca D'água --</option>
                                    <option v-for="watermark in availableWatermarks" :key="watermark.name"
                                            :value="watermark.name">
                                        {{ watermark.name }}
                                    </option>
                                    <option v-if="availableWatermarks.length === 0" :value="null" disabled>Nenhuma marca
                                        d'água disponível
                                    </option>
                                </select>
                                <p class="text-sm text-gray-500 mt-1">Esta marca d'água será aplicada a todas as
                                    imagens enviadas nesta sessão.</p>
                            </div>

                            <div class="mt-6">
                                <InputLabel value="Selecione as Imagens para Upload:"/>
                                <div
                                    class="p-6 text-center text-gray-500 border-2 border-dashed border-gray-300 rounded-md cursor-pointer hover:border-indigo-500 relative"
                                    @click="triggerFileInput"
                                    @drop.prevent="handleDrop"
                                    @dragover.prevent
                                    :class="{ 'opacity-50 cursor-not-allowed': isUploading }"
                                >
                                    <input
                                        type="file"
                                        ref="fileInput"
                                        multiple
                                        accept="image/*"
                                        @change="handleFileChange"
                                        :disabled="isUploading"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    />
                                    <p>Arraste e solte suas imagens aqui, ou clique para selecionar.</p>
                                    <p class="text-sm mt-1">Formatos aceitos: JPG, PNG. Tamanho máximo recomendado por arquivo: 10MB.</p>
                                </div>

                                <ul v-if="files.length > 0" class="mt-4 space-y-2">
                                    <li v-for="file in files" :key="file.id"
                                        class="flex items-center justify-between p-3 border rounded-md"
                                        :class="{
                                            'bg-blue-50 border-blue-200': file.status === 'uploading',
                                            'bg-green-50 border-green-200': file.status === 'success',
                                            'bg-red-50 border-red-200': file.status === 'error',
                                            'bg-gray-50 border-gray-200': file.status === 'pending',
                                        }">
                                        <div class="flex items-center flex-grow">
                                            <img v-if="file.thumb" :src="file.thumb" alt="Pré-visualização"
                                                 class="w-12 h-12 object-cover rounded mr-4"/>
                                            <div class="flex-grow">
                                                <p class="font-semibold text-gray-800 break-all">{{ file.name }}</p>
                                                <p class="text-sm text-gray-600">{{
                                                        (file.size / 1024 / 1024).toFixed(2)
                                                    }} MB</p>
                                                <div v-if="file.status === 'uploading' || file.status === 'success' || file.status === 'error'"
                                                     class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                                                    <div class="h-2.5 rounded-full"
                                                         :class="{
                                                             'bg-blue-600': file.status === 'uploading',
                                                             'bg-green-600': file.status === 'success',
                                                             'bg-red-600': file.status === 'error',
                                                         }"
                                                         :style="{ width: file.progress + '%' }"></div>
                                                    <p class="text-xs text-gray-500 mt-1">{{ file.progress }}%</p>
                                                </div>
                                                <p v-if="file.message" class="text-sm mt-1"
                                                   :class="{
                                                       'text-red-500': file.status === 'error',
                                                       'text-green-500': file.status === 'success',
                                                   }">
                                                    {{ file.message }}
                                                </p>
                                            </div>
                                        </div>
                                        <button v-if="file.status !== 'uploading' && !isUploading"
                                                @click="removeFile(file)"
                                                type="button"
                                                class="ml-4 p-2 text-red-600 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-full">
                                            X
                                        </button>
                                    </li>
                                </ul>

                                <div v-if="uploadErrors.length > 0" class="mt-4 p-4 border rounded bg-red-50">
                                    <p class="font-semibold text-red-700">❌ Erros durante o upload:</p>
                                    <ul class="list-disc ml-5 text-red-600">
                                        <li v-for="(error, index) in uploadErrors" :key="index">{{ error }}</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6 space-x-4">
                                <Link :href="route('fotografo.dashboard')"
                                      class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                      :disabled="isUploading">
                                    Voltar para o Dashboard
                                </Link>

                                <PrimaryButton @click="uploadImages" :class="{ 'opacity-25': isUploading || files.length === 0 }"
                                               :disabled="isUploading || files.length === 0">
                                    {{ isUploading ? 'Enviando Imagens...' : 'Iniciar Upload de Imagens' }}
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* Estilos específicos para o upload, se houver */
</style>
