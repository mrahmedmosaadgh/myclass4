<template>
  <div class="_controlGroup_19yuc_175" :class="my_class">
    <label v-for="(option, index) in sortedOptions" :key="index">
      <input
        type="radio"
        v-model="model"
        :name="name"
        :value="option"
        class="hidden"
      >
      <div class="p-0"
:class="option?.period_number===period_day?.period?'opacity-20':'opacity-100'"
        :style="`background-color: ${option?.cst?.subject?.color_bg};
                color: ${option?.cst?.subject?.color_text};`"

      >





      <div
        class="_boxRadioButton_19yuc_202 w-18 h-18 !border-4 border-solid"
        :style="`
                 ${model === option ? 'border-color:green;opacity:1' : 'border-color:white;opacity: 0.5 '}`"
      >
        <span class="p-0">{{ option?.cst?.classroom?.name }}</span>
        <span class="p-0">{{ option?.period_order }}</span>
        <div class="bg-blue-800 px-2 rounded-full scale-75 text-white">
          {{ option?.cst?.subject?.name }}
        </div>
        <NameAbbreviator
          class="scale-75"
          :full-name="option?.cst?.teacher?.name"
          separator=" "
          :letters_count="2"
        />
<!-- {{ option?.period_number===period_day?.period }}
day:{{ period_day?.day }}
period_number:{{ period_day?.period }} -->


      </div>
    </div>

    </label>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import NameAbbreviator from './NameAbbreviator2.vue';

const model = defineModel();

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  my_class: {
    type: String,
    default: ''
  },
  options: {
    type: Array,
    required: true
  },
  period_day: {
    type: Object,
    required: true
  }
});

// Add computed property to sort options
const sortedOptions = computed(() => {
  return [...props.options].sort((a, b) => {
    const aOrder = Number(a?.period_order ?? 0);
    const bOrder = Number(b?.period_order ?? 0);
    return aOrder - bOrder;
  });
});
</script>









