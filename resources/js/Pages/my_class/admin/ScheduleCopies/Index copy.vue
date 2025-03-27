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
import axios from 'axios';
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
const items = ref(props.records?.data || []);
const pagination = ref(props.records?.links || null);

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

const formFields = computed(() => [
    {
        name: 'name',
        label: 'Name',
        type: 'text',

        required: true
    },
    {
        name: 'useTimestamp',
        label: 'Use Timestamp as Name',
        type: 'checkbox',
        value: false, // Add this to ensure it's treated as a checkbox
        help: 'Automatically generate name using timestamp',
        displayAsText: false // Add this to ensure it's not displayed as text
    },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: props.options?.schools?.map(school => ({
            value: school.id,
            label: school.name
        })) || []
    },
    {
        name: 'week_number',
        label: 'Week Number',
        type: 'number',
        min: 1,
        max: 52
    }
]);

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const handleSubmit = (formData) => {
    // Extract the actual form data from the nested structure
    const data = formData.form || formData;

    if (editing.value) {
        axios.post(`${baseUrl}/${editing.value.id}`, {
            _method: 'PUT',
            ...data
        })
        .then(() => {
            closeModal();
            refreshData();
        })
        .catch(error => {
            let errorMessage = 'An error occurred while saving the record.';

            if (error.response?.data?.errors) {
                // Get the first error message
                const firstError = Object.values(error.response.data.errors)[0];
                errorMessage = firstError[0] || errorMessage;
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }

            alert(errorMessage);
        });
    } else {
        // Add timestamp to name if requested
        if (data.useTimestamp) {
            const now = new Date();
            const timestamp = now.toISOString().slice(0, 19).replace(/[-:]/g, '').replace('T', '_');
            data.name = `copy_${timestamp}`;
            delete data.useTimestamp; // Remove the flag before sending to server
        }

        axios.post(baseUrl, data)
        .then(() => {
            closeModal();
            refreshData();
        })
        .catch(error => {
            let errorMessage = 'An error occurred while saving the record.';

            if (error.response?.data?.errors) {
                // Get the first error message
                const firstError = Object.values(error.response.data.errors)[0];
                errorMessage = firstError[0] || errorMessage;
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }

            alert(errorMessage);
        });
    }
};

const deleteRecord = (record) => {
    if (!confirm('Are you sure you want to delete this record?')) return;

    axios.delete(`${baseUrl}/${record.id}`)
        .then(() => {
            refreshData();
        })
        .catch(error => {
            let errorMessage = 'An error occurred while deleting the record.';

            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }

            alert(errorMessage);
        });
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
            alert('An error occurred while refreshing the data.');
        });
};
</script>













