<template>
    <div>
        <!-- Slides Navigation -->
        <div class="flex items-center space-x-2 mb-4">
            <button
                v-for="(slide, index) in slides"
                :key="slide.id"
                @click="selectSlide(index)"
                :class="[
                    'px-3 py-1 rounded',
                    currentSlideIndex === index
                        ? 'bg-blue-500 text-white'
                        : 'bg-gray-200 hover:bg-gray-300'
                ]"
            >
                {{ slide.title }}
            </button>
            <button
                @click="addNewSlide"
                class="px-3 py-1 rounded bg-green-500 text-white hover:bg-green-600"
            >
                + Add Slide
            </button>
        </div>

        <!-- Editor -->
        <div v-if="showEditor" class="border border-gray-300 rounded-md">
            <Toolbar
                :editor="editorRef"
                :defaultConfig="toolbarConfig"
                :mode="mode"
                style="border-bottom: 1px solid #ccc"
            />
            <Editor
                :defaultConfig="editorConfig"
                :mode="mode"
                v-model="currentContent"
                @onCreated="handleCreated"
                @onChange="handleChange"
                style="height: 500px; overflow-y: hidden;"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { Editor, Toolbar } from '@wangeditor/editor-for-vue';
import { getEditorConfig, toolbarConfig } from '@/Utils/editorConfig';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    locale: {
        type: String,
        default: 'en'
    },
    mode: {
        type: String,
        default: 'default'
    }
});

const emit = defineEmits(['update:modelValue']);

const editorRef = ref(null);
const currentContent = ref('');
const currentSlideIndex = ref(0);
const slides = ref([]);
const showEditor = ref(true);

// Define selectSlide function before using it in watch
const selectSlide = (index) => {
    if (index >= 0 && index < slides.value.length) {
        currentSlideIndex.value = index;
        currentContent.value = slides.value[index].content || '';
        if (editorRef.value) {
            editorRef.value.setHtml(currentContent.value);
        }
    }
};

// Initialize slides
const initializeSlides = () => {
    if (!Array.isArray(props.modelValue) || props.modelValue.length === 0) {
        slides.value = [{
            id: Date.now(),
            title: 'Slide 1',
            content: ''
        }];
        emit('update:modelValue', slides.value);
    } else {
        slides.value = [...props.modelValue];
    }
};

// Initialize on mount
initializeSlides();

// Watch for modelValue changes
watch(() => props.modelValue, (newValue) => {
    if (Array.isArray(newValue)) {
        slides.value = [...newValue];
        if (slides.value.length === 0) {
            slides.value = [{
                id: Date.now(),
                title: 'Slide 1',
                content: ''
            }];
            emit('update:modelValue', slides.value);
        }
        selectSlide(currentSlideIndex.value);
    }
}, { deep: true });

const addNewSlide = () => {
    const newSlide = {
        id: Date.now(),
        title: `Slide ${slides.value.length + 1}`,
        content: ''
    };
    slides.value.push(newSlide);
    selectSlide(slides.value.length - 1);
    emit('update:modelValue', slides.value);
};

const handleCreated = (editor) => {
    editorRef.value = editor;
    if (slides.value[currentSlideIndex.value]) {
        currentContent.value = slides.value[currentSlideIndex.value].content || '';
        editor.setHtml(currentContent.value);
    }
};

const handleChange = (editor) => {
    const content = editor.getHtml();
    currentContent.value = content;

    // Update the current slide's content
    if (slides.value[currentSlideIndex.value]) {
        slides.value[currentSlideIndex.value].content = content;
        emit('update:modelValue', [...slides.value]);
    }
};

// Clean up
onBeforeUnmount(() => {
    if (editorRef.value) {
        editorRef.value.destroy();
    }
});

const editorConfig = {
    ...getEditorConfig(),
    placeholder: 'Please enter content...',
    MENU_CONF: {
        ...getEditorConfig().MENU_CONF,
        uploadImage: {
            maxFileSize: 2 * 1024 * 1024,
            maxNumberOfFiles: 10,
            allowedFileTypes: ['image/*'],
            customUpload(file, insertFn) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    insertFn(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        }
    }
};
</script>

<style src="@wangeditor/editor/dist/css/style.css"></style>

