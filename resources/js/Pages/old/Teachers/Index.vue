<template>
    <AppLayout :title="pageTitle">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ pageTitle }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div v-if="$page.props.flash?.success"
                         class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ $page.props.flash.success }}
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <div class="flex space-x-2">
                            <PrimaryButton @click="openModal()">
                                Add New Teacher
                            </PrimaryButton>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :columns="importColumns"
                                button-text="Import Teachers"
                                preview-title="Preview Teacher Data"
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
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import axios from 'axios';
import ImportExcel from '@/Components/Common/ImportExcel.vue';

const props = defineProps({
    records: {
        type: Object,
        required: true
    },
    schools: {
        type: Array,
        required: true
    }
});

const items = computed(() => props.records?.data || []);
const pagination = computed(() => props.records?.links || null);

const pageTitle = 'Teachers Management';
const modelName = 'Teacher';
const baseUrl = '/admin/teacher';

const tableColumns = [
    { key: 'name', label: 'Name' },
    { key: 'name_ar', label: 'Arabic Name' },
    { key: 'school.name', label: 'School' },
    { key: 'email', label: 'Email' },
    { key: 'phone_number', label: 'Phone' },
    { key: 'gender', label: 'Gender' },
    { key: 'actions', label: 'Actions' }
];

const schoolOptions = computed(() =>
    props.schools.map(school => ({
        value: school.id,
        label: school.name
    }))
);

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true
    },
    {
        name: 'name_ar',
        label: 'Arabic Name',
        type: 'text'
    },
    // {
    //     name: 't_id',
    //     label: 'Teacher ID',
    //     type: 'text',
    //     required: true
    // },
    {
        name: 'school_id',
        label: 'School',
        type: 'select',
        required: true,
        options: schoolOptions
    },
    {
        name: 'email',
        label: 'Email',
        type: 'email'
    },
    {
        name: 'phone_number',
        label: 'Phone Number',
        type: 'text'
    },
    {
        name: 'whatsapp_number',
        label: 'WhatsApp Number',
        type: 'text'
    },
    {
        name: 'gender',
        label: 'Gender',
        type: 'select',
        options: [
            { value: 'male', label: 'Male' },
            { value: 'female', label: 'Female' }
        ]
    },
    {
        name: 'nationality',
        label: 'Nationality',
        type: 'text'
    },
    {
        name: 'date_of_birth',
        label: 'Date of Birth',
        type: 'date'
    },
    {
        name: 'address',
        label: 'Address',
        type: 'textarea'
    },
    {
        name: 'notes',
        label: 'Notes',
        type: 'textarea'
    }
];

const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
};

const handleSubmit = ({ form, onSuccess, onError }) => {
    if (submitting.value) return; // Prevent double submission

    submitting.value = true;
    NProgress.start();

    const id = editing.value?.id;
    const url = id ? `${baseUrl}/${id}` : baseUrl;

    axios.post(url, {
        ...(id && { _method: 'PUT' }),
        ...form
    })
        .then(response => {
            onSuccess();
            closeModal();
            refreshData();
        })
        .catch(error => {
            if (error.response?.data?.errors) {
                onError(error.response.data.errors);
            } else {
                onError({ error: ['An unexpected error occurred'] });
            }
        })
        .finally(() => {
            submitting.value = false;
            NProgress.done();
        });
};

const deleteRecord = (record) => {
    if (!confirm('Are you sure you want to delete this record?')) return;

    NProgress.start();

    axios.delete(`${baseUrl}/${record.id}`)
        .then(() => {
            refreshData();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the record.');
        })
        .finally(() => {
            NProgress.done();
        });
};

const refreshData = () => {
    router.reload({
        only: ['records'],
        preserveScroll: true,
        preserveState: true
    });
};

const exportData = () => {
    window.location.href = `${baseUrl}/export`;
};

const importColumns = [
    // { key: 'id', label: 'ID' },
    { key: 'name', label: 'Name' },
    // { key: 'email', label: 'Email' },
    // { key: 'phone', label: 'Phone' },
    { key: 'school', label: 'school' }
    // { key: 'subject', label: 'Subject' }
];
</script>







