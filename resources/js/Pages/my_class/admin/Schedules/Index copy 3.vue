<template>

    <AppLayout :title="pageTitle">
        <details>
            {{ $page.props.auth.user.school}}
        </details>
        <FilterSelectV2
            v-model="selected_school"
            v-model:object="selected_school_object"

            :options="$page.props.auth.user.school"
            value-key="id"

                :label-key="['name', 'hr.name' ]"
            placeholder="Select School"
            label-separator=" - "
            :default-selected-index="0"
            :label_only="false"
            />
            selected_school_object:{{ selected_school_object.name }}
<hr>
<FilterSelectV2
            v-model="user_data.classroom"
            v-model:object="user_data.classroom_object"

            :options="$page.props.auth.user.classroom"
            value-key="id"

                :label-key="['name' ]"
            placeholder="Select School"
            label-separator=" - "
            :default-selected-index="0"
            :label_only="false"
            />
            user_data.classroom_object{{ user_data.classroom_object }}
<details>
<pre>

    {{ $page.props.auth.user.classroom  }}
</pre>

</details>
<details>
<pre>

    {{ $page.props.auth.user.schedule  }}
</pre>

</details>







<hr>
<FilterSelectV2
            v-model="user_data.schedule"
            v-model:object="user_data.schedule_object"

            :options="$page.props.auth.user.schedule"
            value-key="id"

                :label-key="[
                    'cst.classroom_name',
                    'cst.subject_name',
                    'cst.teacher_name',
                    'period_order'

                 ]"
            placeholder="Select School"
            label-separator=" - "
            :default-selected-index="0"
            :label_only="false"
            />


{{ user_data.schedule_object }}



        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            <FilterSelectV2
            v-model="user_data.classroom"
            v-model:object="user_data.classroom_object"

            :options="$page.props.auth.user.classroom"
            value-key="id"

                :label-key="['name' ]"
            placeholder="Select School"
            label-separator=" - "
            :default-selected-index="0"
            :label_only="false"
            />
                <WeeklySchedule
                    :schedules="$page.props.auth.user.schedule"
                />
            </div>

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
                                    <th class="first-cell" rowspan="2">Class</th>
                                    <th v-for="day in days" :key="day.value"
                                        :colspan="8"
                                        class="border text-center bg-gray-50 p-2">
                                        {{ day.label }}
                                    </th>
                                </tr>
                                <tr>
                                    <template v-for="day in days" :key="`periods-${day.value}`">
                                        <th v-for="periodNum in 8"
                                            :key="`${day.value}-${periodNum}`"
                                            class="border transition-colors duration-200"
                                            :class="{ 'hover-column': hoveredCol === `${day.value}-${periodNum}` }"
                                            @mouseover="hoveredCol = `${day.value}-${periodNum}`"
                                            @mouseleave="hoveredCol = null">
                                            {{ periodNum }}
                                        </th>
                                    </template>
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

                                    <template v-for="day in days" :key="`${classroom.id}-${day.value}`">
                                        <td v-for="periodNum in 8"
                                            :key="`${classroom.id}-${day.value}-${periodNum}`"
                                            class="border w-8 p-1 relative"
                                            :class="{
                                                'hover-column': hoveredCol === `${day.value}-${periodNum}`,
                                                'hover-row': hoveredRow === classroom.id,
                                                'hover-cell': hoveredCell.row === classroom.id && hoveredCell.col === `${day.value}-${periodNum}`
                                            }"
                                            @mouseover="setHoveredCell(classroom.id, `${day.value}-${periodNum}`)"
                                            @mouseleave="clearHoveredCell()">

                                            <div v-if="findSchedule(classroom.id, periodNum, day.value)"
                                                 class="h-full p-1 rounded border"
                                                 :class="getScheduleCardColor(findSchedule(classroom.id, periodNum, day.value))">
                                                <div class="flex flex-col h-full gap-0.5">
                                                    <div class="flex justify-between items-start">
                                                        <span class="font-medium text-xs">
                                                            {{ findSchedule(classroom.id, periodNum, day.value).cst?.subject?.name }}
                                                        </span>
                                                        <span class="text-[10px] text-gray-500">
                                                            {{ periodNum }}
                                                        </span>
                                                    </div>
                                                    <span class="text-[11px] text-gray-600">
                                                        {{ findSchedule(classroom.id, periodNum, day.value).cst?.teacher?.name }}
                                                    </span>
                                                    <span class="text-[11px] text-gray-600">
                                                        {{ findSchedule(classroom.id, periodNum, day.value).cst?.classroom?.name }}
                                                    </span>
                                                    <span v-if="findSchedule(classroom.id, periodNum, day.value).place"
                                                          class="text-[11px] text-gray-500">
                                                        {{ findSchedule(classroom.id, periodNum, day.value).place }}
                                                    </span>
                                                    <button @click="openModal(findSchedule(classroom.id, periodNum, day.value), {
                                                        classroom_id: classroom.id,
                                                        period_number: periodNum,
                                                        school_id: selected_school_object.value?.id,
                                                        day: day.value
                                                    })"
                                                            class="mt-0.5 text-[10px] text-blue-600 hover:text-blue-800 text-right">
                                                        Edit
                                                    </button>
                                                </div>
                                            </div>
                                            <div v-else
                                                 class="p-1 w-full h-full flex items-center justify-center border border-dashed border-gray-300 rounded cursor-pointer hover:bg-gray-50"
                                                 @click="openModal(null, {
                                                     classroom_id: classroom.id,
                                                     period_number: periodNum,
                                                     day: day.value
                                                 })">
                                                <span class="text-gray-400 text-sm">+ </span>
                                            </div>
                                        </td>
                                    </template>
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
import Example from '@/Components/Example.vue';
import DialogModal_8 from './DialogModal_8.vue';
import WeeklySchedule from '@/Components/Schedule/WeeklySchedule.vue';
import FilterSelectV2 from '@/Components/FilterSelectV2.vue';
// resources\js\Components\Example.vue
// import CardComponent from './CardComponent.vue';
// import CardComponent2 from './CardComponent2.vue';
import NameAbbreviator from '@/Components/Common/NameAbbreviator.vue';
const props = defineProps({
    records: Object,
    options: Object,
});

