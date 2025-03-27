<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        #
                    </th>
                    <th v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                        {{ column.label }}
                    </th>
                    <th v-if="actions.length" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in items" :key="item.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ startingNumber + index }}
                    </td>
                    <td v-for="column in columns"
                        :key="column.key"
                        class="px-6 py-4 whitespace-nowrap"
                        :class="column.class ? column.class(getValue(item, column.key)) : ''"
                    >
                        <template v-if="column.formatter">
                            {{ column.formatter(getValue(item, column.key), item) }}
                        </template>
                        <template v-else>
                            {{ getValue(item, column.key) }}
                        </template>
                    </td>
                    <td v-if="actions.length" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button
                            v-for="action in actions"
                            :key="action.type"
                            v-show="shouldShowAction(action, item)"
                            @click="handleAction(action, item)"
                            :class="[
                                action.class || getDefaultActionClass(action.type),
                                'ml-4 first:ml-0'
                            ]"
                        >
                            {{ action.label }}
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    columns: {
        type: Array,
        required: true
    },
    actions: {
        type: Array,
        default: () => []
    },
    currentPage: {
        type: Number,
        default: 1
    },
    perPage: {
        type: Number,
        default: 40
    }
});

const emit = defineEmits(['action']);

const startingNumber = computed(() => {
    return ((props.currentPage - 1) * props.perPage) + 1;
});

const getValue = (item, key) => {
    if (!item) return '';
    return key.split('.').reduce((obj, k) => obj?.[k], item);
};

const getDefaultActionClass = (type) => {
    const classes = {
        edit: 'text-indigo-600 hover:text-indigo-900',
        delete: 'text-red-600 hover:text-red-900',
        view: 'text-blue-600 hover:text-blue-900',
    };
    return classes[type] || 'text-gray-600 hover:text-gray-900';
};

const shouldShowAction = (action, item) => {
    if (typeof action.show === 'function') {
        return action.show(item);
    }
    return true;
};

const handleAction = (action, item) => {
    emit('action', { type: action.type, item });
};
</script>

