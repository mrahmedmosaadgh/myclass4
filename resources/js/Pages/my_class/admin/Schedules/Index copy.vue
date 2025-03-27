<template>
    <AppLayout :title="pageTitle">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Schedule
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
                                button-text="Import Schedules"
                                preview-title="Preview Schedule Data"
                            />
                            <SecondaryButton @click="exportData">
                                Export
                            </SecondaryButton>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="mb-4 flex space-x-4">
                        <div class="w-1/4">
                            <SelectInput
                                v-model="filters.school_id"
                                :options="schoolOptions"
                                label="School"
                                @change="refreshData"
                            />
                        </div>
                        <div class="w-1/4">
                            <SelectInput
                                v-model="filters.grade_id"
                                :options="gradeOptions"
                                label="Grade"
                                @change="refreshData"
                            />
                        </div>
                    </div>

                    <DataTable
                        :items="items"
                        :columns="tableColumns"
                        @edit="openModal"
                        @delete="handleDelete"
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

import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import { exportToExcel } from '@/Utils/exportHelper';
import { deleteRecord } from '@/Utils/crudHelper';

// First, ensure props are properly typed and have default values
const props = defineProps({
    records: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    schools: {
        type: Array,
        default: () => []
    },
    grades: {
        type: Array,
        default: () => []
    },
    classrooms: {
        type: Array,
        default: () => []
    },
    subjects: {
        type: Array,
        default: () => []
    },
    teachers: {
        type: Array,
        default: () => []
    }
});

const pageTitle = 'Schedule Management';
const modelName = 'Schedule';
const baseUrl = '/admin/schedules';

// Add the missing computed properties
const schoolOptions = computed(() =>
    props.schools?.map(school => ({
        value: school.id,
        label: school.name
    })) || []
);

const gradeOptions = computed(() =>
    props.grades?.map(grade => ({
        value: grade.id,
        label: grade.name
    })) || []
);

const modalOpen = ref(false);
const editing = ref(null);
const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);
const submitting = ref(false);

const importColumns = [
    {
        key: 'school',
        label: 'School',
        required: true,
        description: 'Must match an existing school name'
    },
    {
        key: 'grade',
        label: 'Grade',
        required: true,
        description: 'Must match an existing grade name'
    },
    {
        key: 'classroom',
        label: 'Classroom',
        required: true,
        description: 'Must match an existing classroom name'
    },
    {
        key: 'subject',
        label: 'Subject',
        required: true,
        description: 'Must match an existing subject name'
    },
    {
        key: 'teacher',
        label: 'Teacher',
        required: true,
        description: 'Must match an existing teacher name'
    },
    {
        key: 'day',
        label: 'Day',
        required: true,
        description: 'Day of the week (1-7)'
    },
    {
        key: 'period',
        label: 'Period',
        required: true,
        description: 'Period number'
    },
    {
        key: 'place',
        label: 'Place',
        required: false,
        description: 'Location of the class'
    }
];

const tableColumns = [
    { key: 'school.name', label: 'School' },
    { key: 'grade.name', label: 'Grade' },
    { key: 'classroom.name', label: 'Classroom' },
    { key: 'subject.name', label: 'Subject' },
    { key: 'teacher.name', label: 'Teacher' },
    { key: 'day_name', label: 'Day' },
    { key: 'period', label: 'Period' },
    { key: 'place', label: 'Place' },
    { key: 'active', label: 'Status', type: 'status' },
];

const formFields = [
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: computed(() =>
            (props.schools || []).map(item => ({
                value: item.id,
                label: item.name
            }))
        )
    },
    {
        name: 'grade_id',
        label: 'Grade',
        type: 'select',
        required: true,
        options: computed(() =>
            (props.grades || []).map(item => ({
                value: item.id,
                label: item.name
            }))
        )
    },
    {
        name: 'classroom_id',
        label: 'Classroom',
        type: 'select',
        required: true,
        options: computed(() =>
            (props.classrooms || []).map(item => ({
                value: item.id,
                label: item.name
            }))
        )
    },
    {
        name: 'subject_id',
        label: 'Subject',
        type: 'select',
        required: true,
        options: computed(() =>
            (props.subjects || []).map(item => ({
                value: item.id,
                label: item.name
            }))
        )
    },
    {
        name: 'teacher_id',
        label: 'Teacher',
        type: 'select',
        required: true,
        options: computed(() =>
            (props.teachers || []).map(item => ({
                value: item.id,
                label: item.name
            }))
        )
    },
    {
        name: 'day',
        label: 'Day',
        type: 'select',
        required: true,
        options: [
            { value: 1, label: 'Monday' },
            { value: 2, label: 'Tuesday' },
            { value: 3, label: 'Wednesday' },
            { value: 4, label: 'Thursday' },
            { value: 5, label: 'Friday' },
            { value: 6, label: 'Saturday' },
            { value: 7, label: 'Sunday' }
        ]
    },
    {
        name: 'period',
        label: 'Period',
        type: 'number',
        required: true,
        min: 1
    },
    {
        name: 'place',
        label: 'Place',
        type: 'text'
    },
    {
        name: 'notes',
        label: 'Notes',
        type: 'textarea'
    },
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

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const refreshData = () => {
    axios.get(baseUrl)
        .then(response => {
            if (response.data.records) {
                items.value = response.data.records.data;
                pagination.value = response.data.records.links;
            }
        })
        .catch(error => {
            console.error('Error refreshing data:', error);
            alert('An error occurred while refreshing the data');
        });
};

const exportData = () => {
    exportToExcel({
        items: items.value,
        columns: [
            { key: 'school.name', label: 'School' },
            { key: 'grade.name', label: 'Grade' },
            { key: 'classroom.name', label: 'Classroom' },
            { key: 'subject.name', label: 'Subject' },
            { key: 'teacher.name', label: 'Teacher' },
            { key: 'day_name', label: 'Day' },
            { key: 'period', label: 'Period' },
            { key: 'place', label: 'Place' }
        ],
        fileName: 'schedules',
        sheetName: 'Schedules'
    });
};

const handleDelete = (record) => {
    deleteRecord({
        url: baseUrl,
        id: record.id,
        onSuccess: refreshData
    });
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    if (submitting.value) return;

    submitting.value = true;
    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;

    try {
        await axios.post(url, {
            ...(id && { _method: 'PUT' }),
            ...form
        });

        onSuccess();
        closeModal();
        refreshData();
    } catch (error) {
        if (error.response?.data?.errors) {
            onError(error.response.data.errors);
        } else {
            onError({ error: ['An unexpected error occurred'] });
        }
    } finally {
        submitting.value = false;
    }
};
</script>






