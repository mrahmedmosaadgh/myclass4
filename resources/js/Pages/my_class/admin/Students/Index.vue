<template>
    <AppLayout title="Students">
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
                                Add New Student
                            </PrimaryButton>
                            <div class="mb-6">
                                <SecondaryButton @click="showColumnManager = !showColumnManager">
                                    Manage Import Columns
                                </SecondaryButton>

                                <div   class="mt-4">
                                    <ColumnManager

                                        :show="showColumnManager"
                                        v-model:columns="importColumns"
                                        @close="showColumnManager = false"
                                    />
                                </div>
                            </div>
                            <ImportExcel
                                @imported="refreshData"
                                :validate-url="baseUrl + '/validate-import'"
                                :import-url="baseUrl + '/import'"
                                :undo-url="baseUrl + '/undo-import'"
                                :columns="importColumns"
                                button-text="Import Students"
                                preview-title="Preview Student Data"
                            />
                            <SecondaryButton @click="handleExport('excel')">
                                Export Excel
                            </SecondaryButton>
                            <SecondaryButton @click="handleExport('csv')">
                                Export CSV
                            </SecondaryButton>
                        </div>
                    </div>
<button @click="first()">first</button>
                    <StudentFilters
                        :schools="localSchools"
                        @filter-applied="handleFilterApplied"
                    />

                    <DataTable
                        :columns="tableColumns"
                        :items="items"
                        :loading="false"
                        @edit="openModal"
                        @delete="deleteRecord"
                    />

                    <Pagination
                        v-if="pagination"
                        :links="pagination"
                    />
                </div>
            </div>
        </div>

        <FormModal
            :show="showModal"
            :fields="formFields"
            :title="modalTitle"
            :errors="formErrors"
            :submitting="submitting"
            @close="closeModal"
            @submitted="submitForm"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import DataTable from '@/Components/Common/DataTable.vue';
import FormModal from '@/Components/Common/FormModal.vue';
import ImportExcel from '@/Components/Common/ImportExcel.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import * as XLSX from 'xlsx';
import StudentFilters from '@/Components/Students/StudentFilters.vue';
import ColumnManager from '@/Components/Common/ColumnManager.vue';
import { exportData } from '@/Utils/exportHelper';

const formErrors = ref({});
const submitting = ref(false);

const showColumnManager = ref(false);
const importColumns = ref([
    { key: 's_id', label: 'ID', required: false, is_id: true },
    { key: 'name', label: 'Name', required: true },
    { key: 'name_ar', label: 'Arabic Name', required: true },
    { key: 'school', label: 'School', required: true },
    { key: 'classroom', label: 'Classroom', required: true },
    { key: 'grade', label: 'Grade', required: true },
    { key: 'stage', label: 'Stage', required: true }
]);

// Optional: Save column order to localStorage
watch(importColumns, (newColumns) => {
    localStorage.setItem('importColumnsOrder', JSON.stringify(newColumns));
}, { deep: true });

// Optional: Load saved column order on component mount
onMounted(() => {
    const savedColumns = localStorage.getItem('importColumnsOrder');
    if (savedColumns) {
        importColumns.value = JSON.parse(savedColumns);
    }
});

const props = defineProps({
    records: {
        type: Object,
        required: true
    },
    schools: {
        type: Array,
        required: true,
        default: () => []
    },
    userRoles: {
        type: Array,
        required: true,
        default: () => []
    },
    permissions: {
        type: Object,
        required: true,
        default: () => ({})
    }
});

const baseUrl = '/admin/students';
const showModal = ref(false);
const editingId = ref(null);
const form = ref({
    name: '',
    name_ar: '',
    name_cute: '',
    notes: '',
    school_id: '',
    stage_id: '',
    grade_id: '',
    classroom_id: ''
});

const tableColumns = [
    { key: 's_id', label: 'ID' },
    { key: 'name', label: 'Name' },
    { key: 'name_ar', label: 'Arabic Name' },
    { key: 'school.name', label: 'School' },
    { key: 'classroom.name', label: 'Classroom' },
    { key: 'grade.name', label: 'Grade' },
    { key: 'stage.name', label: 'Stage' }
];

const localRecords = ref(props.records);
const localSchools = ref(props.schools);
const localUserRoles = ref(props.userRoles);
const localPermissions = ref(props.permissions);

const items = ref([]);
const pagination = ref(null);

const modalTitle = computed(() => editingId.value ? 'Edit Student' : 'Add New Student');
const first = ()=>{
             selectedSchool.value   =1
             selectedStage.value    =1
             selectedGrade.value    =1
             selectedClassroom.value=1



    const filters = {
        school_id: 1,
        stage_id: 1,
        grade_id: 1,
        classroom_id: 1
    };
    handleFilterApplied(filters)


};


