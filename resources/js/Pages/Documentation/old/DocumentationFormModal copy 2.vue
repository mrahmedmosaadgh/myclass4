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
                        <input id="title" v-model="form.title" type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                    </div>

                    <!-- Content Field -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Content</label>
                        <div style="border: 1px solid #ccc; z-index: 100;">
                            <Toolbar style="border-bottom: 1px solid #ccc" :editor="editorRef" :defaultConfig="toolbarConfig"
                                :mode="mode" />
                            <Editor style="height: 300px; overflow-y: hidden;" v-model="form.content"
                                :defaultConfig="editorConfig" :mode="mode" @onCreated="handleCreated"
                                @onChange="handleChange" />
                        </div>
                    </div>

                    <!-- Type Field -->
                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                        <select id="type" v-model="form.type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                            <option value="">Select Type</option>
                            <option v-for="(label, value) in types" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Status Field -->
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" v-model="form.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                            <option value="">Select Status</option>
                            <option v-for="(label, value) in statuses" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
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
import '@wangeditor/editor/dist/css/style.css'
import { Editor, Toolbar } from '@wangeditor/editor-for-vue'
import { ref, shallowRef, onBeforeUnmount, watch } from 'vue'
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
    type: 'note', // Set a default value that matches the enum
    status: 'draft',
    tags: ''
});

// Get CSRF token safely
const getCsrfToken = () => {
    const tokenElement = document.querySelector('meta[name="csrf-token"]');
    return tokenElement ? tokenElement.getAttribute('content') : '';
};

// Editor config
const editorConfig = {
    placeholder: 'Please enter content...',
    MENU_CONF: {
        uploadImage: {
            customUpload: (file, insertFn) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const base64Url = e.target.result;
                    insertFn(base64Url);
                };
                reader.readAsDataURL(file);
            }
        }
    }
};

// Editor instance
const editorRef = shallowRef();

// Toolbar config
const toolbarConfig = {
    excludeKeys: []
};

// Editor mode
const mode = 'default';

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

// Handling editor creation
const handleCreated = (editor) => {
    editorRef.value = editor;
};

// Handling content changes
const handleChange = (editor) => {
    form.value.content = editor.getHtml();
};

// Cleanup
onBeforeUnmount(() => {
    const editor = editorRef.value;
    if (editor == null) return;
    editor.destroy();
});
</script>

<style>
.w-e-text-container {
    background-color: #fff !important;
}
</style>







