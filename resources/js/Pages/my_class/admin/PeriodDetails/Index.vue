<template>
    <AppLayout title="Period Details">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <h2 class="text-2xl font-bold">Period Details</h2>
                        <PrimaryButton @click="openModal()">Add New</PrimaryButton>
                    </div>

                    <DataTable
                        :columns="tableColumns"
                        :items="items"
                        :loading="false"
                        @edit="openModal"
                        @delete="deleteRecord"
                    />

                    <Pagination v-if="pagination" :links="pagination" />
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
import DataTable from '@/Components/DataTable.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormModal from '@/Components/FormModal.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    records: Object,
    options: Object
});

const items = computed(() => props.records.data);
const pagination = computed(() => props.records.links);
const modalOpen = ref(false);
const editing = ref(null);
const modelName = 'Period Detail';

const tableColumns = [
    { key: 'sequence', label: 'Sequence' },
    { key: 'code', label: 'Code' },
    { key: 'name', label: 'Name' },
    { key: 'from', label: 'From' },
    { key: 'to', label: 'To' },
    { key: 'main', label: 'Main', type: 'boolean' },
    { key: 'actions', label: 'Actions' }
];

const formFields = [
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
        name: 'code',
        label: 'Code',
        type: 'number',
        required: true,
        min: 1
    },
    {
        name: 'sequence',
        label: 'Sequence',
        type: 'number',
        required: true,
        min: 1
    },
    {
        name: 'name',
        label: 'Name',
        type: 'text'
    },
    {
        name: 'main',
        label: 'Main',
        type: 'checkbox'
    },
    {
        name: 'time_before',
        label: 'Time Before (minutes)',
        type: 'number',
        min: 0
    },
    {
        name: 'from',
        label: 'From',
        type: 'time'
    },
    {
        name: 'to',
        label: 'To',
        type: 'time'
    },
    {
        name: 'notes',
        label: 'Notes',
        type: 'textarea'
    }
];

const openModal = (record = null) => {
    editing.value = record;
    modalOpen.value = true;
};

const closeModal = () => {
    editing.value = null;
    modalOpen.value = false;
};

const handleSubmit = (formData) => {
    if (editing.value) {
        useForm(formData).put(route('period-details.update', editing.value.id));
    } else {
        useForm(formData).post(route('period-details.store'));
    }
    closeModal();
};

const deleteRecord = (record) => {
    if (confirm('Are you sure you want to delete this period detail?')) {
        useForm().delete(route('period-details.destroy', record.id));
    }
};
</script>