const baseUrl = '/admin/schedules';
const pageTitle = 'Schedule Management';
const modelName = 'Schedule';
const selected_school = ref(null);
const user_data = ref({});
const selected_school_object = ref({});
const modalOpen = ref(false);
const editing = ref(null);
const submitting = ref(false);
const days = [
    { value: 1, label: 'Sunday' },
    { value: 2, label: 'Monday' },
    { value: 3, label: 'Tuesday' },
    { value: 4, label: 'Wednesday' },
    { value: 5, label: 'Thursday' }
];

// Update the getSchedule function to match the day numbering
const getSchedule = (day, period) => {
    return props.schedules.find(s =>
        s.day === day &&
        s.period_number === period &&
        s.active
    );
};
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

const findSchedule = (classroomId, period, day) => {
    return items.value.find(schedule =>
        schedule.cst?.classroom_id === classroomId &&
        schedule.period_number === period &&
        schedule.day === day &&
        schedule.active
    );
};

const getScheduleCardColor = (schedule) => {
    if (!schedule || !schedule.cst) return '';

    return {
        'bg-blue-50': schedule.cst.subject?.type === 'regular',
        'bg-green-50': schedule.cst.subject?.type === 'special',
        // Add more color conditions as needed
    };
};

const openModal = (existingSchedule = null, defaultData = {}) => {
    if (existingSchedule) {
        // Editing existing schedule
        editing.value = {
            cst_id: existingSchedule.cst_id,
            id: existingSchedule.id
        };
    } else {
        // Creating new schedule
        editing.value = {
            cst_id: null,
            school_id: selected_school_object.value?.id,
            period_number: defaultData.period_number,
            day: defaultData.day
        };
    }
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
            // Update existing schedule
            await axios.put(`/api/schedules/${id}`, {
                cst_id: form.cst_id
            });
        } else {
            // Create new schedule
            await axios.post('/api/schedules', {
                cst_id: form.cst_id,
                school_id: editing.value.school_id,
                period_number: editing.value.period_number,
                day: editing.value.day
            });
        }

        closeModal();
        onSuccess();
    } catch (error) {
        onError(error);
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
        period_number: period,
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
        .map(schedule => schedule.period_number);

    // Assuming 8 periods per day
    return Array.from({length: 8}, (_, i) => i + 1)
        .filter(period => !occupiedPeriods.includes(period));
};

// Add this to your template where you want to show available slots
const isSlotAvailable = (classroomId, period) => {
    return !props.records.data.some(schedule =>
        schedule.classroom_id === classroomId &&
        schedule.period_number === period
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

// Add these refs at the top of your script setup
const selectedCstIndex = ref(null);
const defaultScheduleData = computed(() => ({
    school_id: selected_school_object.value?.id,
    period_number: null,
    day: null,

    cst_id: null,
    place: '',
    active: true,
    notes: ''
}));
const periods = ref([1, 2, 3, 4, 5, 6, 7, 8]);
</script>

<style scoped>
.first-cell {
    position: sticky;
    left: 0;
    background-color: white;
    z-index: 10;
}

thead th {
    background-color: #f9fafb;
    font-weight: 600;
    text-align: center;
    padding: 0.5rem;
}

.border {
    border: 1px solid #e5e7eb;
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



















