<template>
    <Modal :show="show" @close="closeModal" :maxWidth="'4xl'">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ editing ? 'Edit Documentation' : 'Add New Documentation' }}
            </h2>

            <form @submit.prevent="submitForm">
                <!-- Title Input -->
                <div class="mb-4">
                    <InputLabel for="title" value="Title" />
                    <TextInput
                        id="title"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.title"
                        required
                    />
                    <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <!-- Type Selection -->
                <div class="mb-4">
                    <InputLabel for="type" value="Type" />
                    <SelectInput
                        id="type"
                        class="mt-1 block w-full"
                        v-model="form.type"
                        :options="typeOptions"
                    />
                    <InputError :message="form.errors.type" class="mt-2" />
                </div>

                <!-- Status Selection -->
                <div class="mb-4">
                    <InputLabel for="status" value="Status" />
                    <SelectInput
                        id="status"
                        class="mt-1 block w-full"
                        v-model="form.status"
                        :options="statusOptions"
                    />
                    <InputError :message="form.errors.status" class="mt-2" />
                </div>

                <!-- Editor Container with fixed height -->
                <div class="mb-4" style="min-height: 400px;">
                    <InputLabel value="Content" class="mb-2" />
                    <DocumentationEditor
                        v-model="form.content"
                        :locale="locale"
                    />
                    <InputError :message="form.errors.content" class="mt-2" />
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end mt-4">
                    <SecondaryButton @click="closeModal" class="mr-2">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="form.processing">
                        {{ editing ? 'Update' : 'Create' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DocumentationEditor from './Components/DocumentationEditor.vue';
import { initEditor } from '@/Utils/editorConfig';

// Initialize editor plugins
initEditor();

const props = defineProps({
    show: Boolean,
    editing: Object,
    locale: {
        type: String,
        default: 'en'
    }
});

const emit = defineEmits(['close', 'update:show']);

const form = useForm({
    title: '',
    content: [],
    type: 'note',
    status: 'draft'
});

const typeOptions = [
    { value: 'note', label: 'Note' },
    { value: 'document', label: 'Document' },
    { value: 'tutorial', label: 'Tutorial' }
];

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'archived', label: 'Archived' }
];

watch(() => props.editing, (newValue) => {
    if (newValue) {
        form.title = newValue.title;
        form.content = typeof newValue.content === 'string'
            ? JSON.parse(newValue.content)
            : newValue.content;
        form.type = newValue.type;
        form.status = newValue.status;
    } else {
        form.reset();
    }
}, { immediate: true });

const closeModal = () => {
    emit('close');
    emit('update:show', false);
    form.reset();
};

const submitForm = () => {
    if (props.editing) {
        form.put(route('documentation.update', props.editing.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('documentation.store'), {
            onSuccess: () => closeModal(),
        });
    }
};
</script>

<style>
.w-e-scroll {
    min-height: 300px !important;
}
</style>

