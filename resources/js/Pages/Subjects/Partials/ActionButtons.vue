<template>
    <div class="flex space-x-2">
        <PrimaryButton @click="$emit('add')">
            <PlusIcon class="h-5 w-5 mr-2" />
            Add New Subject
        </PrimaryButton>

        <ImportExcel
            @imported="$emit('import')"
            :validate-url="baseUrl + '/validate-import'"
            :import-url="baseUrl + '/import'"
            :undo-url="baseUrl + '/undo-import'"
            :columns="importColumns"
            button-text="Import Subjects"
            preview-title="Preview Subject Data"
        />

        <SecondaryButton @click="$emit('export')">
            <DocumentDownloadIcon class="h-5 w-5 mr-2" />
            Export
        </SecondaryButton>
    </div>
</template>

<script setup>
import { PlusIcon, DocumentDownloadIcon } from '@heroicons/vue/solid';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';

defineProps({
    baseUrl: String,
    importColumns: Array
});

defineEmits(['add', 'import', 'export']);
</script>
