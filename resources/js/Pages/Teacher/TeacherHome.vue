<template>
    <AppLayout title="Teacher Dashboard">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Teacher Info Summary -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h2 class="text-xl font-semibold mb-2">My Classes</h2>

                        <!-- Filters -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Classroom
                                </label>
                                <select
                                    v-model="selectedClassroom"
                                    @change="handleClassroomChange"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Classroom</option>
                                    <option
                                        v-for="classroom in classrooms"
                                        :key="classroom.id"
                                        :value="classroom.id"
                                    >
                                        {{ classroom.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Subject
                                </label>
                                <select
                                    v-model="selectedSubject"
                                    @change="loadStudents"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select Subject</option>
                                    <option
                                        v-for="subject in filteredSubjects"
                                        :key="subject.id"
                                        :value="subject.id"
                                    >
                                        {{ subject.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Students Table -->
                        <div v-if="students.length > 0" class="mt-6">
                            <h3 class="text-lg font-medium mb-4">Students List</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Name
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Arabic Name
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ID
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="student in students" :key="student.id">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ student.name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ student.name_ar }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ student.student_id }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else-if="selectedClassroom && selectedSubject" class="mt-6 text-center text-gray-500">
                            No students found for the selected classroom and subject.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

// Data
const classrooms = ref([]);
const subjects = ref([]);
const students = ref([]);
const selectedClassroom = ref('');
const selectedSubject = ref('');
const loading = ref(false);

// Get CSRF token from Inertia page props
const token = usePage().props.csrf_token;

// Configure axios defaults
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

// Computed
const filteredSubjects = computed(() => {
    if (!selectedClassroom.value) return [];
    return subjects.value.filter(subject =>
        subject.classroom_id === selectedClassroom.value
    );
});

// Methods
const loadTeacherData = async () => {
    try {
        const response = await axios.get('/api/teacher/classes');
        const { data } = response;
        classrooms.value = data.classrooms;
        subjects.value = data.subjects;
    } catch (error) {
        console.error('Error loading teacher data:', error);
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
    }
};

const handleClassroomChange = () => {
    selectedSubject.value = '';
    students.value = [];
};

const loadStudents = async () => {
    if (!selectedClassroom.value || !selectedSubject.value) {
        students.value = [];
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get('/api/teacher/students', {
            params: {
                classroom_id: selectedClassroom.value,
                subject_id: selectedSubject.value
            }
        });
        students.value = response.data.students;
    } catch (error) {
        console.error('Error loading students:', error);
        students.value = [];
    } finally {
        loading.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    loadTeacherData();
});
</script>

