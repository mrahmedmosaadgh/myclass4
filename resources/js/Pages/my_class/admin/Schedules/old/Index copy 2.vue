<template>
    <AppLayout :title="pageTitle">
 

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-2xl font-bold">{{ pageTitle }}</h2>
                    <!-- <h2 class="text-2xl font-bold">{{ $page.props.active_copy }}</h2> -->
                    <div class="p-0" v-if="$page.props.active_copy.length==1">
                        <!-- <PrimaryButton @click="openModal()">Create New Copy</PrimaryButton> -->
                    </div>
                    <div class="p-0" v-else>
                        fix active active
                    </div>

                    <PrimaryButton @click="openModal()">Create New Schedule</PrimaryButton>

                    <div class="flex justify-between items-center mb-6">

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
    </div>


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
import CardComponent from './CardComponent.vue';
import CardComponent2 from './CardComponent2.vue';

const props = defineProps({
    records: Object,
    options: Object,
});

const pageTitle = 'Schedule Management';
const modelName = 'Schedule';
const baseUrl = '/admin/schedules';

const modalOpen = ref(false);
const editing = ref(null);
const items = ref(props.records?.data || []);
const pagination = ref(props.records?.links || null);

const tableColumns = [
    { key: 'copy.name', label: 'Copy' },
    { key: 'school.name', label: 'School' },
    { key: 'grade.name', label: 'Grade' },
    { key: 'classroom.name', label: 'Classroom' },
    { key: 'subject.name', label: 'Subject' },
    { key: 'teacher.name', label: 'Teacher' },
    { key: 'day', label: 'Day' },
    { key: 'period', label: 'Period' },
    { key: 'active', label: 'Active', type: 'status' }
];

const formFields = computed(() => [
    {
        name: 'copy_id',
        label: 'Copy',
        type: 'select',
        required: true,
        options: props.options?.copies?.map(item => ({
            value: item.id,
            label: item.name
        })) || []
    },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: props.options?.schools?.map(item => ({
            value: item.id,
            label: item.name
        })) || []
    },
    {
        name: 'grade_id',
        label: 'Grade',
        type: 'select',
        required: true,
        options: props.options?.grades?.map(item => ({
            value: item.id,
            label: item.name
        })) || []
    },
    {
        name: 'classroom_id',
        label: 'Classroom',
        type: 'select',
        required: true,
        options: props.options?.classrooms?.map(item => ({
            value: item.id,
            label: item.name
        })) || []
    },
    {
        name: 'subject_id',
        label: 'Subject',
        type: 'select',
        required: true,
        options: props.options?.subjects?.map(item => ({
            value: item.id,
            label: item.name
        })) || []
    },
    {
        name: 'teacher_id',
        label: 'Teacher',
        type: 'select',
        required: true,
        options: props.options?.teachers?.map(item => ({
            value: item.id,
            label: item.name
        })) || []
    },
    {
        name: 'day',
        label: 'Day',
        type: 'select',
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
        min: -2
    },
    {
        name: 'num',
        label: 'Number',
        type: 'number',
        min: 1
    },
    {
        name: 'name',
        label: 'Name',
        type: 'text'
    },
    {
        name: 'place',
        label: 'Place',
        type: 'text'
    },
    {
        name: 'color_custom',
        label: 'Custom Color',
        type: 'color'
    },
    {
        name: 'active',
        label: 'Status',
        type: 'select',
        options: [
            { value: true, label: 'Active' },
            { value: false, label: 'Inactive' }
        ]
    },
    {
        name: 'notes',
        label: 'Notes',
        type: 'textarea'
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
</script>

