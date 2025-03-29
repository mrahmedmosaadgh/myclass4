<template>
    <AppLayout :title="pageTitle">
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
                                    <th class="border bg-gray-50 p-3 first-cell">Classroom</th>
                                    <th v-for="period in 8"
                                        :key="period"
                                        class="border p-3 min-w-[150px] transition-colors duration-200"
                                        :class="{ 'hover-column': hoveredCol === period }"
                                        @mouseover="hoveredCol = period"
                                        @mouseleave="hoveredCol = null">
                                        Period {{ period }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="classroom in groupedSchedules"
                                    :key="classroom.id"
                                    :class="{ 'hover-row': hoveredRow === classroom.id }"
                                    @mouseover="hoveredRow = classroom.id"
                                    @mouseleave="hoveredRow = null">

                                    <td class="border p-3 font-medium sticky-cell"
                                        :class="{ 'hover-row': hoveredRow === classroom.id }"
                                        @mouseover="setHoveredRow(classroom.id)"
                                        @mouseleave="clearHoveredRow()">
                                        {{ classroom.name }}
                                        <div class="text-xs text-gray-500">
                                            Grade: {{ classroom.grade?.name }}
                                        </div>
                                    </td>

                                    <td v-for="period in 8"
                                        :key="`${classroom.id}-${period}`"
                                        class="border p-1 relative   transition-all duration-200"
                                        :class="{
                                            'hover-column': hoveredCol === period,
                                            'hover-row': hoveredRow === classroom.id,
                                            'hover-cell': hoveredCell.row === classroom.id && hoveredCell.col === period
                                        }"
                                        @mouseover="setHoveredCell(classroom.id, period)"
                                        @mouseleave="clearHoveredCell()">

                                        <div v-if="findSchedule(classroom.id, period)"
                                             class="h-full p-2 rounded border   "
                                             :class="getScheduleCardColor(findSchedule(classroom.id, period))">
                                            <div class="flex flex-col h-full          ">
                                                <div class="flex justify-between items-start mb-1">
                                                    <span class="font-medium text-sm">
                                                        {{ findSchedule(classroom.id, period).subject?.name }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        Period {{ period }}
                                                    </span>
                                                </div>
                                                <span class="text-xs text-gray-600">
                                                    Teacher: {{ findSchedule(classroom.id, period).teacher?.name }}
                                                </span>
                                                <span class="text-xs text-gray-600">
                                                    Class: {{ classroom.name }}
                                                </span>
                                                <span v-if="findSchedule(classroom.id, period).place"
                                                      class="text-xs text-gray-500">
                                                    Place: {{ findSchedule(classroom.id, period).place }}
                                                </span>
                                                <button @click="openModal(findSchedule(classroom.id, period))"
                                                        class="mt-auto text-xs text-blue-600 hover:text-blue-800 text-right">
                                                    Edit
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else
                                             class="h-full p-1 w-full h-full flex items-center justify-center border border-dashed border-gray-300 rounded cursor-pointer hover:bg-gray-50"
                                             @click="openModal(null, { classroom_id: classroom.id, period_order: period })">
                                            <span class="text-gray-400 text-sm">+ Add Schedule</span>

                                            <!-- {{ classroom.name }} -->
<div class="p-2 absolute top-0 text-xs">

    {{ classroom?.schedules?.[0]?.['teacher']?.name  }}
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
        name: 'period_order',
        label: 'period order',
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

const groupedSchedules = computed(() => {
    const classrooms = props.options?.classrooms || [];
    return classrooms.map(classroom => ({
        ...classroom,
        grade: props.options?.grades?.find(g => g.id === classroom.grade_id),
        schedules: items.value.filter(schedule => schedule.classroom_id === classroom.id)
    }));
});

const findSchedule = (classroomId, period) => {
    return props.records.data.find(schedule =>
        schedule.classroom_id === classroomId &&
        schedule.period_order === period
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
</style>



















