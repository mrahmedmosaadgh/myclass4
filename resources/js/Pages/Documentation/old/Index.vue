<template>
    <AppLayout title="Documentation">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                        :locale="$page.props.locale"
                        @close="closeModal"
                        @submitted="handleSubmit"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTableV2 from '@/Components/Common/DataTableV2.vue';
import DocumentationFormModal from './DocumentationFormModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios';

const modalOpen = ref(false);
const editing = ref(null);
const items = ref([]);

const form = useForm({
    title: '',
    content: [], // Initialize as array
    type: 'note',
    status: 'draft',
    tags: ''
});

const tableColumns = computed(() => [
    { key: 'title', label: 'Title', sortable: true },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Created At', sortable: true },
]);

const actions = computed(() => [
    { label: 'Edit', icon: 'PencilIcon', action: 'edit' },
    { label: 'Delete', icon: 'TrashIcon', action: 'delete' },
]);

const openModal = () => {
    editing.value = null;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    form.reset();
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

const handleAction = ({ item, action }) => {
    if (action === 'edit') {
        editing.value = item;
        modalOpen.value = true;
    } else if (action === 'delete') {
        // Implement delete logic
        console.log('Delete:', item);
    }
};

const handleSubmit = async (formData) => {
    try {
        const payload = {
            title: formData.title,
            content: JSON.stringify(formData.content), // Convert slides array to JSON string
            type: formData.type,
            status: formData.status,
            tags: formData.tags
        };

        const response = await axios.post(route('documentation.store'), payload);

        if (response.data.message) {
            console.log(response.data.message);
        }

        closeModal();
        items.value = [...items.value, response.data.record];

    } catch (error) {
        console.error('Error submitting form:', error);
        if (error.response?.data?.errors) {
            // Show validation errors to user
            const errorMessages = Object.values(error.response.data.errors)
                .flat()
                .join('\n');
            alert(errorMessages);
        } else {
            alert('Failed to save documentation. Please try again.');
        }
    }
};

onMounted(() => {
    // The data is already available through Inertia props
    items.value = props.records.data;
});

const props = defineProps({
    records: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        required: true
    }
});

const handleSaveAllSlides = async (slides) => {
    try {
        await axios.post('/api/documentation', {
            content: slides,
            // Add any other necessary data
        });

        // Handle success (e.g., show notification, refresh data)
        closeModal();
    } catch (error) {
        console.error('Error saving slides:', error);
        // Handle error
    }
};
</script>











