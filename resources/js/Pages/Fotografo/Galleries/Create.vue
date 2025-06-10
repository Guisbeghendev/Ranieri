<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';

import axios from 'axios'; // Manter axios para buscar watermarks e criar a galeria

const props = defineProps({
    groups: Array,
});

const form = useForm({
    title: '',
    description: '',
    event_date: '',
    selected_group_ids: [],
    selected_watermark: null,
});

const availableWatermarks = ref([]);
const processingGalleryCreation = ref(false);

const fetchWatermarks = async () => {
    try {
        const response = await axios.get(route('fotografo.galleries.watermarks.index'));
        availableWatermarks.value = response.data;
        if (availableWatermarks.value.length > 0) {
            form.selected_watermark = availableWatermarks.value[0].name;
        }
    } catch (error) {
        console.error('Erro ao buscar marcas d\'água:', error.response ? error.response.data : error.message);
    }
};

onMounted(() => {
    fetchWatermarks();
});

const submit = async () => {
    processingGalleryCreation.value = true;

    try {
        const galleryResponse = await axios.post(route('fotografo.galleries.store'), form.data());

        const responseData = galleryResponse.data;

        if (responseData && responseData.gallery_id) {
            const newGalleryId = responseData.gallery_id;
            alert(responseData.success_message || 'Galeria criada com sucesso!');

            // REDIRECIONAR PARA A NOVA PÁGINA DE UPLOAD COM O ID DA GALERIA
            // Usando Inertia.js para redirecionamento
            window.location.href = route('fotografo.galleries.upload-images', { gallery: newGalleryId });

        } else {
            alert('Erro inesperado: Galeria criada, mas ID não retornado. Redirecionando para o dashboard.');
            window.location.href = route('fotografo.dashboard'); // Redireciona para o dashboard se algo der errado no ID
        }

    } catch (error) {
        processingGalleryCreation.value = false;
        console.error('Erro ao criar galeria:', error.response ? error.response.data : error.message);

        if (error.response && error.response.status === 419) {
            alert('Sessão expirada ou erro de segurança (CSRF). Por favor, recarregue a página e tente novamente.');
            window.location.reload();
        } else if (error.response && error.response.status === 422) {
            form.errors = error.response.data.errors;
            alert('Falha ao criar galeria. Verifique os campos com erro.');
        } else {
            alert('Falha ao criar galeria. Verifique os erros no console.');
        }
    } finally {
        processingGalleryCreation.value = false;
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Criar Nova Galeria"/>

        <template #title>
            <h1>Criar Nova Galeria</h1>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="title" value="Título da Galeria"/>
                                <TextInput
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.title"
                                    required
                                    autofocus
                                    :disabled="processingGalleryCreation"
                                />
                                <InputError class="mt-2" :message="form.errors.title"/>
                            </div>

                            <div class="mt-4">
                                <InputLabel for="description" value="Descrição"/>
                                <textarea
                                    id="description"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                    v-model="form.description"
                                    rows="4"
                                    :disabled="processingGalleryCreation"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description"/>
                            </div>

                            <div class="mt-4">
                                <InputLabel for="event_date" value="Data do Evento"/>
                                <TextInput
                                    id="event_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.event_date"
                                    :disabled="processingGalleryCreation"
                                />
                                <InputError class="mt-2" :message="form.errors.event_date"/>
                            </div>

                            <div class="mt-4">
                                <InputLabel value="Visível para os seguintes grupos:"/>
                                <div class="mt-2 space-y-2">
                                    <div v-for="group in props.groups" :key="group.id" class="flex items-center">
                                        <Checkbox
                                            :id="`group-${group.id}`"
                                            :value="group.id"
                                            v-model:checked="form.selected_group_ids"
                                            :disabled="processingGalleryCreation"
                                        />
                                        <InputLabel :for="`group-${group.id}`" class="ml-2">
                                            {{ group.name }}
                                        </InputLabel>
                                    </div>
                                </div>
                                <InputError class="mt-2"
                                            :message="form.errors.selected_group_ids ? form.errors.selected_group_ids[0] : ''"/>
                            </div>

                            <div class="mt-4">
                                <InputLabel for="watermark_select" value="Escolha a Marca D'água:"/>
                                <select
                                    id="watermark_select"
                                    v-model="form.selected_watermark"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                                    :disabled="processingGalleryCreation"
                                >
                                    <option :value="null" disabled>-- Selecione uma marca d'água --</option>
                                    <option v-for="watermark in availableWatermarks" :key="watermark.name"
                                            :value="watermark.name">
                                        {{ watermark.name }}
                                    </option>
                                    <option v-if="availableWatermarks.length === 0" :value="null" disabled>Nenhuma marca
                                        d'água disponível
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.selected_watermark"/>
                            </div>

                            <div v-if="processingGalleryCreation"
                                 class="mt-4 p-4 border rounded bg-gray-50">
                                <p class="font-semibold">Status do Processo:</p>
                                <p class="text-indigo-600">
                                    Criando Galeria...
                                </p>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <PrimaryButton :class="{ 'opacity-25': processingGalleryCreation }"
                                               :disabled="processingGalleryCreation">
                                    {{ processingGalleryCreation ? 'Criando Galeria...' : 'Criar Galeria' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
.upload-area-custom-style {
    /* Sem estilos complexos. O Tailwind cuida. */
}
</style>
