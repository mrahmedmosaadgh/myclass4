<template>
    <AppLayout :title="pageTitle">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Calendar Entry
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
                                button-text="Import Calendar"
                                preview-title="Preview Calendar Data"
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

const pageTitle = 'Calendar Management';
const modelName = 'Calendar Entry';
const baseUrl = '/admin/calendar';

const modalOpen = ref(false);
const editing = ref(null);

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const tableColumns = [
    { key: 'date', label: 'Date' },
    { key: 'week', label: 'Week' },
    { key: 'day', label: 'Day' },
    { key: 'week_number', label: 'Week Number' },
    { key: 'semester_number', label: 'Semester' },
    { key: 'school.name', label: 'School' },
    { key: 'status', label: 'Status', type: 'select', options: props.options?.statusOptions },
    { key: 'event', label: 'Event' },
];

const formFields = [
    {
        name: 'date',
        label: 'Date',
        type: 'date',
        required: true
    },
    {
        name: 'week',
        label: 'Week',
        type: 'number',
        required: true
    },
    {
        name: 'day',
        label: 'Day',
        type: 'text',
        required: true
    },
    {
        name: 'day_number',
        label: 'Day Number',
        type: 'select',
        required: true,
        options: [
            { value: 1, label: 'Sunday' },
            { value: 2, label: 'Monday' },
            { value: 3, label: 'Tuesday' },
            { value: 4, label: 'Wednesday' },
            { value: 5, label: 'Thursday' },
            { value: 6, label: 'Friday' },
            { value: 7, label: 'Saturday' }
        ]
    },
    {
        name: 'week_number',
        label: 'Week Number',
        type: 'number',
        required: true
    },
    {
        name: 'semester_number',
        label: 'Semester Number',
        type: 'number',
        required: true
    },
    {
        name: 'academic_year_id',
        label: 'Academic Year',
        type: 'select',
        required: true,
        options: computed(() => props.options?.academicYears?.map(year => ({
            value: year.id,
            label: year.name
        })) || [])
    },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: computed(() => props.options?.schools?.map(school => ({
            value: school.id,
            label: school.name
        })) || [])
    },
    {
        name: 'status',
        label: 'Status',
        type: 'select',
        required: true,
        options: [
            { value: 1, label: 'Active' },
            { value: 0, label: 'Day Off' },
            { value: 2, label: 'Activity' },
            { value: 3, label: 'Test' },
            { value: 4, label: 'Final Exam' }
        ]
    },
    {
        name: 'event',
        label: 'Event',
        type: 'text'
    },
    {
        name: 'event_academic',
        label: 'Academic Event',
        type: 'text'
    },
    {
        name: 'vacation',
        label: 'Vacation',
        type: 'number'
    }
];

const importColumns = [
    { key: 'date', label: 'Date', required: true },
    { key: 'academic_year', label: 'Academic Year (ID or Name)', required: true },
    { key: 'school', label: 'School (ID or Name)', required: true },
    { key: 'week', label: 'Week', required: false },
    { key: 'day', label: 'Day', required: false },
    { key: 'day_number', label: 'Day Number (1-7)', required: false },
    { key: 'week_number', label: 'Week Number', required: false },
    { key: 'semester_number', label: 'Semester Number', required: false },
    { key: 'status', label: 'Status (1=Active, 0=Day Off, 2=Activity, 3=Test, 4=Final Exam)', required: false },
    { key: 'event', label: 'Event', required: false },
    { key: 'event_academic', label: 'Academic Event', required: false },
    { key: 'vacation', label: 'Vacation', required: false }
];

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const openModal = (item = null) => {
    editing.value = item;
    modalOpen.value = true;
};

const handleSubmit = ({ form }) => {
    if (editing.value) {
        router.put(`${baseUrl}/${editing.value.id}`, form);
    } else {
        router.post(baseUrl, form);
    }
    closeModal();
};

const deleteRecord = (item) => {
    if (confirm('Are you sure you want to delete this record?')) {
        router.delete(`${baseUrl}/${item.id}`);
    }
};

const exportData = () => {
    const dataToExport = items.value.map(item => ({
        date: item.date,
        week: item.week,
        day: item.day,
        day_number: item.day_number,
        week_number: item.week_number,
        semester_number: item.semester_number,
        school: item.school?.name,
        status: item.status,
        event: item.event,
        event_academic: item.event_academic,
        vacation: item.vacation
    }));

    const ws = XLSX.utils.json_to_sheet(dataToExport);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Calendar');
    XLSX.writeFile(wb, 'calendar_export.xlsx');
};

const refreshData = () => {
    router.reload();
};
</script>



