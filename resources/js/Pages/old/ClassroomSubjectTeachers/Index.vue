<template>
    <AppLayout :title="pageTitle">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Assignment
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
                                button-text="Import Assignments"
                                preview-title="Preview Data"
                            />
                            <SecondaryButton @click="exportData">
                                Export
                            </SecondaryButton>
                        </div>
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
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import * as XLSX from 'xlsx';

const props = defineProps({
    records: Object,
    options: Object,
});

const pageTitle = 'Classroom Subject Teachers';
const modelName = 'Teacher Assignment';
const baseUrl = '/admin/classroom-subject-teacher';

const modalOpen = ref(false);
const editing = ref(null);

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const tableColumns = [
    { key: 'school.name', label: 'School' },
    { key: 'grade.name', label: 'Grade' },
    { key: 'classroom.name', label: 'Classroom' },
    { key: 'subject.name', label: 'Subject' },
    { key: 'teacher.name', label: 'Teacher' },
    { key: 'classes_per_week', label: 'Classes/Week' },
];

const formFields = [
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: computed(() => props.options?.schools?.map(item => ({
            value: item.id,
            label: item.name
        })) || [])
    },
    // {
    //     name: 'grade_id',
    //     label: 'Grade',
    //     type: 'select',
    //     required: true,
    //     options: computed(() => props.options?.grades?.map(item => ({
    //         value: item.id,
    //         label: item.name
    //     })) || [])
    // },
    {
        name: 'classroom_id',
        label: 'Classroom',
        type: 'select',
        required: true,
        options: computed(() => props.options?.classrooms?.map(item => ({
            value: item.id,
            label: item.name
        })) || [])
    },
    {
        name: 'subject_id',
        label: 'Subject',
        type: 'select',
        required: true,
        options: computed(() => props.options?.subjects?.map(item => ({
            value: item.id,
            label: item.name
        })) || [])
    },
    {
        name: 'teacher_id',
        label: 'Teacher',
        type: 'select',
        required: true,
        options: computed(() => props.options?.teachers?.map(item => ({
            value: item.id,
            label: item.name
        })) || [])
    },
    {
        name: 'classes_per_week',
        label: 'Classes per Week',
        type: 'number',
        required: true,
        min: 1
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
    router.reload({ only: ['records'] });
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    const url = editing.value
        ? `${baseUrl}/${editing.value.id}`
        : baseUrl;

    try {
        const response = await axios[editing.value ? 'put' : 'post'](url, form);
        onSuccess();
        refreshData();
        closeModal();
    } catch (error) {
        onError(error.response.data.errors);
    }
};

const deleteRecord = async (record) => {
    if (confirm('Are you sure you want to delete this record?')) {
        await axios.delete(`${baseUrl}/${record.id}`);
        refreshData();
    }
};

const exportData = () => {
    try {
        const wsData = [
            ['School', 'Grade', 'Classroom', 'Subject', 'Teacher', 'Classes/Week'],
            ...items.value.map(item => [
                item.school?.name || '',
                item.grade?.name || '',
                item.classroom?.name || '',
                item.subject?.name || '',
                item.teacher?.name || '',
                item.classes_per_week
            ])
        ];

        const ws = XLSX.utils.aoa_to_sheet(wsData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Teacher Assignments');

        const fileName = `teacher_assignments_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, fileName);
    } catch (error) {
        console.error('Export failed:', error);
        alert('Failed to export data');
    }
};

const importColumns = [
    {
        key: 'school',
        label: 'School Name',
        required: true,
        description: 'Must match an existing school name'
    },
    {
        key: 'grade',
        label: 'Grade Name',
        required: true,
        description: 'Must match an existing grade name'
    },
    {
        key: 'classroom',
        label: 'Classroom Name',
        required: true,
        description: 'Must match an existing classroom name'
    },
    {
        key: 'subject',
        label: 'Subject Name',
        required: true,
        description: 'Must match an existing subject name'
    },
    {
        key: 'teacher',
        label: 'Teacher Name',
        required: true,
        description: 'Must match an existing teacher name'
    },
    {
        key: 'classes_per_week',
        label: 'Classes per Week',
        required: true,
        type: 'number',
        description: 'Number of classes per week'
    }
];
</script>
