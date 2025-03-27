<template>
    <div>
        <!-- Search input -->
        <div class="mb-4">
            <input
                type="text"
                v-model="searchQuery"
                placeholder="Search users..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
        </div>

        <!-- Users List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                <li v-for="user in filteredUsers" :key="user.id">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-indigo-600">
                                    {{ user.name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ user.email }}
                                </p>
                            </div>
                            <div>
                                <button
                                    @click="$emit('manage-user-roles', user)"
                                    class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-xs font-medium rounded text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none"
                                >
                                    Manage Roles
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            <span class="font-semibold">Current Roles:</span>
                            <span v-if="user.roles.length">
                                {{ user.roles.map(r => r.name).join(', ') }}
                            </span>
                            <span v-else>No roles assigned</span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        required: true
    }
});

defineEmits(['manage-user-roles']);

const searchQuery = ref('');

const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;

    const search = searchQuery.value.toLowerCase();
    return props.users.filter(user =>
        user.name.toLowerCase().includes(search) ||
        user.email.toLowerCase().includes(search)
    );
});
</script>
