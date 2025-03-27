<template>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">
            {{ locale === 'ar' ? 'المحتوى' : 'Content' }}
        </label>

        <!-- Content List -->
        <div class="mb-4">
            <div v-for="(item, index) in form.content" :key="item.id" class="mb-4 p-4 border rounded">
                <div class="flex justify-between items-center mb-2">
                    <input v-model="item.title"
                           class="border rounded px-2 py-1"
                           :placeholder="locale === 'ar' ? 'العنوان' : 'Title'"/>
                    <div class="space-x-2">
                        <button @click="editContent(index)"
                                class="px-2 py-1 bg-blue-500 text-white rounded">
                            {{ locale === 'ar' ? 'تحرير' : 'Edit' }}
                        </button>
                        <button @click="removeContent(index)"
                                class="px-2 py-1 bg-red-500 text-white rounded">
                            {{ locale === 'ar' ? 'حذف' : 'Remove' }}
                        </button>
                    </div>
                </div>
                <div v-html="item.content" class="prose max-w-none"></div>
            </div>
        </div>

        <!-- Add Content Buttons -->
        <div class="flex space-x-2 mb-4">
            <button @click="addFromClipboard"
                    class="px-4 py-2 bg-green-500 text-white rounded">
                {{ locale === 'ar' ? 'إضافة من الحافظة' : 'Add from Clipboard' }}
            </button>
            <button @click="showEditor = true"
                    class="px-4 py-2 bg-blue-500 text-white rounded">
                {{ locale === 'ar' ? 'إضافة محتوى جديد' : 'Add New Content' }}
            </button>
        </div>

        <!-- Editor Modal -->
        <div v-if="showEditor" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-4 rounded-lg w-3/4 max-h-[90vh] overflow-y-auto">
                <div style="border: 1px solid #ccc;" :dir="locale === 'ar' ? 'rtl' : 'ltr'">
                    <Toolbar
                        style="border-bottom: 1px solid #ccc"
                        :editor="editorRef"
                        :defaultConfig="toolbarConfig"
                        mode="default"
                    />
                    <Editor
                        style="height: 300px; overflow-y: hidden;"
                        v-model="currentContent"
                        :defaultConfig="editorConfig"
                        mode="default"
                        @onCreated="handleCreated"
                        @onChange="handleChange"
                        @onDestroyed="handleDestroyed"
                    />
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button @click="saveContent"
                            class="px-4 py-2 bg-green-500 text-white rounded">
                        {{ locale === 'ar' ? 'حفظ' : 'Save' }}
                    </button>
                    <button @click="showEditor = false"
                            class="px-4 py-2 bg-gray-500 text-white rounded">
                        {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Editor, Toolbar } from '@wangeditor/editor-for-vue'
import { ref, shallowRef, onBeforeUnmount, watch, nextTick } from 'vue'
import { Boot } from '@wangeditor/editor'
import markdownModule from '@wangeditor/plugin-md'
import formulaModule from '@wangeditor/plugin-formula'

// Register the modules
Boot.registerModule(markdownModule)
Boot.registerModule(formulaModule)

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    locale: {
        type: String,
        default: 'en'
    }
})

const emit = defineEmits(['update:modelValue'])

const editorRef = shallowRef()
const showEditor = ref(false)
const currentContent = ref('')
const editingIndex = ref(-1)
const form = ref({
    content: [] // Initialize as empty array
})

// Watch for changes in props.modelValue and update form.content
watch(() => props.modelValue, (newValue) => {
    if (newValue && Array.isArray(newValue)) {
        form.value.content = [...newValue];
    } else {
        form.value.content = []; // Ensure it's always an array
    }
}, { immediate: true })

const formatTextToHtml = (text) => {
    return text
        // Convert URLs to links
        .replace(
            /(https?:\/\/[^\s]+)/g,
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
        )
        // Convert markdown-style bold
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        // Convert markdown-style italic
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        // Convert newlines to <br> tags
        .replace(/\n/g, '<br>')
        // Preserve multiple spaces
        .replace(/\s\s/g, '&nbsp;&nbsp;');
};

const addFromClipboard = async () => {
    try {
        // Get clipboard data as HTML if available
        const clipboardData = await navigator.clipboard.read();
        let htmlContent = '';

        for (const item of clipboardData) {
            if (item.types.includes('text/html')) {
                const blob = await item.getType('text/html');
                htmlContent = await blob.text();
                break;
            }
        }

        // If no HTML content found, fallback to plain text
        if (!htmlContent) {
            const text = await navigator.clipboard.readText();
            htmlContent = formatTextToHtml(text);
        }

        // Add new content directly to the form content array
        const newContent = {
            id: Date.now(),
            title: 'New Content',
            content: htmlContent
        };

        form.value.content = [...form.value.content, newContent];
        emit('update:modelValue', form.value.content);

    } catch (err) {
        console.error('Clipboard error:', err);
        alert('Failed to read clipboard. Please make sure you have granted clipboard permission.');
    }
};

