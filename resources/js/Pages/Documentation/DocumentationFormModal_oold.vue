<template>
    <Modal :show="show" @close="$emit('close')" max-width="4xl">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editing ? $t('documentation.edit') : $t('documentation.new') }}
                </h2>
                <div class="flex items-center space-x-2">
                    <button
                        v-for="action in actions"
                        :key="action.name"
                        type="button"
                        :class="[
                            'inline-flex items-center px-3 py-2 border rounded-md text-sm font-medium',
                            action.variant === 'primary'
                                ? 'border-transparent text-white bg-indigo-600 hover:bg-indigo-700'
                                : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'
                        ]"
                        @click="action.handler"
                    >
                        <component
                            v-if="action.icon"
                            :is="action.icon"
                            class="h-4 w-4 mr-2"
                        />
                        {{ action.name }}
                    </button>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Title Field -->
                <div>
                    <InputLabel for="title" :value="$t('documentation.title')" />
                    <TextInput
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        :error="errors.title"
                    />
                    <InputError :message="errors.title" class="mt-2" />
                </div>

                <!-- Type Field -->
                <div>
                    <InputLabel for="type" :value="$t('documentation.type')" />
                    <SelectInput
                        id="type"
                        v-model="form.type"
                        :options="documentationTypes"
                        class="mt-1 block w-full"
                        required
                        :error="errors.type"
                    />
                    <InputError :message="errors.type" class="mt-2" />
                </div>

                <!-- Status Field -->
                <div>
                    <InputLabel for="status" :value="$t('documentation.status')" />
                    <SelectInput
                        id="status"
                        v-model="form.status"
                        :options="documentationStatuses"
                        class="mt-1 block w-full"
                        required
                        :error="errors.status"
                    />
                    <InputError :message="errors.status" class="mt-2" />
                </div>

                <!-- Tags Field -->
                <div>
                    <InputLabel for="tags" :value="$t('documentation.tags')" />
                    <TagInput
                        v-model="form.tags"
                        :placeholder="$t('documentation.enter_tags')"
                        :error="errors.tags"
                    />
                    <InputError :message="errors.tags" class="mt-2" />
                </div>

                <!-- Content Editor -->
                <div class="editor-container">
                    <InputLabel :value="$t('documentation.content')" />
                    <DocumentationEditor
                        v-model="form.content"
                        :locale="$page.props.locale"
                        :error="errors.content"
                        class="min-h-[400px]"
                    />
                    <InputError :message="errors.content" class="mt-2" />
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3">
                    <SecondaryButton @click="closeModal">
                        {{ $t('Cancel') }}
                    </SecondaryButton>
                    <PrimaryButton
                        type="submit"
                        :disabled="saving"
                        :class="{ 'opacity-75': saving }"
                    >
                        <SpinnerIcon v-if="saving" class="h-4 w-4 mr-2 animate-spin" />
                        {{ saving ? $t('Saving...') : $t('Save') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TagInput from '@/Components/TagInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DocumentationEditor from './Components/DocumentationEditor.vue';
import SpinnerIcon from '@/Components/Icons/SpinnerIcon.vue';

const { t } = useI18n();

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    editing: {
        type: Object,
        default: null
    },
    locale: {
        type: String,
        default: 'en'
    },
    actions: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'submitted']);

const saving = ref(false);
const errors = ref({});

const form = ref({
    title: '',
    content: [], // Initialize as empty array
    type: 'note',
    status: 'draft',
    tags: []
});

// Watch for editing changes and populate form
watch(() => props.editing, (newValue) => {
    if (newValue) {
        form.value = {
            title: newValue.title || '',
            content: Array.isArray(newValue.content)
                ? [...newValue.content]
                : [{ id: Date.now(), title: 'Slide 1', content: newValue.content || '' }],
            type: newValue.type || 'note',
            status: newValue.status || 'draft',
            tags: Array.isArray(newValue.tags) ? [...newValue.tags] : []
        };
    } else {
        form.value = {
            title: '',
            content: [{ id: Date.now(), title: 'Slide 1', content: '' }],
            type: 'note',
            status: 'draft',
            tags: []
        };
    }
}, { immediate: true });

const documentationTypes = [
    { value: '', label: 'Select Type' },
    { value: 'tutorial', label: 'Tutorial' },
    { value: 'guide', label: 'Guide' },
    { value: 'reference', label: 'Reference' }
];

const documentationStatuses = [
    { value: '', label: 'Select Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' }
];

const closeModal = () => {
    form.reset();
    errors.value = {};
    emit('close');
};

const handleSubmit = async () => {
    if (saving.value) return;

    try {
        saving.value = true;
        errors.value = {};

        if (!form.title.trim()) {
            errors.value.title = 'Title is required';
            return;
        }

        if (!form.content.length || !form.content.some(slide => slide.content)) {
            errors.value.content = 'At least one slide with content is required';
            return;
        }

        emit('submitted', {
            form: {
                ...form.data(),
                content: form.content // Ensure we're sending the array of slides
            },
            onSuccess: () => {
                saving.value = false;
                closeModal();
            },
            onError: (validationErrors) => {
                saving.value = false;
                errors.value = validationErrors;
            }
        });
    } catch (error) {
        console.error('Error saving documentation:', error);
        saving.value = false;
        errors.value.general = 'An unexpected error occurred';
    }
};
</script>

<style scoped>
.editor-container {
    min-height: 400px;
}

.editor-container :deep(.w-e-text-container) {
    min-height: 400px !important;
    height: 400px !important;
}

.editor-container :deep(.w-e-scroll) {
    min-height: 350px !important;
    height: 350px !important;
}

/* Ensure modal has enough space */
:deep(.modal-content) {
    max-height: 90vh;
    overflow-y: auto;
}
</style>






















