<template>
    <AppLayout :title="pageTitle">

<!-- {{ props.options }} -->
        <!-- props:

        <pre>
            {{ props }}
        </pre> -->
        groupedSchedules:{{ groupedSchedules }}
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">{{ pageTitle }}</h2>

                    <div class="mb-4" v-if="$page.props.active_copy.length==1">
                        <PrimaryButton @click="openModal()">Create New Schedule</PrimaryButton>
                    </div>
                    <div v-else class="mb-4">
                        <div class="text-red-600">Please fix active copy status</div>
                    </div>

                    <!-- Schedule Table -->
                    <div class="table-wrapper">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr>
                                    <th class="first-cell">Class</th> <!-- Shortened from "Classroom" -->
                                    <th v-for="period in 8"
                                        :key="period"
                                        class="border transition-colors duration-200"
                                        :class="{ 'hover-column': hoveredCol === period }"
                                        @mouseover="hoveredCol = period"
                                        @mouseleave="hoveredCol = null">
                                        {{ period }} <!-- Just the number -->
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="classroom in groupedSchedules"
                                    :key="classroom.id"
                                    :class="{ 'hover-row': hoveredRow === classroom.id }"
                                    @mouseover="hoveredRow = classroom.id"
                                    @mouseleave="hoveredRow = null">

                                    <td class="border w-8 p-1 font-medium sticky-cell"
                                        :class="{ 'hover-row': hoveredRow === classroom.id }"
                                        @mouseover="setHoveredRow(classroom.id)"
                                        @mouseleave="clearHoveredRow()">
                                        {{ classroom.name }}
                                        <div class="text-xs  text-gray-500">
                                            Grade: {{ classroom.grade?.name }}
                                        </div>
                                    </td>

                                    <td v-for="period in 8"
                                        :key="`${classroom.id}-${period}`"
                                        class="border w-8 p-1 relative    "
                                        :class="{
                                            'hover-column': hoveredCol === period,
                                            'hover-row': hoveredRow === classroom.id,
                                            'hover-cell': hoveredCell.row === classroom.id && hoveredCell.col === period
                                        }"
                                        @mouseover="setHoveredCell(classroom.id, period)"
                                        @mouseleave="clearHoveredCell()">

<div class="p-1 "
:style="`background-color: ${findSchedule(classroom.id, period)?.cst?.color_custom};`"
>

                                       {{ findSchedule(classroom.id, period)?.cst
?.teacher?.name }}
</div>





                                        <div v-if="findSchedule(classroom.id, period)"
                                             class="h-full p-1 rounded border"
                                             :class="getScheduleCardColor(findSchedule(classroom.id, period))">
                                            <div class="flex flex-col h-full gap-0.5">
                                                <div class="flex justify-between items-start">
                                                    <span class="font-medium text-xs">
                                                        {{ findSchedule(classroom.id, period).subject?.name }}
                                                    </span>
                                                    <span class="text-[10px] text-gray-500">
                                                        {{ period }}
                                                    </span>
                                                </div>
                                                <span class="text-[11px] text-gray-600">
                                                    {{ findSchedule(classroom.id, period).teacher?.name }}
                                                </span>
                                                <span class="text-[11px] text-gray-600">
                                                    {{ classroom.name }}
                                                </span>
                                                <span v-if="findSchedule(classroom.id, period).place"
                                                      class="text-[11px] text-gray-500">
                                                    {{ findSchedule(classroom.id, period).place }}
                                                </span>
                                                <button @click="openModal(findSchedule(classroom.id, period))"
                                                        class="mt-0.5 text-[10px] text-blue-600 hover:text-blue-800 text-right">
                                                    Edit
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else
                                             class="  p-1 w-full h-full flex items-center justify-center border border-dashed border-gray-300 rounded cursor-pointer hover:bg-gray-50"
                                             @click="openModal(null, { classroom_id: classroom.id, period_order: period })">
                                            <span class="text-gray-400 text-sm">+ </span>

                                            <!-- {{ classroom.name }} -->
<div class="p-2 absolute top-0 text-xs overflow-visible w-8">

    {{ findSchedule(classroom.id, period)?.teacher?.name }}
