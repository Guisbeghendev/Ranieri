<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Link } from '@inertiajs/vue3'; // Para o botão de voltar/upload

const props = defineProps({
    gallery: Object, // A galeria a ser editada
    groups: Array,    // Todos os grupos disponíveis
    selectedGroupIds: Array, // IDs dos grupos já associados à galeria
});

// Formulário Inertia para os dados da galeria
const form = useForm({
    _method: 'patch', // Importante para o Laravel entender que é uma atualização
    title: props.gallery.title,
    description: props.gallery.description,
    event_date: props.gallery.event_date,
    selected_group_ids: props.selectedGroupIds || [], // Certifique-se de ter um array
});

// Função para marcar/desmarcar grupos
const toggleGroup = (groupId) => {
    const index = form.selected_group_ids.indexOf(groupId);
    if (index === -1) {
        form.selected_group_ids.push(groupId);
    } else {
        form.selected_group_ids.splice(index, 1);
    }
};

// Função para enviar o formulário de atualização
const submit = () => {
    form.post(route('fotografo.galleries.update', props.gallery.id), {
        onSuccess: () => {
            alert('Galeria atualizada com sucesso!');
            // Redireciona de volta para a lista de galerias após o sucesso
            // Se preferir redirecionar para outro lugar, mude a rota
            // Por exemplo, para a página de upload: route('fotografo.galleries.upload-images', props.gallery.id)
        },
        onError: (errors) => {
            console.error('Erro ao atualizar galeria:', errors);
            alert('Erro ao atualizar galeria. Verifique o console.');
        },
    });
};

// Função para ir para a tela de upload de imagens da galeria atual
const goToUploadImages = () => {
    window.location.href = route('fotografo.galleries.upload-images', props.gallery.id);
};
</script>

<template>
    <AppLayout>
        <Head :title="`Editar Galeria: ${gallery.title}`" />

        <template #title>
            <h1>
                Editar Galeria: {{ gallery.title }}
            </h1>
        </template>

        <div class="py-12">
            <div class="max-w-md mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <InputLabel for="title" value="Título da Galeria" />
                                <TextInput
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.title"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="description" value="Descrição" />
                                <textarea
                                    id="description"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.description"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="event_date" value="Data do Evento" />
                                <TextInput
                                    id="event_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.event_date"
                                />
                                <InputError class="mt-2" :message="form.errors.event_date" />
                            </div>

                            <div class="mb-4">
                                <InputLabel value="Grupos" />
                                <div class="mt-2 space-y-2">
                                    <div v-for="group in groups" :key="group.id" class="flex items-center">
                                        <Checkbox
                                            :id="`group-${group.id}`"
                                            :checked="form.selected_group_ids.includes(group.id)"
                                            @change="toggleGroup(group.id)"
                                        />
                                        <label :for="`group-${group.id}`" class="ml-2 text-sm text-gray-600">{{ group.name }}</label>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.selected_group_ids" />
                            </div>

                            <div class="flex items-center justify-between mt-6">
                                <Link :href="route('fotografo.galleries.index')"
                                      class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Voltar
                                </Link>

                                <PrimaryButton :disabled="form.processing">
                                    Salvar Alterações
                                </PrimaryButton>

                                <PrimaryButton
                                    type="button"
                                    @click="goToUploadImages"
                                    class="bg-indigo-600 hover:bg-indigo-700"
                                >
                                    Fazer Upload de Imagens
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
