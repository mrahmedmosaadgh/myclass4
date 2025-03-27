<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">{{ title }}</h2>
            <form @submit.prevent="$emit('submit')" class="mt-6">
                <div class="space-y-4">
                    <div v-for="field in fields" :key="field.name">
                        <label :for="field.name" class="block text-sm font-medium text-gray-700">
                            {{ field.label }}
                            <span v-if="field.required" class="text-red-500">*</span>
                        </label>

                        <select
                            v-if="field.type === 'select'"
                            :id="field.name"
                            v-model="modelValue[field.name]"
                            :disabled="field.disabled"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option
                                v-for="option in field.options"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>

                        <!-- Other field types... -->
                    </div>
                </div>

                <!-- Form buttons... -->
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: Boolean,
    title: String,
    fields: Array,
    modelValue: Object,
    errors: Object,
    submitting: Boolean
});

defineEmits(['close', 'submit', 'update:modelValue']);
</script>