</div>
                                                <!-- <details v-show="classroom?.schedules?.length>0">

                                                </details> -->


                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



        <DialogModal_8
            :show="modalOpen"
            :title="editing ? 'Edit Schedule' : 'Add New Schedule'"
            :fields="formFields"
            :formData="editing"
            @close="closeModal"
            @submitted="handleSubmit"
        >
            <template #title>
                {{ editing ? 'Edit Schedule' : 'Add New Schedule' }}
            </template>

            <template #content>
                <div class="space-y-4">
                    <div v-for="field in formFields" :key="field.name" class="grid grid-cols-1 gap-2">
                        <label :for="field.name" class="block text-sm font-medium text-gray-700">
                            {{ field.label }}
                        </label>
                        <select
                            v-if="field.type === 'select'"
                            :id="field.name"
                            v-model="editing[field.name]"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Select {{ field.label }}</option>
                            <option
                                v-for="option in field.options"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <textarea
                            v-else-if="field.type === 'textarea'"
                            :id="field.name"
                            v-model="editing[field.name]"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="3"
                        ></textarea>
                        <input
                            v-else
                            :type="field.type"
                            :id="field.name"
                            v-model="editing[field.name]"
                            :min="field.min"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="closeModal">
                    Cancel
                </SecondaryButton>
                <PrimaryButton
                    class="ml-3"
                    :disabled="submitting"
                    @click="handleSubmit({ form: editing })"
                >
                    {{ submitting ? 'Saving...' : (editing?.id ? 'Update' : 'Create') }}
                </PrimaryButton>
            </template>
        </DialogModal_8>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import DialogModal_8 from './DialogModal_8.vue';
import CardComponent from './CardComponent.vue';
import CardComponent2 from './CardComponent2.vue';

const props = defineProps({
    records: Object,
    options: Object,
});

const baseUrl = '/admin/schedules';
const pageTitle = 'Schedule Management';
const modelName = 'Schedule';

const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);
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
        name: 'cst_id',
        label: 'Class-Subject-Teacher',
        type: 'select',
        required: true,
        options: props.options?.csts?.map(item => ({
            value: item.id,
            label: `${item.classroom.name} - ${item.subject.name} - ${item.teacher.name}`
        })) || []
    },
    {
        name: 'period_order',
        label: 'Period',
        type: 'number',
        min: 1
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

const groupedSchedules = computed(() => {
    const csts = props.options?.csts || [];
    const uniqueClassrooms = [...new Set(csts.map(cst => cst.classroom))];

    return uniqueClassrooms.map(classroom => ({
        ...classroom,
        grade: classroom.grade,
        schedules: items.value.filter(schedule =>
            schedule.cst?.classroom_id === classroom.id
        )
    }));
});

const findSchedule = (classroomId, period) => {
    console.log(classroomId, period);
    console.log('props', props);
    console.log('props.records[0]', props.records[0]);

    if (!props.records ) return null;

    return props.records.find(schedule =>
        schedule.cst?.classroom_id === classroomId &&
        schedule?.period_detail?.sequence === period
    );
};

const getScheduleCardColor = (schedule) => {
    if (!schedule) return '';

    if (schedule.color_custom) {
        return `bg-${schedule.color_custom}-50 border-${schedule.color_custom}-200`;
    }

    // Default color scheme based on subjects
    const colors = {
        'Mathematics': 'bg-blue-50 border-blue-200',
        'Science': 'bg-green-50 border-green-200',
        'English': 'bg-purple-50 border-purple-200',
        'default': 'bg-gray-50 border-gray-200'
    };

    return colors[schedule.subject?.name] || colors.default;
};

const openModal = (record = null, defaults = {}) => {
    editing.value = record ? { ...record } : { ...defaults };
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
    editing.value = null;
    submitting.value = false;
};

const handleSubmit = async ({ form, onSuccess, onError }) => {
    if (submitting.value) return;

    submitting.value = true;
    const id = editing.value?.id;

    try {
        if (id) {
            // Update existing record
            await axios.post(`${baseUrl}/${id}`, {
                _method: 'PUT',
                ...form
            });
        } else {
            // Create new record
            await axios.post(baseUrl, form);
        }

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

const handleSchedulePlacement = (classroomId, period) => {
    const formData = {
        ...form.value,
        classroom_id: classroomId,
        period_order: period,
        copy_id: activeCopy.value.id,
    };

    axios.post(baseUrl, formData)
        .then(response => {
            refreshData();
            // Show success message
            toast.success('Schedule placed successfully');
        })
        .catch(error => {
            let errorMessage = 'Failed to place schedule';

            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }

            // Show error message
            toast.error(errorMessage);

            if (error.response?.data?.conflict) {
                // Optionally show conflict details
                console.log('Conflict details:', error.response.data.conflict);
            }
        });
};

// Helper function to find available periods for a classroom
const getAvailablePeriods = (classroomId) => {
    const occupiedPeriods = items.value
        .filter(schedule => schedule.classroom_id === classroomId)
        .map(schedule => schedule.period_order);

    // Assuming 8 periods per day
    return Array.from({length: 8}, (_, i) => i + 1)
        .filter(period => !occupiedPeriods.includes(period));
};

// Add this to your template where you want to show available slots
const isSlotAvailable = (classroomId, period) => {
    return !props.records.data.some(schedule =>
        schedule.classroom_id === classroomId &&
        schedule.period_order === period
    );
};

// Add these new refs for hover effects
const hoveredRow = ref(null);
const hoveredCol = ref(null);
const hoveredCell = ref({ row: null, col: null });

const setHoveredCell = (row, col) => {
    hoveredCell.value = { row, col };
    hoveredRow.value = row;
    hoveredCol.value = col;
};

const clearHoveredCell = () => {
    hoveredCell.value = { row: null, col: null };
    hoveredRow.value = null;
    hoveredCol.value = null;
};

// Add these methods for better hover control
const setHoveredRow = (id) => {
    hoveredRow.value = id;
};

const clearHoveredRow = () => {
    hoveredRow.value = null;
};
</script>

<style scoped>
.border {
    @apply border-gray-200;
}

/* Base transitions */
td, th {
    position: relative;
    transition: background-color 0.2s ease;
}

.table-wrapper {
    @apply relative overflow-x-auto overflow-y-auto max-h-[80vh];
}

table {
    @apply bg-gradient-to-br from-white to-gray-50;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border-collapse: separate;
    border-spacing: 0;
}

/* Sticky header */
thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    background: white;
    @apply bg-gradient-to-b from-gray-50 to-gray-100;
}

/* Corner cell (intersection of sticky header and column) */
.first-cell {
    position: sticky;
    left: 0;
    z-index: 30;
}

/* Sticky first column */
.sticky-cell {
    position: sticky;
    left: 0;
    z-index: 10;
    background: white;
    transition: all 0.2s ease;
}

/* Row hover effect */
.hover-row {
    @apply bg-gradient-to-r from-purple-50/80 to-pink-50/80 !important;
    box-shadow: inset 0 0 12px rgba(219, 39, 119, 0.05);
}

/* Ensure sticky cell shows hover state */
.sticky-cell.hover-row {
    @apply bg-gradient-to-r from-purple-50 to-purple-50/90 !important;
}

/* Column hover effect */
.hover-column,
td:nth-child(n+2):nth-child(-n+9):has(~ tr td.hover-column) {
    @apply bg-gradient-to-b from-indigo-50/80 to-blue-50/80;
    box-shadow: inset 0 0 12px rgba(99, 102, 241, 0.05);
}

/* Cell hover effect */
.hover-cell {
    @apply bg-gradient-to-br from-cyan-50 to-blue-50;
    box-shadow:
        inset 0 0 15px rgba(6, 182, 212, 0.1),
        0 0 10px rgba(6, 182, 212, 0.1);
    z-index: 15;
}

/* Add shadows for visual separation */
thead th {
    box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.1),
                0 2px 4px rgba(0, 0, 0, 0.05);
}

