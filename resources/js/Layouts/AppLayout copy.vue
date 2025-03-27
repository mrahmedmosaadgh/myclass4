<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import MenuButton from '@/Components/MenuButton.vue';
import {
    HomeIcon,
    AcademicCapIcon,
    ClipboardIcon,
    ChartBarIcon,
    CalendarIcon,
    ClipboardCheckIcon
} from '@heroicons/vue/outline';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(window.innerWidth >= 1024);

const toggleNavigation = () => {
    showingNavigationDropdown.value = !showingNavigationDropdown.value;
};

const menuGroups = [
    {
        title: 'Admin',
        role: 'admin',
        items: [
            {
                name: 'Schools',
                route: 'admin.school.index',
                permission: 'admin_manage_school'
            },
            {
                name: 'Academic Years',
                route: 'admin.academic-year.index',
                permission: 'admin_manage_academic_year'
            },
            {
                name: 'Semesters',
                route: 'admin.semester.index',
                permission: 'admin_manage_semester'
            }
        ]
    },
    {
        title: 'Users Management',
        role: 'admin',
        items: [
            {
                name: 'Teachers',
                route: 'admin.teacher.index',
                permission: 'admin_manage_teacher'
            },
            {
                name: 'Students',
                route: 'admin.students.index',
                permission: 'admin_manage_student'
            }
        ]
    },
    {
        title: 'Schedule',
        role: 'admin',
        items: [
            {
                name: 'Classrooms',
                route: 'admin.classroom.index',
                permission: 'admin_manage_classroom'
            },
            {
                name: 'Schedules',
                route: 'admin.schedules.index',
                permission: 'admin_manage_schedule'
            },
            {
                name: 'Calendar',
                route: 'admin.calendar.index',
                permission: 'admin_manage_calendar'
            }
        ]
    },
    {
        title: 'Teacher',
        role: 'teacher',
        items: [
            {
                name: 'Dashboard',
                route: 'teacher.home',
                icon: 'HomeIcon'
            },
            {
                name: 'My Classes',
                route: 'teacher.classes',
                icon: 'AcademicCapIcon'
            },
            {
                name: 'Attendance',
                route: 'teacher.attendance',
                icon: 'ClipboardIcon'
            },
            {
                name: 'Grades',
                route: 'teacher.grades',
                icon: 'ChartBarIcon'
            }
        ]
    },
    {
        title: 'Student',
        role: 'student',
        items: [
            {
                name: 'Dashboard',
                route: 'student.home',
                icon: 'HomeIcon'
            },
            {
                name: 'My Schedule',
                route: 'student.schedule',
                icon: 'CalendarIcon'
            },
            {
                name: 'My Grades',
                route: 'student.grades',
                icon: 'ChartBarIcon'
            },
            {
                name: 'Attendance Record',
                route: 'student.attendance',
                icon: 'ClipboardCheckIcon'
            }
        ]
    }
];

const filteredMenuGroups = computed(() => {
    const userRole = usePage().props.auth.user.role;
    return menuGroups.filter(group => {
        if (!group.role) return true;
        return userRole === group.role || userRole === 'admin';
    });
});

const topNavItems = computed(() => {
    const userRole = usePage().props.auth.user.role;
    const items = [];

    switch(userRole) {
        case 'admin':
            items.push(
                { name: 'Dashboard', route: 'admin.dashboard' },
                { name: 'Schools', route: 'admin.school.index' }
            );
            break;
        case 'teacher':
            items.push(
                { name: 'Dashboard', route: 'teacher.home' },
                { name: 'My Classes', route: 'teacher.classes' }
            );
            break;
        case 'student':
            items.push(
                { name: 'Dashboard', route: 'student.home' },
                { name: 'My Schedule', route: 'student.schedule' }
            );
            break;
    }

    return items;
});

onMounted(() => {
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            sidebarOpen.value = true;
        } else {
            sidebarOpen.value = false;
        }
    });
});

onUnmounted(() => {
    window.removeEventListener('resize', () => {});
});
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />

        <!-- Backdrop for mobile -->
        <div v-if="sidebarOpen"
             class="fixed inset-0 bg-gray-600 bg-opacity-75 z-30 lg:hidden"
             @click="sidebarOpen = false">
        </div>

        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Menu Button - Now visible at all times -->
                            <div class="flex items-center">
                                <MenuButton
                                    :is-open="sidebarOpen"
                                    @toggle="sidebarOpen = !sidebarOpen"
                                    class="mr-2"
                                />
                            </div>

                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink
                                    v-for="item in topNavItems"
                                    :key="item.route"
                                    :href="route(item.route)"
                                    :active="route().current(item.route)"
                                >
                                    {{ item.name }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                            <div>{{ $page.props.auth.user.name }}</div>
                                            <div class="ml-1">
                                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Removed mobile-only menu button -->
                    </div>
                </div>
            </nav>

            <!-- Mobile Navigation -->
            <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink
                        v-for="item in topNavItems"
                        :key="item.route"
                        :href="route(item.route)"
                        :active="route().current(item.route)"
                    >
                        {{ item.name }}
                    </ResponsiveNavLink>
                </div>

                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <svg class="h-10 w-10 fill-current text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>

                        <div class="ml-3">
                            <div class="font-medium text-base text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.show')">
                            Profile
                        </ResponsiveNavLink>

                        <form method="POST" @submit.prevent="logout">
                            <ResponsiveNavLink as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar Drawer -->
            <aside class="fixed inset-y-0 left-0 bg-white w-64 transform transition-transform duration-300 ease-in-out z-40 overflow-y-auto"
                   :class="{
                       'translate-x-0': sidebarOpen,
                       '-translate-x-full': !sidebarOpen,
                       'lg:translate-x-0 lg:static lg:inset-0': true
                   }">
                <nav class="mt-5">
                    <div v-for="group in filteredMenuGroups" :key="group.title" class="px-3 mb-6">
                        <h2 class="text-xs uppercase font-bold text-gray-500 mb-2 px-4">{{ group.title }}</h2>
                        <div v-for="item in group.items" :key="item.route" class="mb-1">
                            <Link
                                :href="route(item.route)"
                                class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-gray-100"
                                :class="{ 'bg-gray-100': route().current(item.route) }"
                            >
                                <component
                                    v-if="item.icon"
                                    :is="item.icon"
                                    class="w-5 h-5 mr-3"
                                />
                                {{ item.name }}
                            </Link>
                        </div>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="transition-all duration-300 ease-in-out"
                  :class="{'lg:pl-64': sidebarOpen}">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>


















