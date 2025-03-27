<template>
    <DataTableV4
        :items="items"
        :columns="columns"
        :actions="tableActions"
        @action="handleAction"
    />
</template>

<script setup>
import { ref } from 'vue';
import DataTableV4 from './DataTableV4.vue';

const columns = [
    {
        key: 'id',
        label: 'ID',
        sortable: true
    },
    {
        key: 'name',
        label: 'Name',
        sortable: true
    },
    // Add other columns as needed
];

const tableActions = [
    {
        type: 'edit',
        label: 'Edit',
        icon: 'pencil',
        action: (item) => {
            // Your edit logic
        }
    },
    {
        type: 'delete',
        label: 'Delete',
        icon: 'trash-2',
        action: (item) => {
            // Your delete logic
        }
    },
    {
        type: 'view',
        label: 'View',
        icon: 'eye',
        action: (item) => {
            // Your view logic
        }
    }
];

const handleAction = ({ type, item }) => {
    switch (type) {
        case 'edit':
            // Handle edit
            break;
        case 'delete':
            // Handle delete
            break;
        case 'view':
            // Handle view
            break;
    }
};
</script>