const openModal = (item = null) => {
    formErrors.value = {};

    if (!selectedSchool.value) {
        alert('Please select a school first');
        return;
    }

    if (item) {
        editingId.value = item.id;
        form.value = {
            ...item,
            school_id: selectedSchool.value,
            stage_id: selectedStage.value || item.stage_id,
            grade_id: selectedGrade.value || item.grade_id
        };
    } else {
        editingId.value = null;
        form.value = {
            name: '',
            name_ar: '',
            name_cute: '',
            notes: '',
            school_id: selectedSchool.value,
            stage_id: selectedStage.value || '',
            grade_id: selectedGrade.value || '',
            classroom_id: ''
        };
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingId.value = null;
    formErrors.value = {}; // Reset errors when closing modal
};

const submitForm = (formData1) => {

var formData = formData1.form;
    if (submitting.value) return;

    submitting.value = true;
    formErrors.value = {};

    const url = editingId.value ? `${baseUrl}/${editingId.value}` : baseUrl;

    // Include all required fields in the request
    const requestData = {
        ...(editingId.value && { _method: 'PUT' }),
        name: formData.name,
        name_ar: formData.name_ar,
        name_cute: formData.name_cute,
        notes: formData.notes,
        school_id: selectedSchool.value,
        stage_id: selectedStage.value,
        grade_id: selectedGrade.value,
        classroom_id: selectedClassroom.value,
        // Add any other required fields from your validation rules
    };

    axios.post(url, requestData)
        .then(response => {
            if (response.data.records) {
                localRecords.value = response.data.records;
                closeModal();
                return { success: true };
            }
        })
        .catch(error => {
            if (error.response?.data?.errors) {
                formErrors.value = error.response.data.errors;
            } else {
                console.error('Submission error:', error);
                alert('An error occurred while saving the record.');
            }
        })
        .finally(() => {
            submitting.value = false;
        });
};

const deleteRecord = async (item) => {
    if (confirm('Are you sure you want to delete this student?')) {
        try {
            const response = await axios.delete(`${baseUrl}/${item.id}`);
            localRecords.value = response.data.records;
        } catch (error) {
            console.error('Error deleting record:', error);
        }
    }
};

const refreshData = async () => {
    try {
        const response = await axios.get(baseUrl);
        localRecords.value = response.data.records;
        localSchools.value = response.data.schools;
        localUserRoles.value = response.data.userRoles;
        localPermissions.value = response.data.permissions;
    } catch (error) {
        console.error('Error refreshing data:', error);
    }
};

const handleExport = (format = 'excel') => {
    exportData({
        items: items.value,
        columns: tableColumns,
        fileName: 'students',
        sheetName: 'Students',
        format: format // 'excel' or 'csv'
    });
};

const formFields = [
    {
        name: 'name',
        label: 'Name',
        type: 'text',
        required: true,
        placeholder: 'Enter student name'
    },
    {
        name: 'name_ar',
        label: 'Arabic Name',
        type: 'text',
        required: false,
        placeholder: 'Enter Arabic name'
    },
    {
        name: 'name_cute',
        label: 'Nickname',
        type: 'text',
        required: false,
        placeholder: 'Enter nickname'
    },
    {
        name: 'notes',
        label: 'Notes',
        type: 'textarea',
        required: false,
        placeholder: 'Enter any additional notes'
    }
];

// Refs for filtering and data
const stages = ref([]);
const grades = ref([]);
const classrooms = ref([]);
const selectedSchool = ref('');
const selectedStage = ref('');
const selectedGrade = ref('');
const selectedClassroom = ref('');

// Methods for handling dropdowns and data loading
const handleSchoolChange = async () => {
    selectedStage.value = '';
    selectedGrade.value = '';
    stages.value = [];
    grades.value = [];

    if (selectedSchool.value) {
        await loadStages(selectedSchool.value);
    }
    await filterData();
};

const handleStageChange =  () => {
    console.log(props?.schools);

    selectedGrade.value = '';
    grades.value = [];

    // if (selectedStage.value) {
    //     await loadGrades(selectedStage.value);
    // }
      filterData();
};

const handleGradeChange = () => {
    selectedClassroom.value = '';
    classrooms.value = [];

    if (selectedGrade.value) {
        axios.get(`/admin/classrooms/by-grade/${selectedGrade.value}`)
            .then(response => {
                classrooms.value = response.data;
            })
            .catch(error => {
                console.error('Error loading classrooms:', error);
                classrooms.value = [];
            });
    }
};

const loadStages = async (schoolId) => {
    try {
        const response = await axios.get(`/admin/stages/by-school/${schoolId}`);
        stages.value = response.data;
    } catch (error) {
        console.error('Error loading stages:', error);
        stages.value = [];
    }
};

const loadGrades = async (stageId) => {
    try {
        const response = await axios.get(`/admin/grades/by-stage/${stageId}`);
        grades.value = response.data;
    } catch (error) {
        console.error('Error loading grades:', error);
        grades.value = [];
    }
};

const loadClassrooms = async (gradeId) => {
    try {
        const response = await axios.get(`/admin/classrooms/by-grade/${gradeId}`);
        classrooms.value = response.data;
    } catch (error) {
        console.error('Error loading classrooms:', error);
        classrooms.value = [];
    }
};

const filterData = async () => {
    try {
        const params = {
            school_id: selectedSchool.value,
            stage_id: selectedStage.value,
            grade_id: selectedGrade.value
        };

        // const response = await axios.get(baseUrl, { params });
        // localRecords.value = response.data.records;
    } catch (error) {
        console.error('Error filtering data:', error);
    }
};

const handleFilterApplied = (filters) => {
    console.log(    filters);

    const params = {
        school_id: filters.school_id || '',
        stage_id: filters.stage_id || '',
        grade_id: filters.grade_id || '',
        classroom_id: filters.classroom_id || ''
    };
    selectedSchool.value = filters.school_id;
    selectedStage.value = filters.stage_id;
    selectedGrade.value = filters.grade_id;
    selectedClassroom.value = filters.classroom_id;

    axios.get(`${baseUrl}/filtered`, { params })
        .then(response => {
            if (response.data && response.data.records) {
                items.value = response.data.records.data;
                pagination.value = response.data.records.links;
                localRecords.value = response.data.records;
            }
        })
        .catch(error => {
            console.error('Error applying filters:', error);
            if (error.response?.data?.message) {
                alert(error.response.data.message);
            } else {
                alert('An error occurred while filtering students');
            }
        });
};

onMounted(() => {
    if (props.records) {
        items.value = props.records.data || [];
        pagination.value = props.records.links || null;
        localRecords.value = props.records;
    }
});
</script>













