const editContent = (index) => {
    if (!form.value.content || !Array.isArray(form.value.content)) {
        console.warn('Content is not properly initialized');
        return;
    }

    editingIndex.value = index;
    currentContent.value = '';
    showEditor.value = true;

    nextTick(() => {
        if (editorRef.value) {
            editorRef.value.destroy();
            editorRef.value = null;
        }
    });
};

const removeContent = (index) => {
    const newContent = [...form.value.content]
    newContent.splice(index, 1)
    form.value.content = newContent
    emit('update:modelValue', form.value.content)
}

const saveContent = () => {
    const newContent = [...form.value.content]

    if (editingIndex.value >= 0) {
        newContent[editingIndex.value] = {
            ...newContent[editingIndex.value],
            content: currentContent.value
        }
    } else {
        newContent.push({
            id: Date.now(),
            title: 'New Content',
            content: currentContent.value
        })
    }

    form.value.content = newContent
    emit('update:modelValue', form.value.content)
    showEditor.value = false
    currentContent.value = ''
    editingIndex.value = -1
}

// Editor configuration
const editorConfig = {
    placeholder: 'Please enter content...',
    MENU_CONF: {
        uploadImage: {
            server: '/api/upload-image',
            fieldName: 'image',
            maxFileSize: 10 * 1024 * 1024,
            maxNumberOfFiles: 10,
            allowedFileTypes: ['image/*'],
            metaWithUrl: true,
            customUpload(file, insertFn) {
                const reader = new FileReader()
                reader.onload = (e) => {
                    const base64 = e.target.result
                    insertFn(base64)
                }
                reader.readAsDataURL(file)
            }
        },
        formulaConfig: {
            katex: {
                throwOnError: false,
                output: 'html'  // Use 'html' output mode
            }
        }
    },
    parseHtml: (elem) => {
        // Special handling for KaTeX elements
        if (elem.className && elem.className.includes('katex')) {
            return {
                type: 'formula',
                value: elem.outerHTML
            };
        }
        return elem;
    }
}

// Toolbar configuration
const toolbarConfig = {
    toolbarKeys: [
        'headerSelect',
        '|',
        'bold',
        'italic',
        'underline',
        {
            key: 'group-more-style',
            title: 'More',
            menuKeys: ['through', 'code', 'clearStyle'],
        },
        '|',
        'color',
        'bgColor',
        '|',
        'fontSize',
        'fontFamily',
        'lineHeight',
        '|',
        'bulletedList',
        'numberedList',
        'todo',
        {
            key: 'group-justify',
            title: 'Alignment',
            menuKeys: ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyJustify'],
        },
        '|',
        'insertLink',
        'uploadImage',
        'insertTable',
        {
            key: 'group-more',
            title: 'More',
            menuKeys: ['insertVideo', 'codeBlock', 'blockquote'],
        },
        '|',
        {
            key: 'group-formula',
            title: 'Formula',
            menuKeys: ['insertMath', 'insertLatex'],
        },
        '|',
        'undo',
        'redo',
    ]
}

// Editor event handlers
const handleCreated = (editor) => {
    editorRef.value = editor;

    // Add defensive checks
    if (editingIndex.value >= 0 &&
        form.value.content &&
        Array.isArray(form.value.content) &&
        form.value.content[editingIndex.value]) {

        // Wait for next tick before setting content
        nextTick(() => {
            editor.setHtml(form.value.content[editingIndex.value].content);
        });
    }
};

const handleChange = (editor) => {
    currentContent.value = editor.getHtml();
};

const handleDestroyed = () => {
    editorRef.value = null;
};

// Add this to ensure proper cleanup
onBeforeUnmount(() => {
    if (editorRef.value) {
        editorRef.value.destroy();
        editorRef.value = null;
    }
});
</script>

<style>
.w-e-text-container {
    background-color: #fff !important;
}

[dir="rtl"] .w-e-toolbar {
    flex-direction: row-reverse;
}

[dir="rtl"] .w-e-text-container {
    direction: rtl;
}

[dir="rtl"] .w-e-toolbar > div {
    direction: rtl;
}
</style>



