.sticky-cell {
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
}

.sticky-cell.hover-row {
    box-shadow:
        2px 0 4px rgba(0, 0, 0, 0.05),
        inset 0 0 12px rgba(219, 39, 119, 0.05);
}

/* Schedule card styles */
.h-full.p-2.rounded.border {
    transition: all 0.3s ease;
}

.h-full.p-2.rounded.border:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Base cell sizing - significantly reduced widths */
td, th {
    @apply p-1.5; /* Further reduced padding */
    min-width: 90px; /* Significantly reduced from 120px */
    max-width: 120px; /* Reduced from 180px */
}

/* First column (classroom names) sizing */
.sticky-cell, .first-cell {
    @apply p-1.5;
    min-width: 100px; /* Reduced from 140px */
    max-width: 120px; /* Reduced from 160px */
}

/* Compact content styling */
.text-xs {
    @apply mt-0.5 text-[11px]; /* Smaller text */
    line-height: 1.2;
}

/* More compact card styling */
.h-full.p-2.rounded.border {
    @apply p-1; /* Minimal padding */
}

/* Ensure text wrapping for long content */
td, th {
    white-space: normal; /* Allow text wrapping */
    font-size: 0.875rem; /* Slightly smaller font */
    line-height: 1.25;
}

/* Schedule card content */
.flex.flex-col.h-full {
    @apply gap-0.5; /* Minimal gap between elements */
}

/* Adjust font sizes in cards */
.font-medium.text-sm {
    @apply text-xs;
}

.text-gray-500, .text-gray-600 {
    @apply text-[11px];
}
</style>

