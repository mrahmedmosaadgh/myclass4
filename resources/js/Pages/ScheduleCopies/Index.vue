<template>
    <AppLayout :title="pageTitle">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">{{ pageTitle }}</h2>
                        <PrimaryButton @click="openModal()">Create New Copy</PrimaryButton>
                    </div>

                    <DataTable
                        :items="items"
                        :columns="tableColumns"
                        @edit="openModal"
                        @delete="deleteRecord"
                    />

                    <div class="mt-4" v-if="pagination">
                        <Pagination :links="pagination" />
                    </div>
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
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';

const props = defineProps({
    records: Object,
    options: Object,
});

const pageTitle = 'Schedule Copies Management';
const modelName = 'Schedule Copy';
const baseUrl = '/admin/schedule-copies';

const modalOpen = ref(false);
const editing = ref(null);
const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const tableColumns = [
    { key: 'name', label: 'Name' },
    { key: 'school.name', label: 'School' },
    { key: 'academic_year.name', label: 'Academic Year' },
    { key: 'semester.name', label: 'Semester' },
    { key: 'week_number', label: 'Week' },
    { key: 'copy_date', label: 'Copy Date' },
    { key: 'status', label: 'Status' },
    { key: 'active', label: 'Active', type: 'status' }
];

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true
    },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: props.options?.schools || []
    },
    {
        name: 'academic_year_id',
        label: 'Academic Year',
        type: 'select',
        required: true,
        options: props.options?.academicYears || []
    },
    {
        name: 'semester_id',
        label: 'Semester',
        type: 'select',
        options: props.options?.semesters || []
    },
    {
        name: 'week_number',
        label: 'Week Number',
        type: 'number'
    },
    {
        name: 'copy_date',
        label: 'Copy Date',
        type: 'date'
    },
    {
        name: 'status',
        label: 'Status',
        type: 'select',
        required: true,
        options: props.options?.statusOptions || []
    },
    {
        name: 'description',
        label: 'Description',
        type: 'textarea'
    },
    {
        name: 'notes',
        label: 'Notes',
        type: 'textarea'
    },
    {
        name: 'active',
        label: 'Active',
        type: 'checkbox'
    }
];

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const handleSubmit = (formData) => {
    if (editing.value) {
        router.put(`${baseUrl}/${editing.value.id}`, formData);
    } else {
        router.post(baseUrl, formData);
    }
    closeModal();
};

const deleteRecord = (record) => {
    if (confirm('Are you sure you want to delete this record?')) {
        router.delete(`${baseUrl}/${record.id}`);
    }
};

const refreshData = () => {
    router.reload({ only: ['records'] });
};
</script>