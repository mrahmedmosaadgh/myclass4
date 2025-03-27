<template>
    <AppLayout :title="pageTitle">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">{{ pageTitle }}</h2>
                        <div class="space-x-2">
                            <PrimaryButton @click="openModal()">Create New Copy</PrimaryButton>
                        </div>
                    </div>

                    <DataTableV2
                        :items="items"
                        :columns="tableColumns"
                        :actions="tableActions"
                        export-file-name="schedule_copies"
                        export-sheet-name="Schedule Copies"
                        :showToolbar="true"
                        searchable
                        sortable
                        column-toggle
                        :show-per-page="true"
                        :bulk-actions="tableBulkActions"
                        :print-settings="{
                            title: 'Schedule Copies Report',
                            showTimestamp: true,
                            timestampFormat: 'long',
                            orientation: 'landscape',
                            paperSize: 'a4',
                            fontSize: '12pt',
                            headerBgColor: '#f8f9fa',
                            borderColor: '#ddd',
                            filename: 'schedule_copies_report',
                            footerText: 'Generated from Schedule Management System',
                            columnSettings: {
                                'status': {
                                    formatter: (value) => value.charAt(0).toUpperCase() + value.slice(1)
                                },
                                'active': {
                                    formatter: (value) => value ? 'Yes' : 'No'
                                },
                                'copy_date': {
                                    formatter: (value) => new Date(value).toLocaleDateString()
                                }
                            }
                        }"
                        @action="handleAction"
                        @search="handleSearch"
                        @sort="handleSort"
                        @bulk-action="handleBulkAction"
                        @page-change="handlePageChange"
                    >
                        <!-- Optional: Custom column templates -->
                        <template #status="{ value }">
                            <span :class="getStatusClass(value)">{{ value }}</span>
                        </template>
                    </DataTableV2>
                </div>
            </div>
        </div>

        <FormModal
            :show="modalOpen"
            :title="modelName"
            :fields="formFields"
            :editing="editing"
            :form="form"
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
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTableV2 from '@/Components/Common/DataTableV2.vue';
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
const form = ref({
    name: '',
    school_id: '',
    status: '',
    week_number: ''
});

const tableColumns = [
    {
        key: 'name',
        label: 'Name',
        sortable: true
    },
    {
        key: 'school.name',
        label: 'School',
        sortable: true
    },
    {
        key: 'academic_year.name',
        label: 'Academic Year'
    },
    {
        key: 'semester.name',
        label: 'Semester'
    },
    {
        key: 'week_number',
        label: 'Week',
        sortable: true
    },
    {
        key: 'copy_date',
        label: 'Copy Date',
        sortable: true
    },
    {
        key: 'status',
        label: 'Status',
        template: true,
        class: (value) => getStatusClass(value)
    },
    {
        key: 'active',
        label: 'Active',
        formatter: (value) => value ? 'Yes' : 'No'
    }
];

const tableActions = [
    {
        type: 'create-schedule',
        label: 'Create Schedule',
        class: 'text-green-600 hover:text-green-900',
        // Only show for pending copies
        show: (item) => item.status === 'pending'
    },
    {
        type: 'edit',
        label: 'Edit',
        class: 'text-indigo-600 hover:text-indigo-900'
    },
    {
        type: 'delete',
        label: 'Delete',
        class: 'text-red-600 hover:text-red-900'
    }
];

const tableBulkActions = [
    { value: 'delete', label: 'Delete Selected' },
    { value: 'activate', label: 'Activate Selected' },
    { value: 'deactivate', label: 'Deactivate Selected' }
];

const getStatusClass = (status) => {
    const classes = {
        pending: 'text-yellow-600',
        active: 'text-green-600',
        inactive: 'text-red-600'
    };
    return classes[status] || '';
};

const formFields = computed(() => {
    // Get the first school's ID if schools exist
    const defaultSchoolId = props.options?.schools?.[0]?.id || '';

    return [
        {
            name: 'name',
            label: 'Name',
            type: 'text',
            default: generateTimestampName(),
            required: true
        },
        {
            name: 'school_id',
            label: 'School',
            type: 'select',
            default: defaultSchoolId, // Set the first school as default
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
            default: '',
            min: 1,
            max: 52
        },
        {
            name: 'status',
            label: 'Status',
            type: 'select',
            default: 'draft',
            required: true,
            options: [
                { value: 'draft', label: 'draft' },
                { value: 'pending', label: 'Pending' },
                { value: 'active', label: 'Active' },
                { value: 'inactive', label: 'Inactive' }
            ]
        }
    ];
});

const generateTimestampName = () => {
    const now = new Date();

    // Format: "copy_24-Mar-2025_03:45"
    const date = now.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    }).replace(/ /g, '-');

    const time = now.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });

    return `copy_${date}_${time}`;
};

const openModal = (record = null) => {
    editing.value = record;
    if (record) {
        form.value = { ...record };
    } else {
        // Initialize form with default values from formFields
        form.value = formFields.value.reduce((acc, field) => {
            acc[field.name] = field.default || '';
            return acc;
        }, {});
    }
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    form.value = formFields.value.reduce((acc, field) => {
        acc[field.name] = field.default || '';
        return acc;
    }, {});
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
                const firstError = Object.values(error.response.data.errors)[0];
                errorMessage = firstError[0] || errorMessage;
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }

            alert(errorMessage);
        });
    } else {
        axios.post(baseUrl, data)
        .then(() => {
            closeModal();
            refreshData();
        })
        .catch(error => {
            let errorMessage = 'An error occurred while saving the record.';

            if (error.response?.data?.errors) {
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

const handleSearch = (query) => {
    // Implement search logic
};

const handleSort = ({ key, order }) => {
    // Implement sort logic
};

const handleBulkAction = ({ action, selected }) => {
    // Implement bulk action logic
};

const handlePageChange = (page) => {
    // Implement pagination logic
};

const handleAction = ({ type, item }) => {
    switch (type) {
        case 'edit':
            openModal(item);
            break;
        case 'delete':
            deleteRecord(item);
            break;
        case 'create-schedule':
            handleCreateSchedule(item);
            break;
        default:
            console.warn(`Unhandled action type: ${type}`);
    }
};

const handleCreateSchedule = async (item) => {
    try {
        // First step: Check changes
        const checkResponse = await axios.get(`${baseUrl}/${item.id}/check-schedule-changes`);
        const changes = checkResponse.data.changes;

        // Show changes to user and ask for confirmation
        const message = `
            Changes to be made:
            - ${changes.to_delete.count} schedules will be deleted
            - ${changes.to_create.count} new schedules will be created
            - ${changes.unchanged} schedules will remain unchanged

            Do you want to proceed?
        `;

        if (confirm(message)) {
            // Second step: Execute changes
            const executeResponse = await axios.post(`${baseUrl}/${item.id}/execute-schedule-changes`);
            alert(executeResponse.data.message);
            refreshData();
        }
    } catch (error) {
        let errorMessage = 'An error occurred while processing the schedules.';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        }
        alert(errorMessage);
    }
};
</script>



































