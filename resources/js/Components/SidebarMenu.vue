<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import MenuButton from '@/Components/MenuButton.vue';
import { menuGroups } from '@/Components/MenuConfig';

const sidebarOpen = ref(false);
const userMenuOpen = ref(false);
const userMenuRef = ref(null);

const user = computed(() => usePage().props.auth.user);

const filteredMenuGroups = computed(() => {
    const userRoles = usePage().props.auth?.user?.roles || [];
    return menuGroups.filter(group => {
        if (!group.role) return true;
        return userRoles.includes(group.role) || userRoles.includes('admin');
    });
});

// Close user menu when clicking outside
const handleClickOutside = (event) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
        userMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const logout = () => {
    router.post(route('logout'));
};

const safeRoute = (routeName, params = {}) => {
    try {
        if (!routeName) return '#';
        if (typeof route === 'undefined') return '#';
        if (!route().has(routeName)) {
            console.warn(`Route not found: ${routeName}`);
            return '#';
        }
        return route(routeName, params);
    } catch (error) {
        console.warn(`Route error with ${routeName}:`, error);
        return '#';
    }
};
</script>

<template>
    <div class="flex items-center">
        <!-- Menu Button -->

        <div class="flex items-center">
            <MenuButton
                :is-open="sidebarOpen"
                @toggle="sidebarOpen = !sidebarOpen"
                class="ml-2"
            />
        </div>

        <!-- User Menu -->
        <div class="ml-3 relative" ref="userMenuRef">
            <button
                @click="userMenuOpen = !userMenuOpen"
                class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out"
            >
                <span class="mr-2">{{ user.name }}</span>
                <svg
                    class="h-4 w-4"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>

            <!-- User Dropdown Menu -->
            <div
                v-show="userMenuOpen"
                class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1"
            >
                <Link
                    :href="safeRoute('profile.show')"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                    Profile
                </Link>
                <button
                    @click="logout"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                    Logout
                </button>
            </div>
        </div>

        <!-- Backdrop -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar Drawer -->
        <aside
            class="fixed inset-y-0 right-0 bg-white w-64 transform transition-transform duration-300 ease-in-out z-50 overflow-y-auto"
            :class="{
                'translate-x-0': sidebarOpen,
                'translate-x-full': !sidebarOpen,
            }"
        >
            <nav class="mt-5">
                <div v-for="group in filteredMenuGroups" :key="group.title" class="px-3 mb-6">
                    <h2 class="text-xs uppercase font-bold text-gray-500 mb-2 px-4">
                        {{ group.title }}
                    </h2>
                    <div v-for="item in group.items" :key="item.route" class="mb-1">
                        <Link
                            :href="safeRoute(item.route)"
                            class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-gray-100"
                            :class="{ 'bg-gray-100': $page.url.startsWith(safeRoute(item.route)) }"
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
    </div>
</template>





