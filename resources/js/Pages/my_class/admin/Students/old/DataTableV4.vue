<template>
    <div class="bg-white rounded-lg shadow">
        <!-- Header Section -->
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
            <div class="flex-1">
                <!-- Search Input -->
                <div v-if="searchable" class="max-w-xs">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            placeholder="Search..."
                            @input="handleSearch"
                        >
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <LucideIcon name="search" class="h-4 w-4 text-gray-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div v-if="showBulkActions && selected.length > 0" class="flex items-center space-x-3">
                <span class="text-sm text-gray-700">{{ selected.length }} selected</span>
                <select
                    v-model="bulkAction"
                    class="rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    @change="handleBulkAction"
                >
                    <option value="">Bulk Actions</option>
                    <option v-for="action in bulkActions" :key="action.value" :value="action.value">
                        {{ action.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <!-- Checkbox Column -->
                        <th v-if="selectable" scope="col" class="relative px-6 py-3">
                            <input
                                type="checkbox"
                                class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                :checked="isAllSelected"
                                @change="toggleSelectAll"
                            >
                        </th>

                        <!-- Column Headers -->
                        <th
                            v-for="(column, index) in columns"
                            :key="index"
                            @click="column.sortable ? handleSort(column.key) : null"
                            :class="[
                                'px-6 py-3 text-left text-xs font-medium tracking-wider',
                                column.sortable ? 'cursor-pointer hover:bg-gray-100' : '',
                                getSortClass(column.key)
                            ]"
                        >
                            <div class="flex items-center space-x-1">
                                <span>{{ column.label }}</span>
                                <LucideIcon
                                    v-if="column.sortable"
                                    :name="getSortIcon(column.key)"
                                    class="w-4 h-4"
                                />
                            </div>
                        </th>

                        <!-- Actions Column -->
                        <th v-if="hasActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(item, index) in displayedItems"
                        :key="getItemKey(item, index)"
                        :class="{'bg-gray-50': index % 2 === 0}"
                    >
                        <!-- Checkbox -->
                        <td v-if="selectable" class="px-6 py-4 whitespace-nowrap">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                :value="getItemKey(item)"
                                v-model="selected"
                            >
                        </td>

                        <!-- Data Cells -->
                        <td
                            v-for="(column, colIndex) in columns"
                            :key="colIndex"
                            class="px-6 py-4 whitespace-nowrap"
                            :class="getCellClass(column, item)"
                        >
                            <div v-if="column.template" class="flex items-center">
                                <slot :name="column.key" :item="item" :value="getValue(item, column.key)">
                                    {{ getValue(item, column.key) }}
                                </slot>
                            </div>
                            <div v-else-if="column.formatter">
                                {{ column.formatter(getValue(item, column.key), item) }}
                            </div>
                            <div v-else>
                                {{ getValue(item, column.key) }}
                            </div>
                        </td>

                        <!-- Actions -->
                        <td v-if="hasActions" class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex justify-end space-x-2">
                                <button
                                    v-for="(action, actionIndex) in actions"
                                    :key="actionIndex"
                                    v-if="shouldShowAction(action, item)"
                                    @click="handleActionClick(action, item)"
                                    :class="[
                                        'inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium transition-all duration-150 ease-in-out',
                                        getActionClass(action)
                                    ]"
                                >
                                    <LucideIcon
                                        v-if="action.icon"
                                        :name="action.icon"
                                        class="w-4 h-4 mr-1.5"
                                        :class="action.iconClass"
                                    />
                                    <span>{{ action.label }}</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="displayedItems.length === 0">
                        <td :colspan="totalColumns" class="px-6 py-10 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <LucideIcon name="inbox" class="w-12 h-12 text-gray-400 mb-3" />
                                <p class="text-gray-500 text-base">{{ emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer with Pagination -->
        <div v-if="showPagination" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <button
                    @click="changePage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-700 bg-white border border-gray-300 hover:bg-gray-50"
                >
                    Previous
                </button>
                <button
                    @click="changePage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="ml-3 relative inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-gray-700 bg-white border border-gray-300 hover:bg-gray-50"
                >
                    Next
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ startIndex + 1 }}</span>
                        to
                        <span class="font-medium">{{ endIndex }}</span>
                        of
                        <span class="font-medium">{{ totalItems }}</span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button
                            v-for="page in displayedPages"
                            :key="page"
                            @click="changePage(page)"
                            :class="[
                                'relative inline-flex items-center px-4 py-2 text-sm font-medium border',
                                currentPage === page
                                    ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                            ]"
                        >
                            {{ page }}
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';

