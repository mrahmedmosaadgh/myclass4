<template>
  <div class="_controlGroup_19yuc_175 " :class="my_class">
    <label v-for="(option, index) in options" :key="index"
    > {{ option.value }}
    <!-- class="_hiddenRadioInput_u1tst_155 sf-hidden " -->
      <input
        type="radio"
        v-model="model"
        :name="name"
        :value="option.value"
        >
        <!-- :checked="model === option.value"
        @change="model = option.value" -->
      <!-- :disabled="option[disabled]" -->
      <div
      class="_boxRadioButton_19yuc_202 w-18 h-18 !border-4  border-solid   "
      :style="`background-color: ${option?.cst?.subject?.color_bg};color:${option?.cst?.subject?.color_text};${model?'border-color:green':'border-color:white'};`"
      v-if="index==0||index==1">
        <!-- <component
          :is="option.icon"
          class="_icon_1l3zf_1"
          style="height:22px;width:32px"
        /> -->
        <!-- <div class="scale-75  ">   {{ option?.cst?.subject?.color_bg }}   </div>
        <div class="scale-75  ">   {{ option?.cst?.subject?.color_text }}   </div> -->
        <span class="p-0 ">   {{ option?.cst?.classroom?.name }}   </span>
        <!-- <span class="p-0 ">   {{ option?.cst?.classroom?.name }}   </span> -->
        <!-- <div class="_text_hkjt4_94">   {{ option?.cst?.teacher?.name }}   </div> -->
        <div class=" bg-blue-800 px-2 rounded-full scale-75 text-white">   {{ option?.cst?.subject?.name }}   </div>
        <NameAbbreviator class="scale-75 "
        :full-name="option?.cst?.teacher?.name"
        separator=" "
        :letters_count="2"
        />
    <!-- <NameAbbreviator class="bg-blue-800 px-2 rounded-full scale-75 text-white"
        :full-name="option?.cst?.teacher?.name"
        separator=" "
        :letters_count="2"
    /> -->

<!-- <details>

    <pre >
        {{ option?.cst?.subject?.name  }}
    </pre>
</details> -->
      </div>
    </label>
  </div>
</template>

<script setup>
import NameAbbreviator from './NameAbbreviator2.vue';

const model = defineModel();

defineProps({
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
    required: true,
    // Each option should have: { value, label, icon }
  }
});
</script>

