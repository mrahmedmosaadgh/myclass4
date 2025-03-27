<template>
    <AppLayout title="Schedule Copies">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Schedule Copies</h2>
                        <PrimaryButton @click="openModal()">Create New Copy</PrimaryButton>
                    </div>

                    <DataTable
                        :items="items"
                        :columns="tableColumns"
                        @edit="openModal"
                        @delete="deleteRecord"
                    />

                    <div v-if="pagination" class="mt-4">
                        <Pagination :links="pagination" />
                    </div>

                    <FormModal
                        :show="showModal"
                        :title="modalTitle"
                        :fields="formFields"
                        :editing="editing"
                        :errors="formErrors"
                        @close="closeModal"
                        @submitted="handleSubmit"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from './Common/DataTable.vue';
import FormModal from './Common/FormModal.vue';
import { toast } from 'vue3-toastify';
import axios from 'axios';

// Define props
const props = defineProps({
    records: {
        type: Object,
        required: true,
        default: () => ({
            data: [],
            links: null
        })
    },
    options: {
        type: Object,
        required: true,
        default: () => ({})
    }
});

// Reactive state
const showModal = ref(false);
const editing = ref(null);
const formErrors = ref({});
const items = ref(props.records.data || []);
const pagination = ref(props.records.links || null);

// Form state
const form = ref({
    name: '',
    school_id: '',
    academic_year_id: '',
    semester_id: '',
    week_number: null,
    copy_date: null,
    status: 'draft',
    active: true,
    description: '',
    notes: ''
});

// Constants
const baseUrl = '/admin/schedule-copies';

// Table configuration
const tableColumns = [
    { key: 'name', label: 'Name' },
    { key: 'school.name', label: 'School' },
    { key: 'academic_year.name', label: 'Academic Year' },
    { key: 'semester.name', label: 'Semester' },
    { key: 'week_number', label: 'Week' },
    { key: 'copy_date', label: 'Copy Date' },
    { key: 'status', label: 'Status' },
    { key: 'active', label: 'Active' }
];

// Form fields configuration
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
        options: props.options.schools || [],
        required: true
    },
    {
        name: 'academic_year_id',
        label: 'Academic Year',
        type: 'select',
        options: props.options.academicYears || [],
        required: true
    },
    {
        name: 'semester_id',
        label: 'Semester',
        type: 'select',
        options: props.options.semesters || [],
        required: true
    },
    {
        name: 'week_number',
        label: 'Week Number',
        type: 'number',
        required: true
    },
    {
        name: 'copy_date',
        label: 'Copy Date',
        type: 'date',
        required: true
    },
    {
        name: 'status',
        label: 'Status',
        type: 'select',
        options: [
            { value: 'draft', label: 'Draft' },
            { value: 'published', label: 'Published' }
        ],
        required: true
    },
    {
        name: 'active',
        label: 'Active',
        type: 'checkbox'
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
    }
];

// Computed properties
const modalTitle = computed(() => editing.value ? 'Edit Schedule Copy' : 'Create New Schedule Copy');

// Methods
const openModal = (record = null) => {
    editing.value = record;
    if (record) {
        form.value = { ...record };
    } else {
        form.value = {
            name: '',
            school_id: '',
            academic_year_id: '',
            semester_id: '',
            week_number: null,
            copy_date: null,
            status: 'draft',
            active: true,
            description: '',
            notes: ''
        };
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editing.value = null;
    formErrors.value = {};
    form.value = {
        name: '',
        school_id: '',
        academic_year_id: '',
        semester_id: '',
        week_number: null,
        copy_date: null,
        status: 'draft',
        active: true,
        description: '',
        notes: ''
    };
};

const handleSubmit = async ({ form: formData, onSuccess, onError }) => {
    const url = editing.value ? `${baseUrl}/${editing.value.id}` : baseUrl;

    try {
        const response = await axios[editing.value ? 'put' : 'post'](url, formData);
        if (response.data.records) {
            items.value = response.data.records.data;
            pagination.value = response.data.records.links;
        }
        toast.success(`Schedule copy ${editing.value ? 'updated' : 'created'} successfully`);
        closeModal();
        onSuccess();
    } catch (error) {
        const errors = error.response?.data?.errors || {};
        onError(errors);
        toast.error('Failed to save schedule copy');
    }
};

const deleteRecord = async (record) => {
    if (!confirm('Are you sure you want to delete this schedule copy?')) return;

    try {
        await axios.delete(`${baseUrl}/${record.id}`);
        items.value = items.value.filter(item => item.id !== record.id);
        toast.success('Schedule copy deleted successfully');
    } catch (error) {
        toast.error('Failed to delete schedule copy');
    }
};
</script>













