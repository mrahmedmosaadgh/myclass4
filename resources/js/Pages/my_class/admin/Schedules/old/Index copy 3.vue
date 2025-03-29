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
                        <div v-for="grade in groupedSchedules" :key="grade.id" class="mb-8">
                            <h3 class="text-xl font-semibold mb-4 text-gray-800">
                                {{ grade.name }}
                            </h3>

                            <div class="grid grid-cols-8 gap-4">
                                <!-- Period Headers -->
                                <div v-for="period in 8" :key="period"
                                    class="text-center font-medium text-gray-600 bg-gray-100 p-2 rounded">
                                    Period {{ period }}
                                </div>

                                <!-- Schedule Cards -->
                                <template v-for="period in 8" :key="period">
                                    <div class="min-h-[120px] relative">
                                        <div v-if="findSchedule(grade.id, period)"
                                            class="absolute inset-0 p-3 rounded-lg shadow-sm border"
                                            :class="getScheduleCardColor(findSchedule(grade.id, period))">
                                            <div class="flex flex-col h-full">
                                                <span class="font-medium text-sm">
                                                    {{ findSchedule(grade.id, period).subject?.name }}
                                                </span>
                                                <span class="text-xs text-gray-600">
                                                    {{ findSchedule(grade.id, period).teacher?.name }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ findSchedule(grade.id, period).classroom?.name }}
                                                </span>
                                                <div class="mt-auto flex justify-end">
                                                    <button @click="openModal(findSchedule(grade.id, period))"
                                                        class="text-xs text-blue-600 hover:text-blue-800">
                                                        Edit
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else
                                            class="absolute inset-0 border border-dashed border-gray-300 rounded-lg flex items-center justify-center"
                                            @click="openModal(null, { grade_id: grade.id, period: period })">
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

const groupedSchedules = computed(() => {
    const grades = props.options?.grades || [];
    return grades.map(grade => ({
        ...grade,
        schedules: items.value.filter(schedule => schedule.grade_id === grade.id)
    }));
});

const findSchedule = (gradeId, period) => {
    return items.value.find(schedule =>
        schedule.grade_id === gradeId &&
        schedule.period === period
    );
};

const getScheduleCardColor = (schedule) => {
    if (!schedule) return '';

    // You can customize these colors based on subject or any other criteria
    const colors = {
        'Mathematics': 'bg-blue-50 border-blue-200',
        'Science': 'bg-green-50 border-green-200',
        'English': 'bg-purple-50 border-purple-200',
        'default': 'bg-gray-50 border-gray-200'
    };

    return colors[schedule.subject?.name] || colors.default;
};

const openModal = (record = null, defaultValues = {}) => {
    editing.value = record;
    if (!record && defaultValues) {
        editing.value = defaultValues;
    }
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

<style scoped>
.grid-cols-8 {
    grid-template-columns: repeat(8, minmax(0, 1fr));
}
</style>


