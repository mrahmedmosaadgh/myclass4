<template>
  <div @click="$emit('set_data', option)" class="_controlGroup_19yuc_175" :class="my_class">
    <label>
      <!-- <input
        type="checkbox"
        v-model="model"
        :name="name"
        :value="option"
        class="hidden"
      > -->
      <div
        class="_boxRadioButton_19yuc_202 w-18 h-18 !border-4 border-solid hover:opacity-100 hover:scale-125  opacity-50"
        :style="`background-color: ${option?.cst?.subject?.color_bg};
                color: ${option?.cst?.subject?.color_text};
                 ${model === option ? 'border-color:green; ' : 'border-color:white;  '}`
                 "
      >
        <span class="p-0">{{ option?.cst?.classroom?.name }}</span>
        <span class="p-0">

            <pre>
            {{ option?.schedule?.period_order }}
            <!-- {{ option?.schedule?.period_order }} -->

        </pre>
        </span>
        <div class="bg-blue-800 px-2 rounded-full scale-75 text-white">
          {{ option?.cst?.subject?.name }}
        </div>
        <NameAbbreviator
          class="scale-75"
          :full-name="option?.cst?.teacher?.name"
          separator=" "
          :letters_count="2"
        />
      </div>
    </label>
  </div>
</template>

<script setup>
import NameAbbreviator from './NameAbbreviator2.vue';

const model = defineModel();
const emit = defineEmits(['set_data']);

defineProps({
  name: {
    type: String,
    required: true
  },
  my_class: {
    type: String,
    default: ''
  },
  option: {
    type: Object,
    required: true
  }
});
</script>


