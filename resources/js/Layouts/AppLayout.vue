<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import NavLink from '@/Components/NavLink.vue';
import SidebarMenu from '@/Components/SidebarMenu.vue';

defineProps({
    title: String,
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
</script>

<template>
    <div>
        <Head :title="title" />
        <Banner />

        <div class="min-h-screen bg-gray-100">
            <!-- Navigation Bar -->
            <nav class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
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

                        <div class="flex items-center">
                            <SidebarMenu />
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main>
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

























