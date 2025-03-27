<template>
    <AppLayout :title="pageTitle">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ pageTitle }}
            </h2>
        </template>
        <ImportExcel
            @imported="refreshData"
            :validate-url="baseUrl + '/validate-import'"
            :import-url="baseUrl + '/import'"
            :columns="importColumns"
            button-text="Import Subjects"
            preview-title="Preview Subject Data"
        />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <FlashMessage
                        v-if="$page.props.flash?.success"
                        :message="$page.props.flash.success"
                        class="mb-4"
                    />

                    <ActionButtons
                        @add="openModal"
                        @import="refreshData"
                        @export="exportData"
                        :base-url="baseUrl"
                        :import-columns="importColumns"
                    />

                    <FilterSection
                        v-model:search="filters.search"
                        v-model:school="filters.school_id"
                        v-model:status="filters.status"
                        :school-options="schoolOptions"
                        @search="debounceSearch"
                        @filter="refreshData"
                    />

                    <DataTable
                        :items="items"
                        :columns="tableColumns"
                        :loading="loading"
                        :current-page="currentPage"
                        :per-page="perPage"
                        @edit="openModal"
                        @delete="confirmDelete"
                    />

                    <Pagination
                        v-if="pagination"
                        :links="pagination"
                        class="mt-4"
                    />
                </div>
            </div>
        </div>

        <FormModal
            :show="modalOpen"
            :title="modelName"
            :fields="formFields"
            :editing="editing"
            @close="closeModal"
            @submitted="handleSubmit"
        />

        <ConfirmationModal
            :show="showDeleteModal"
            @close="showDeleteModal = false"
            @confirmed="deleteRecord"
        >
            <template #title>Delete Subject</template>
            <template #content>
                Are you sure you want to delete this subject? This action cannot be undone.
            </template>
            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ms-3"
                    @click="deleteRecord"
                >
                    Delete
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { exportToExcel } from '@/Utils/exportHelper';
import ImportExcel from '@/Components/Common/ImportExcel.vue';

// Component imports
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import ActionButtons from './Partials/ActionButtons.vue';
import FilterSection from './Partials/FilterSection.vue';
import Pagination from '@/Components/Pagination.vue';

// Props
const props = defineProps({
    records: Object,
    schools: Array
});

// State
const modalOpen = ref(false);
const showDeleteModal = ref(false);
const editing = ref(null);
const loading = ref(false);
const selectedRecord = ref(null);
const filters = ref({
    search: '',
    school_id: '',
    status: ''
});

// Constants
const pageTitle = 'Subjects Management';
const modelName = 'Subject';
const baseUrl = '/admin/subject'; // Make sure this matches your actual route prefix

// Computed
const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);
const currentPage = computed(() => props.records?.current_page || 1);
const perPage = computed(() => props.records?.per_page || 10);

const schoolOptions = computed(() => [
    { value: '', label: 'All Schools' },
    ...(props.schools || []).map(school => ({
        value: school.id,
        label: school.name
    }))
]);

const tableColumns = [
    { key: 'name', label: 'Name' },
    { key: 'nour_name', label: 'Nour Name' },
    { key: 'nour_id', label: 'Nour ID' },
    { key: 'school.name', label: 'School' },
    {
        key: 'active',
        label: 'Status',
        formatter: (value) => value ? 'Active' : 'Inactive',
        class: (value) => value ? 'text-green-600' : 'text-red-600'
    }
];

const formFields = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'nour_name', label: 'Nour Name', type: 'text', required: true },
    { name: 'nour_id', label: 'Nour ID', type: 'text' },
    { name: 'school_id', label: 'School', type: 'select', required: true, options: schoolOptions },
    { name: 'description', label: 'Description', type: 'textarea' },
    { name: 'notes', label: 'Notes', type: 'textarea' },
    {
        name: 'active',
        label: 'Status',
        type: 'select',
        options: [
            { value: true, label: 'Active' },
            { value: false, label: 'Inactive' }
        ]
    }
];

const importColumns = [
    { key: 'name', label: 'Name', required: true },
    { key: 'nour_name', label: 'Nour Name', required: true },
    // { key: 'nour_id', label: 'Nour ID' },
    { key: 'school', label: 'School', required: true },
    // { key: 'description', label: 'Description' },
    // { key: 'notes', label: 'Notes' },
    { key: 'active', label: 'Status' }
];

// Methods
const refreshData = () => {
    loading.value = true;
    router.get(route('admin.subject.index'), {
        ...filters.value,
        preserveState: true,
        preserveScroll: true,
        only: ['records']
    });
};

const debounceSearch = debounce(refreshData, 300);

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const confirmDelete = (record) => {
    selectedRecord.value = record;
    showDeleteModal.value = true;
};

const deleteRecord = () => {
    if (!selectedRecord.value) return;

    router.delete(`${baseUrl}/${selectedRecord.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedRecord.value = null;
        }
    });
};

const handleSubmit = ({ form, onSuccess, onError }) => {
    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;
    const method = id ? 'put' : 'post';

    router[method](url, form, {
        onSuccess: () => {
            onSuccess();
            closeModal();
        },
        onError
    });
};

const exportData = () => {
    exportToExcel({
        items: items.value,
        columns: [
            { key: 'name', label: 'Name' },
            { key: 'nour_name', label: 'Nour Name' },
            { key: 'nour_id', label: 'Nour ID' },
            { key: 'school.name', label: 'School' },
            { key: 'description', label: 'Description' },
            { key: 'notes', label: 'Notes' },
            { key: 'active', label: 'Status' }
        ],
        fileName: 'subjects',
        sheetName: 'Subjects'
    });
};
</script>







