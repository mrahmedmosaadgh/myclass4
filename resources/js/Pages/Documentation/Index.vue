<template>
    <AppLayout :title="'Documentation'">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- New Editor Section -->
                <!-- <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
                    <DocumentationEditor
                        :model-value="[]"
                        @update:model-value="handleEditorUpdate"
                        :locale="$page.props.locale"
                    />
                </div> -->

                <!-- Existing Content -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Documentation
                            </PrimaryButton>
                        </div>
                    </div>
                    <DataTableV2
                        :items="items"
                        :columns="tableColumns"
                        :actions="actions"
                        :searchable="true"
                        :per-page="10"
                        :total="records.total"
                        :current-page="records.current_page"
                        :last-page="records.last_page"
                        @sort="handleSort"
                        @search="handleSearch"
                        @action="handleAction"
                        @page-change="handlePageChange"
                    />
                    <DocumentationFormModal
                        v-if="modalOpen"
                        v-model:show="modalOpen"
                        v-model="form.content"
                        :editing="editing"
                        :actions="actions"
                        :locale="$page.props.locale || 'en'"
                        @close="closeModal"
                        @submitted="handleSubmit"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { toast } from 'vue3-toastify';
import { ref, computed, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTableV2 from '@/Components/Common/DataTableV2.vue';
import DocumentationFormModal from './DocumentationFormModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DocumentationEditor from './Components/DocumentationEditor.vue';
import axios from 'axios';

// Define props
const props = defineProps({
    records: {
        type: Object,
        required: true,
        default: () => ({
            data: [],
            total: 0,
            current_page: 1,
            last_page: 1
        })
    }
});

// Form state using useForm
const form = useForm({
    title: '',
    content: [],
    type: 'note',
    status: 'draft',
    tags: []
});

// Reactive references
const editorContent = ref('');
const modalOpen = ref(false);
const editing = ref(null);
const items = ref([]);

// Computed properties
const tableColumns = computed(() => [
    { key: 'title', label: 'Title', sortable: true },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Created At', sortable: true },
]);

const actions = computed(() => [
    {
        type: 'edit',
        label: 'Edit',
        icon: 'pencil',
        class: 'text-indigo-600 hover:text-indigo-900'
    },
    {
        type: 'delete',
        label: 'Delete',
        icon: 'trash',
        class: 'text-red-600 hover:text-red-900'
    }
]);

// Methods
const handleEditorUpdate = (newContent) => {
    editorContent.value = newContent;
};

const openModal = (item = null) => {
    if (item) {
        editing.value = item;
        form.reset();
        form.clearErrors();
        form.title = item.title;
        form.content = typeof item.content === 'string' ? JSON.parse(item.content) : item.content;
        form.type = item.type;
        form.status = item.status;
        form.tags = item.tags || [];
    }
    modalOpen.value = true;
};

const closeModal = () => {
    editing.value = null;
    modalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const handlePageChange = (page) => {
    router.get(route('documentation.index', { page }), {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSort = (column) => {
    router.get(route('documentation.index', {
        sort: column.key,
        direction: column.direction
    }), {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSearch = (searchTerm) => {
    router.get(route('documentation.index', {
        search: searchTerm
    }), {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleAction = async ({ type, item }) => {
    if (type === 'edit') {
        try {
            openModal(item);
        } catch (error) {
            console.error('Error preparing item for edit:', error);
            toast.error('Error preparing content for editing');
        }
    } else if (type === 'delete') {
        if (!confirm('Are you sure you want to delete this documentation?')) return;

        try {
            await axios.delete(route('documentation.destroy', item.id));
            items.value = items.value.filter(record => record.id !== item.id);
            toast.success('Documentation deleted successfully');
        } catch (error) {
            console.error('Error deleting documentation:', error);
            toast.error(error.response?.data?.message || 'Error deleting documentation');
        }
    }
};

const handleSubmit = async ({ form: formData, onSuccess, onError }) => {
    try {
        const payload = {
            title: formData.title,
            content: JSON.stringify(formData.content),
            type: formData.type,
            status: formData.status,
            tags: formData.tags
        };

        const response = await axios[editing.value ? 'put' : 'post'](
            route(`documentation.${editing.value ? 'update' : 'store'}`, editing.value?.id),
            payload
        );

        if (editing.value) {
            const index = items.value.findIndex(item => item.id === editing.value.id);
            if (index !== -1) {
                items.value[index] = response.data.record;
            }
        } else {
            items.value.unshift(response.data.record);
        }

        closeModal();
        toast.success(`Documentation ${editing.value ? 'updated' : 'created'} successfully`);
        onSuccess();
    } catch (error) {
        console.error('Submission error:', error);
        const errors = error.response?.data?.errors || { error: ['An unexpected error occurred'] };
        onError(errors);
        toast.error('Error saving documentation');
    }
};

// Lifecycle hooks
onMounted(() => {
    items.value = props.records.data;
});
</script>



