const props = defineProps({
    items: {
        type: Array,
        required: true,
        default: () => [] // Add default empty array
    },
    columns: {
        type: Array,
        required: true
    },
    actions: {
        type: Array,
        default: () => []
    },
    searchable: {
        type: Boolean,
        default: false
    },
    selectable: {
        type: Boolean,
        default: false
    },
    bulkActions: {
        type: Array,
        default: () => []
    },
    perPage: {
        type: Number,
        default: 10
    },
    emptyMessage: {
        type: String,
        default: 'No items found'
    }
});

const emit = defineEmits(['sort', 'search', 'action', 'bulk-action', 'page-change', 'selection-change']);

// State
const searchQuery = ref('');
const sortKey = ref('');
const sortOrder = ref('asc');
const selected = ref([]);
const currentPage = ref(1);
const itemsPerPage = ref(props.perPage);

// Computed
const hasActions = computed(() => {
    console.log('Actions:', props.actions); // Debug log
    return Array.isArray(props.actions) && props.actions.length > 0;
});
const showBulkActions = computed(() => props.bulkActions.length > 0 && props.selectable);

const filteredItems = computed(() => {
    if (!Array.isArray(props.items)) return [];

    let items = [...props.items];

    if (searchQuery.value) {
        items = items.filter(item => {
            return props.columns.some(column => {
                const value = getValue(item, column.key);
                return String(value).toLowerCase().includes(searchQuery.value.toLowerCase());
            });
        });
    }

    if (sortKey.value) {
        items.sort((a, b) => {
            const aVal = getValue(a, sortKey.value);
            const bVal = getValue(b, sortKey.value);
            return sortOrder.value === 'asc' ?
                (aVal > bVal ? 1 : -1) :
                (aVal < bVal ? 1 : -1);
        });
    }

    return items;
});

const displayedItems = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredItems.value.slice(start, end);
});

const totalItems = computed(() => filteredItems.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value);
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage.value, totalItems.value));

const displayedPages = computed(() => {
    const pages = [];
    const maxPages = 5;
    let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
    let end = Math.min(totalPages.value, start + maxPages - 1);

    if (end - start + 1 < maxPages) {
        start = Math.max(1, end - maxPages + 1);
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    return pages;
});

// Methods
const getValue = (item, key) => {
    return key.split('.').reduce((obj, k) => obj?.[k], item) ?? '';
};

const getItemKey = (item, fallbackIndex) => {
    return item.id ?? fallbackIndex;
};

const getCellClass = (column, item) => {
    if (typeof column.class === 'function') {
        return column.class(getValue(item, column.key), item);
    }
    return column.class;
};

const getSortClass = (key) => {
    if (sortKey.value !== key) return 'text-gray-500';
    return sortOrder.value === 'asc' ? 'text-indigo-600' : 'text-indigo-600';
};

const getSortIcon = (key) => {
    if (sortKey.value !== key) return 'chevrons-up-down';
    return sortOrder.value === 'asc' ? 'chevron-up' : 'chevron-down';
};

const getActionClass = (action) => {
    const defaultClass = 'transition-colors duration-150';

    switch (action.type) {
        case 'edit':
            return `${defaultClass} text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100`;
        case 'delete':
            return `${defaultClass} text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100`;
        case 'view':
            return `${defaultClass} text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100`;
        default:
            return action.class || `${defaultClass} text-gray-600 hover:text-gray-800 bg-gray-50 hover:bg-gray-100`;
    }
};

const handleSort = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
    emit('sort', { key, order: sortOrder.value });
};

const handleSearch = () => {
    currentPage.value = 1;
    emit('search', searchQuery.value);
};

const handleActionClick = (action, item) => {
    console.log('Action clicked:', action, item); // Debug log
    if (typeof action.action === 'function') {
        action.action(item);
    } else {
        emit('action', { type: action.type, item });
    }
};

const shouldShowAction = (action, item) => {
    if (!action) return false;
    if (typeof action.show === 'function') {
        return action.show(item);
    }
    // If show property doesn't exist or is not a function, default to true
    return action.show !== false;
};

const toggleSelectAll = () => {
    if (selected.value.length === filteredItems.value.length) {
        selected.value = [];
    } else {
        selected.value = filteredItems.value.map(item => getItemKey(item));
    }
    emit('selection-change', selected.value);
};

const handleBulkAction = () => {
    emit('bulk-action', {
        action: bulkAction.value,
        selected: selected.value
    });
};

const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        emit('page-change', page);
    }
};

// Watch for selection changes
watch(selected, (newValue) => {
    emit('selection-change', newValue);
});
</script>






