<template>
    <Modal :show="show" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ editing ? 'Edit Documentation' : 'New Documentation' }}
            </h2>

            <form @submit.prevent="submitForm" class="mt-6">
                <div class="space-y-6">
                    <!-- Title Field -->
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                    </div>

                    <!-- Content Field -->
                    <div class="mb-4">
                        <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                        <textarea
                            id="content"
                            v-model="form.content"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        ></textarea>
                    </div>

                    <!-- Type Field -->
                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                        <select
                            id="type"
                            v-model="form.type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                            <option v-for="(label, value) in types" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Status Field -->
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                            <option v-for="(label, value) in statuses" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Tags Field -->
                    <div class="mb-4">
                        <label for="tags" class="block text-sm font-medium text-gray-700">Tags</label>
                        <input
                            id="tags"
                            v-model="form.tags"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Enter tags separated by commas"
                        >
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton type="submit" :disabled="submitting">
                        {{ submitting ? 'Saving...' : (editing ? 'Update' : 'Create') }}
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
    show: Boolean,
    editing: Object,
    types: Object,
    statuses: Object
});

const emit = defineEmits(['close', 'submitted']);

const submitting = ref(false);
const form = ref({
    title: '',
    content: '',
    type: '',
    status: '',
    tags: ''
});

watch(() => props.editing, (newVal) => {
    if (newVal) {
        form.value = { ...newVal };
    } else {
        form.value = {
            title: '',
            content: '',
            type: '',
            status: '',
            tags: ''
        };
    }
}, { immediate: true });

const closeModal = () => {
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
        onError: () => {
            submitting.value = false;
        }
    });
};
</script>