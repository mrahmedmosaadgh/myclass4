<template>
    <div class="grid grid-cols-2 gap-4">
        <!-- Editor -->
        <div class="border rounded-lg">
            <div class="mb-2">
                <button @click="handlePasteFromClipboard"
                        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">
                    {{ locale === 'ar' ? 'لصق من الحافظة' : 'Paste from Clipboard' }}
                </button>
            </div>
            <div style="border: 1px solid #ccc" :dir="locale === 'ar' ? 'rtl' : 'ltr'">
                <Toolbar
                    style="border-bottom: 1px solid #ccc"
                    :editor="editorRef"
                    :defaultConfig="toolbarConfig"
                    mode="default"
                />
                <Editor
                    style="height: 300px; overflow-y: hidden;"
                    v-model="editorContent"
                    :defaultConfig="editorConfig"
                    mode="default"
                    @onCreated="handleCreated"
                    @onChange="handleChange"
                />
            </div>
        </div>
        <!-- Preview -->
        <div class="border rounded-lg p-4">
            <h3 class="text-lg font-semibold mb-2">Preview</h3>
            <div v-html="editorContent" class="prose max-w-none"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, shallowRef, onBeforeUnmount } from 'vue';
import { Editor, Toolbar } from '@wangeditor/editor-for-vue';
import '@wangeditor/editor/dist/css/style.css';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    locale: {
        type: String,
        default: 'en'
    }
});

// Add clipboard paste handler
const handlePasteFromClipboard = async () => {
    try {
        const clipboardItems = await navigator.clipboard.read();
        let content = '';

        // Check for images first
        for (const item of clipboardItems) {
            const imageTypes = item.types.filter(type => type.startsWith('image/'));
            if (imageTypes.length > 0) {
                const imageType = imageTypes[0];
                const blob = await item.getType(imageType);
                const reader = new FileReader();

                reader.onload = (e) => {
                    const base64 = e.target.result;
                    // Insert image into editor
                    if (editorRef.value) {
                        editorRef.value.insertNode({
                            type: 'image',
                            src: base64
                        });
                    }
                };

                reader.readAsDataURL(blob);
                return;
            }
        }

        // If no image, try to get HTML content
        for (const item of clipboardItems) {
            if (item.types.includes('text/html')) {
                const blob = await item.getType('text/html');
                content = await blob.text();
                break;
            }
        }

        // Fallback to plain text if no HTML
        if (!content) {
            content = await navigator.clipboard.readText();
        }

        if (content && editorRef.value) {
            editorRef.value.dangerouslyInsertHtml(content);
        }
    } catch (err) {
        console.error('Clipboard error:', err);
        alert(props.locale === 'ar'
            ? 'فشل في قراءة محتوى الحافظة'
            : 'Failed to read clipboard content.');
    }
};

const emit = defineEmits(['update:modelValue']);

// Editor setup
const editorRef = shallowRef();
const editorContent = ref(props.modelValue);

// Editor configuration
const toolbarConfig = {
    excludeKeys: [
        'insertTable',
        'group-video',
        'uploadVideo',
    ]
};

const editorConfig = {
    placeholder: 'Start typing...',
    MENU_CONF: {
        uploadImage: {
            maxFileSize: 2 * 1024 * 1024, // 2MB
            maxNumberOfFiles: 10,
            allowedFileTypes: ['image/*'],
            customUpload(file, insertFn) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const base64 = e.target.result;
                    insertFn(base64);
                };
                reader.readAsDataURL(file);
            }
        }
    }
};

const handleCreated = (editor) => {
    editorRef.value = editor;
};

const handleChange = (editor) => {
    const html = editor.getHtml();
    editorContent.value = html;
    emit('update:modelValue', html);
};

// Clean up editor on component unmount
onBeforeUnmount(() => {
    const editor = editorRef.value;
    if (editor == null) return;
    editor.destroy();
});
</script>

