<template>
    <Modal :show="show" @close="closeModal" :maxWidth="'2xl'">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ editing ? `Edit ${title}` : `Create New ${title}` }}
            </h2>

            <form @submit.prevent="submitForm" class="mt-6">
                <div class="space-y-6">
                    <div v-for="field in fields" :key="field.name" class="mb-4">
                        <label :for="field.name" class="block text-sm font-medium text-gray-700">
                            {{ field.label }}
                            <span v-if="field.required" class="text-red-500">*</span>
                        </label>

                        <!-- Text, Number, and Date inputs -->
                        <input
                            v-if="['text', 'number', 'date'].includes(field.type)"
                            v-model="form[field.name]"
                            :type="field.type"
                            :id="field.name"
                            :min="field.min"
                            :max="field.max"
                            :required="field.required"
                            :placeholder="field.placeholder"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >

                        <!-- Select input -->
                        <select
                            v-else-if="field.type === 'select'"
                            :id="field.name"
                            v-model="form[field.name]"
                            :required="field.required"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="">Select {{ field.label }}</option>
                            <option
                                v-for="option in field.options"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>

                        <!-- Checkbox input -->
                        <div v-else-if="field.type === 'checkbox'" class="mt-1">
                            <label class="inline-flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="form[field.name]"
                                    :id="field.name"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                <span class="ml-2 text-sm text-gray-600">{{ field.help || field.label }}</span>
                            </label>
                        </div>

                        <!-- Textarea input -->
                        <textarea
                            v-else-if="field.type === 'textarea'"
                            v-model="form[field.name]"
                            :id="field.name"
                            :required="field.required"
                            :rows="field.rows || 3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        ></textarea>

                        <!-- Field error message -->
                        <p v-if="errors[field.name]" class="mt-1 text-sm text-red-600">
                            {{ errors[field.name] }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton type="button" @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton type="submit" :disabled="submitting">
                        {{ submitting ? 'Saving...' : 'Save' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    title: {
        type: String,
        required: true
    },
    fields: {
        type: Array,
        required: true
    },
    editing: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'submitted']);

const submitting = ref(false);
const errors = ref({});
const form = ref({});

// Initialize form with default values
const initForm = () => {
    const newForm = {};
    props.fields.forEach(field => {
        // If editing, use the existing value
        if (props.editing && props.editing[field.name] !== undefined) {
            newForm[field.name] = props.editing[field.name];
        } else {
            // Otherwise use the default value or empty string
            newForm[field.name] = field.default !== undefined ? field.default : '';
        }
    });
    form.value = newForm;
    errors.value = {};
};

// Watch for show prop changes
watch(() => props.show, (newVal) => {
    if (newVal) {
        initForm();
    }
}, { immediate: true });

// Watch for editing prop changes
watch(() => props.editing, () => {
    if (props.show) {
        initForm();
    }
});

const closeModal = () => {
    submitting.value = false;
    errors.value = {};
    emit('close');
};

const submitForm = () => {
    submitting.value = true;
    emit('submitted', {
        form: form.value,
        onSuccess: () => {
            submitting.value = false;
            closeModal();
        },
        onError: (validationErrors) => {
            submitting.value = false;
            errors.value = validationErrors;
        }
    });
};
</script>

