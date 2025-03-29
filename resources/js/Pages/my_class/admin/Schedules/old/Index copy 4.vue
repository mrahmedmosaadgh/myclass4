<template>
    <AppLayout :title="pageTitle">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-2xl font-bold">{{ pageTitle }}</h2>

                    <div class="p-0" v-if="$page.props.active_copy.length==1">
                        <PrimaryButton @click="openModal()">Create New Schedule</PrimaryButton>
                    </div>
                    <div class="p-0" v-else>
                        <div class="text-red-600">Please fix active copy status</div>
                    </div>

                    <!-- Schedule Grid View -->
                    <div class="mt-6">
                        <div v-for="classroom in groupedSchedules" :key="classroom.id" class="mb-8">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">
                                {{ classroom.name }}
                                <span class="text-sm text-gray-600">
                                    (Grade: {{ classroom.grade?.name }})
                                </span>
                            </h3>

                            <div class="grid grid-cols-8 gap-4">
                                <!-- Period Headers -->
                                <div v-for="period in 8" :key="period"
                                    class="text-center font-medium text-gray-600 bg-gray-100 p-2 rounded">
                                    Period {{ period }}
                                </div>

                                <!-- Schedule Cards -->
                                <template v-for="period in 8" :key="period">
                                    <div class="min-h-[120px] relative schedule-slot"
                                         :class="{ 'available': isSlotAvailable(classroom.id, period) }"
                                         @click="isSlotAvailable(classroom.id, period) && handleSchedulePlacement(classroom.id, period)">
                                        <div v-if="findSchedule(classroom.id, period)"
                                             class="absolute inset-0 p-3 rounded-lg shadow-sm border"
                                             :class="getScheduleCardColor(findSchedule(classroom.id, period))">
                                            <div class="flex flex-col h-full">
                                                <span class="font-medium text-sm">
                                                    {{ findSchedule(classroom.id, period).subject?.name }}
                                                </span>
                                                <span class="text-xs text-gray-600">
                                                    {{ findSchedule(classroom.id, period).teacher?.name }}
                                                </span>
                                                <span v-if="findSchedule(classroom.id, period).place"
                                                      class="text-xs text-gray-500">
                                                    {{ findSchedule(classroom.id, period).place }}
                                                </span>
                                                <div class="mt-auto flex justify-end">
                                                    <button @click="openModal(findSchedule(classroom.id, period))"
                                                            class="text-xs text-blue-600 hover:text-blue-800">
                                                        Edit
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else
                                             class="absolute inset-0 border border-dashed border-gray-300 rounded-lg flex items-center justify-center empty-slot"
                                             @click="openModal(null, { classroom_id: classroom.id, period: period })">
                                            <span class="text-gray-400 text-sm">+ Add Schedule</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
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

const groupedSchedules = computed(() => {
    const classrooms = props.options?.classrooms || [];
    return classrooms.map(classroom => ({
        ...classroom,
        grade: props.options?.grades?.find(g => g.id === classroom.grade_id),
        schedules: items.value.filter(schedule => schedule.classroom_id === classroom.id)
    }));
});

const findSchedule = (classroomId, period) => {
    return items.value.find(schedule =>
        schedule.classroom_id === classroomId &&
        schedule.period === period
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

const openModal = (record = null) => {
    editing.value = record ? { ...record } : null;
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
    return !items.value.some(schedule =>
        schedule.classroom_id === classroomId &&
        schedule.period_order === period
    );
};
</script>

<style scoped>
.grid-cols-8 {
    grid-template-columns: repeat(8, minmax(0, 1fr));
}
</style>







