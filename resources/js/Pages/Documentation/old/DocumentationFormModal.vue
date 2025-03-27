<template>
    <div>
        <!-- Title Field -->
        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700">
                {{ locale === 'ar' ? 'العنوان' : 'Title' }}
            </label>
            <input
                id="title"
                v-model="form.title"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                required
            >
        </div>

        <!-- Save All Button and Add Slide Buttons -->
        <div class="flex justify-between mb-4">
            <div class="flex space-x-2">
                <button @click="addFromClipboard"
                        class="px-4 py-2 bg-green-500 text-white rounded">
                    {{ locale === 'ar' ? 'إضافة شريحة من الحافظة' : 'Add Slide from Clipboard' }}
                </button>
                <button @click="addNewSlide"
                        class="px-4 py-2 bg-blue-500 text-white rounded">
                    {{ locale === 'ar' ? 'إضافة شريحة جديدة' : 'Add New Slide' }}
                </button>
            </div>
            <button @click="saveAllSlides"
                    class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 transition-colors"
                    :disabled="!form.content.length || saving || !form.title.trim()">
                {{ saving ? (locale === 'ar' ? 'جاري الحفظ...' : 'Saving...') :
                           (locale === 'ar' ? 'حفظ جميع الشرائح' : 'Save All Slides') }}
            </button>
        </div>

        <!-- Slides List -->
        <div class="space-y-4">
            <div v-for="(item, index) in form.content" :key="item.id"
                 class="border rounded p-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-semibold">{{ locale === 'ar' ? 'شريحة' : 'Slide' }} {{ index + 1 }}</h3>
                    <div class="space-x-2">
                        <button @click="editSlide(index)"
                                class="px-2 py-1 bg-blue-500 text-white rounded">
                            {{ locale === 'ar' ? 'تعديل' : 'Edit' }}
                        </button>
                        <button @click="removeSlide(index)"
                                class="px-2 py-1 bg-red-500 text-white rounded">
                            {{ locale === 'ar' ? 'حذف' : 'Delete' }}
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <div v-html="item.content"
                         class="content-editable"
                         ref="contentDiv"
                         @click="handleElementClick($event, index)">
                    </div>
                    <div v-if="selectedElement"
                         class="absolute top-2 right-2 flex space-x-2">
                        <!-- Width Control -->
                        <div class="flex items-center bg-white rounded shadow p-1">
                            <label class="text-sm text-gray-600 mr-2">
                                {{ locale === 'ar' ? 'العرض' : 'Width' }}:
                            </label>
                            <select
                                v-model="selectedWidth"
                                @change="changeElementWidth(index)"
                                class="text-sm border rounded px-1 py-0.5">
                                <option value="w-full">100%</option>
                                <option value="w-3/4">75%</option>
                                <option value="w-1/2">50%</option>
                                <option value="w-1/4">25%</option>
                                <option value="w-auto">Auto</option>
                            </select>
                        </div>

                        <!-- Replace Content Button -->
                        <button
                            @click="openReplaceModal(index)"
                            class="px-2 py-1 bg-blue-500 text-white rounded text-sm">
                            {{ locale === 'ar' ? 'استبدال' : 'Replace' }}
                        </button>

                        <!-- Delete Button -->
                        <button
                            @click="deleteSelectedElement(index)"
                            class="px-2 py-1 bg-red-500 text-white rounded text-sm">
                            {{ locale === 'ar' ? 'حذف' : 'Delete' }}
                        </button>
                    </div>

                    <!-- Replace Content Modal -->
                    <Modal :show="replaceModalOpen" @close="closeReplaceModal">
                        <div class="p-6 replace-modal">
                            <h3 class="text-lg font-medium mb-4">
                                {{ locale === 'ar' ? 'استبدال المحتوى' : 'Replace Content' }}
                            </h3>
                            <textarea
                                v-model="replacementContent"
                                class="w-full h-32 border rounded p-2 mb-4"
                                :placeholder="locale === 'ar' ? 'أدخل المحتوى الجديد' : 'Enter new content'"
                            ></textarea>

                            <!-- Preview Section -->
                            <div class="mb-4">
                                <h4 class="text-sm font-medium mb-2">{{ locale === 'ar' ? 'معاينة' : 'Preview' }}</h4>
                                <div class="border rounded p-3 bg-gray-50 min-h-[100px]" v-html="replacementContent"></div>
                            </div>

                            <div class="flex justify-end space-x-2" :dir="locale === 'ar' ? 'rtl' : 'ltr'">
                                <button
                                    @click="replaceContent"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
                                    :disabled="!replacementContent.trim()">
                                    {{ locale === 'ar' ? 'تأكيد' : 'Confirm' }}
                                </button>
                                <button
                                    @click="closeReplaceModal"
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                    {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
                                </button>
                            </div>
                        </div>
                    </Modal>
                </div>
            </div>
        </div>

        <!-- Empty state message when no slides -->
        <div v-if="!form.content.length" class="text-center py-8 text-gray-500">
            {{ locale === 'ar' ? 'لا توجد شرائح. أضف شريحة جديدة للبدء.' : 'No slides yet. Add a new slide to get started.' }}
        </div>

        <!-- Editor Modal -->
        <div v-if="showEditor" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-4 rounded-lg w-3/4 max-h-[90vh] overflow-y-auto relative"> <!-- Added relative positioning -->
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
                <div class="mt-4 flex justify-end space-x-2 sticky bottom-0 bg-white p-2"> <!-- Added sticky positioning and background -->
                    <button @click="saveSlide"
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">
                        {{ locale === 'ar' ? 'حفظ' : 'Save' }}
                    </button>
                    <button @click="closeEditor"
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">
                        {{ locale === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import '@wangeditor/editor/dist/css/style.css'
import { Editor, Toolbar } from '@wangeditor/editor-for-vue'
import {onMounted , ref, shallowRef, onBeforeUnmount, watch, nextTick, computed } from 'vue'
import { Boot } from '@wangeditor/editor'
import formulaModule from '@wangeditor/plugin-formula'
import Modal from '@/Components/Modal.vue'

// Register the modules before using the editor
Boot.registerModule(formulaModule)

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    locale: {
        type: String,
        default: 'en'
    },
    show: {
        type: Boolean,
        default: false
    }
})

// Create computed property for locale
const currentLocale = computed(() => props.locale)

const emit = defineEmits(['update:modelValue', 'submitted', 'close'])

// Initialize refs
const editorRef = shallowRef()
const showEditor = ref(false)
const currentContent = ref('')
const editingIndex = ref(-1)
const selectedElement = ref(null)
const selectedSlideIndex = ref(null)
const missingImageElement = ref(null)
const form = ref({
    content: [],
    title: '',
    type: '',
    status: '',
    tags: ''
})

// Add these to your existing refs
const saving = ref(false);
const replaceModalOpen = ref(false);
const replacementContent = ref('');

// Add this computed property if you need to disable buttons while saving
const isDisabled = computed(() => saving.value);

// Image handling functions
const handleLocalImage = (img, slideIndex) => {
    const src = img.getAttribute('src');
    // Check if it's a local resource
    if (src && (src.startsWith('file:///') ||
                src.includes('/Temp/') ||
                src.includes('msohtmlclip') ||
                src.startsWith('data:') ||
                src.startsWith('C:/'))) {

        // Immediately replace with placeholder
        img.src = '/images/placeholder-image.png';
        img.classList.add('local-image');

        // Store original source for debugging
        img.setAttribute('data-original-src', 'local-image');

        // Create container for the image
        const container = document.createElement('div');
        container.className = 'image-container relative inline-block';
        img.parentNode.insertBefore(container, img);
        container.appendChild(img);

        // Add replace button
        const replaceBtn = document.createElement('button');
        replaceBtn.textContent = currentLocale.value === 'ar' ? 'استبدال الصورة' : 'Replace Image';
        replaceBtn.className = 'replace-image-btn absolute top-2 right-2 px-2 py-1 bg-blue-500 text-white rounded text-sm';
        container.appendChild(replaceBtn);

        replaceBtn.onclick = (e) => {
            e.stopPropagation();
            missingImageElement.value = img;
            selectedSlideIndex.value = slideIndex;
            replaceImageFromClipboard();
        };
    }
}

// Modified watch function
watch(() => props.modelValue, (newValue) => {
    if (newValue && Array.isArray(newValue)) {
        const processedContent = [...newValue]

        processedContent.forEach((slide, slideIndex) => {
            if (!slide.content) return

            const div = document.createElement('div')
            div.innerHTML = slide.content

            const images = div.getElementsByTagName('img')
            Array.from(images).forEach(img => {
                handleLocalImage(img, slideIndex)
            })

            processedContent[slideIndex].content = div.innerHTML
        })

        form.value.content = processedContent
    }
}, { immediate: true })

const formatTextToHtml = (text) => {
    return text
        // Convert URLs to links
        .replace(
            /(https?:\/\/[^\s]+)/g,
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
        )
        // Convert markdown-style bold or Word's ** format
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        // Convert markdown-style italic or Word's * format
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        // Convert bullet points
        .replace(/^[•\-]\s+(.+)$/gm, '<li>$1</li>')
        // Wrap consecutive li elements in ul
        .replace(/(<li>.*<\/li>)\s*(<li>.*<\/li>)+/g, '<ul>$&</ul>')
        // Convert newlines to <br> tags, but not inside lists
        .replace(/\n(?![^<]*<\/ul>)/g, '<br>')
        // Preserve multiple spaces
        .replace(/\s\s/g, '&nbsp;&nbsp;')
        // Clean up any double-wrapped lists
        .replace(/<ul>\s*<ul>/g, '<ul>')
        .replace(/<\/ul>\s*<\/ul>/g, '</ul>');
};

const cleanWordContent = (html) => {
    const div = document.createElement('div');
    div.innerHTML = html;

    // Remove Word-specific tags and attributes
    const removeAttributes = [
        'class', 'style', 'lang', 'link', 'vlink', 'xmlns:w',
        'xmlns:o', 'xmlns:m', 'xmlns:v', 'xmlns:x', 'xmlns'
    ];

    // Clean up elements recursively
    const cleanElement = (element) => {
        if (element.nodeType === 1) { // Element node
            // Remove Word-specific attributes
            removeAttributes.forEach(attr => element.removeAttribute(attr));

            // Remove empty spans and divs
            if ((element.tagName === 'SPAN' || element.tagName === 'DIV') &&
                !element.attributes.length &&
                !element.textContent.trim()) {
                element.remove();
                return;
            }

            // Clean children
            Array.from(element.children).forEach(cleanElement);
        }
    };

    // Remove unwanted elements
    const removeSelectors = [
        'style', 'script', 'meta', 'link', 'xml', 'o:p',
        '[style*="mso-"]', '[class*="Mso"]', 'comment'
    ];
    removeSelectors.forEach(selector => {
        div.querySelectorAll(selector).forEach(el => el.remove());
    });

    // Clean remaining elements
    cleanElement(div);

    // Handle images
    div.querySelectorAll('img').forEach(img => {
        const src = img.getAttribute('src');
        if (src && (
            src.startsWith('file:///') ||
            src.includes('/Temp/') ||
            src.includes('msohtmlclip') ||
            src.startsWith('data:') ||
            src.startsWith('C:/') ||
            src.includes('word/media/')
        )) {
            img.src = '/images/placeholder-image.png';
            img.classList.add('local-image');
            img.setAttribute('data-original-src', 'local-image');

            // Wrap image in container with replace button
            const container = document.createElement('div');
            container.className = 'image-container relative inline-block';
            img.parentNode.insertBefore(container, img);
            container.appendChild(img);
        }
    });

    // Fix lists
    div.querySelectorAll('li').forEach(li => {
        if (!li.closest('ul, ol')) {
            const ul = document.createElement('ul');
            li.parentNode.insertBefore(ul, li);
            ul.appendChild(li);
        }
    });

    return div.innerHTML;
};

const addFromClipboard = async () => {
    try {
        let content = '';

        // Try to get HTML content first
        const clipboardItems = await navigator.clipboard.read();
        for (const item of clipboardItems) {
            if (item.types.includes('text/html')) {
                const blob = await item.getType('text/html');
                content = await blob.text();
                content = cleanWordContent(content);
                break;
            }
        }

        // Fallback to plain text if no HTML content
        if (!content) {
            const text = await navigator.clipboard.readText();
            content = formatTextToHtml(text);
        }

        if (content) {
            const newContent = {
                id: Date.now(),
                title: `Slide ${form.value.content.length + 1}`,
                content: content
            };

            form.value.content = [...form.value.content, newContent];
            emit('update:modelValue', form.value.content);
        }
    } catch (err) {
        console.error('Clipboard error:', err);
        alert(currentLocale.value === 'ar'
            ? 'فشل في قراءة محتوى الحافظة'
            : 'Failed to read clipboard content.');
    }
};

const addNewSlide = () => {
    showEditor.value = true;
    editingIndex.value = -1;
    currentContent.value = '';
};

const editSlide = (index) => {
    if (!form.value.content || !Array.isArray(form.value.content)) {
        console.warn('Slides array is not properly initialized');
        return;
    }

    editingIndex.value = index;
    currentContent.value = form.value.content[index].content;
    showEditor.value = true;
};

const removeSlide = (index) => {
    const newContent = [...form.value.content];
    newContent.splice(index, 1);
    form.value.content = newContent;
    emit('update:modelValue', form.value.content);
};

const saveSlide = async () => {
    if (!editorRef.value) return;

    try {
        saving.value = true;
        const slideContent = currentContent.value;

        if (!isContentValid(slideContent)) {
            alert(currentLocale.value === 'ar'
                ? 'محتوى الشريحة يحتوي على تنسيق غير صالح ولا يمكن حفظه'
                : 'The slide content contains invalid formatting and cannot be saved');
            return;
        }

        const newContent = [...form.value.content];

        if (editingIndex.value >= 0) {
            newContent[editingIndex.value] = {
                ...newContent[editingIndex.value],
                content: slideContent
            };
        } else {
            newContent.push({
                id: Date.now(),
                title: `Slide ${newContent.length + 1}`,
                content: slideContent
            });
        }

        form.value.content = newContent;
        emit('update:modelValue', form.value.content);

        // Clean up
        closeEditor();
    } catch (error) {
        console.error('Error saving slide:', error);
        alert(currentLocale.value === 'ar'
            ? 'حدث خطأ أثناء حفظ الشريحة. يرجى المحاولة مرة أخرى'
            : 'Unable to save the slide due to an error. Please try again');
    } finally {
        saving.value = false;
    }
};

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
                // Instead of using FileReader, use URL.createObjectURL
                const tempUrl = URL.createObjectURL(file);
                insertFn(tempUrl);
                // Clean up the temporary URL
                URL.revokeObjectURL(tempUrl);
            }
        },
        insertFormula: {
            katex: {
                throwOnError: false,
                output: 'html'
            }
        }
    },
    // Add this to prevent local file loading
    customPaste: (editor, event) => {
        const html = event.clipboardData.getData('text/html');
        if (html) {
            const div = document.createElement('div');
            div.innerHTML = html;

            // Clean local images
            const images = div.getElementsByTagName('img');
            Array.from(images).forEach(img => {
                const src = img.getAttribute('src');
                if (src && (
                    src.startsWith('file:///') ||
                    src.includes('/Temp/') ||
                    src.includes('msohtmlclip') ||
                    src.startsWith('data:') ||
                    src.startsWith('C:/')
                )) {
                    img.src = '/images/placeholder-image.png';
                    img.classList.add('local-image');
                }
            });

            editor.dangerouslyInsertHtml(div.innerHTML);
            return true;
        }
        return false;
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
        'insertFormula',
        '|',
        'undo',
        'redo',
    ]
}

// Editor event handlers
const handleCreated = (editor) => {
    try {
        editorRef.value = editor;

        nextTick(() => {
            if (editingIndex.value >= 0 &&
                form.value.content &&
                Array.isArray(form.value.content) &&
                form.value.content[editingIndex.value]) {

                const content = form.value.content[editingIndex.value].content;
                if (content && editor.setHtml) {
                    try {
                        editor.setHtml(content);
                        currentContent.value = content;
                    } catch (error) {
                        console.error('Error setting editor content:', error);
                        editor.setHtml(''); // Reset to empty if content fails
                        currentContent.value = '';
                        alert('The content could not be loaded into the editor due to formatting issues.');
                        closeEditor();
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error creating editor:', error);
        closeEditor();
        alert('Unable to initialize the editor. Please try again.');
    }
};

const handleChange = (editor) => {
    currentContent.value = editor.getHtml();
};

const handleDestroyed = () => {
    editorRef.value = null;
};

// Ensure cleanup when component is unmounted
onBeforeUnmount(() => {
    if (editorRef.value) {
        editorRef.value.destroy();
        editorRef.value = null;
    }
});

// Add cleanup when modal is closed
const closeEditor = () => {
    if (editorRef.value) {
        editorRef.value.destroy();
        editorRef.value = null;
    }
    showEditor.value = false;
    currentContent.value = '';
    editingIndex.value = -1;
};

// Add a validation function
const isContentValid = (content) => {
    if (!content) return true; // Empty content is considered valid

    try {
        // Check for balanced HTML tags
        const div = document.createElement('div');
        div.innerHTML = content;

        // Check for incomplete KaTeX
        if (content.includes('katex') && !content.includes('katex-html')) {
            return false;
        }

        // Add any other specific validation rules here

        return true;
    } catch (error) {
        console.error('Content validation error:', error);
        return false;
    }
};

// Add new function to save all slides
const saveAllSlides = async () => {
    if (saving.value) return;

    try {
        saving.value = true;

        // Validate required fields
        if (!form.value.title.trim()) {
            alert('Please enter a title');
            return;
        }

        // Validate all slides
        const invalidSlides = form.value.content.filter(slide => !isContentValid(slide.content));
        if (invalidSlides.length > 0) {
            alert('Some slides contain invalid formatting and cannot be saved.');
            return;
        }

        // Emit all form data, not just slides
        emit('submitted', {
            title: form.value.title,
            content: form.value.content,
            type: form.value.type,
            status: form.value.status,
            tags: form.value.tags
        });

    } catch (error) {
        console.error('Error saving slides:', error);
        alert('Unable to save slides due to an error. Please try again.');
    } finally {
        saving.value = false;
    }
};

const handleElementClick = (event, slideIndex) => {
    // Prevent clicking on the content-editable div itself
    if (event.target.classList.contains('content-editable')) {
        return;
    }

    // Remove previous selection
    if (selectedElement.value) {
        selectedElement.value.classList.remove('element-selected');
    }

    // Set new selection
    const clickedElement = event.target;
    clickedElement.classList.add('element-selected');
    selectedElement.value = clickedElement;
    selectedSlideIndex.value = slideIndex;

    // Get current width class
    const widthClasses = ['w-full', 'w-3/4', 'w-1/2', 'w-1/4', 'w-auto'];
    const currentWidthClass = widthClasses.find(cls =>
        clickedElement.classList.contains(cls)
    );
    selectedWidth.value = currentWidthClass || 'w-full';

    // Stop event propagation
    event.stopPropagation();
};

const changeElementWidth = (slideIndex) => {
    if (!selectedElement.value || selectedSlideIndex.value !== slideIndex) return;

    // Remove existing width classes
    selectedElement.value.classList.remove('w-full', 'w-3/4', 'w-1/2', 'w-1/4', 'w-auto');

    // Add new width class
    selectedElement.value.classList.add(selectedWidth.value);

    // Update content in form
    updateSlideContent(slideIndex);
};

const openReplaceModal = (slideIndex) => {
    if (!selectedElement.value || selectedSlideIndex.value !== slideIndex) {
        alert(currentLocale.value === 'ar' ? 'الرجاء تحديد عنصر أولاً' : 'Please select an element first');
        return;
    }
    replacementContent.value = selectedElement.value.innerHTML;
    replaceModalOpen.value = true;
};

const closeReplaceModal = () => {
    replaceModalOpen.value = false;
    replacementContent.value = '';
};

const handleImageError = (event) => {
    handleMissingImage(event);
};

const handleMissingImage = (event) => {
    const img = event.target;
    img.classList.add('missing-image');

    // Create replace button
    const replaceBtn = document.createElement('button');
    replaceBtn.textContent = locale.value === 'ar' ? 'استبدال من الحافظة' : 'Replace from Clipboard';
    replaceBtn.className = 'replace-image-btn px-2 py-1 bg-blue-500 text-white rounded text-sm absolute top-2 right-2';
    replaceBtn.onclick = (e) => {
        e.stopPropagation();
        missingImageElement.value = img;
        replaceImageFromClipboard();
    };

    // Add button next to image
    img.parentNode.style.position = 'relative';
    img.parentNode.appendChild(replaceBtn);
};

const replaceImageFromClipboard = async () => {
    try {
        const clipboardItems = await navigator.clipboard.read();

        for (const item of clipboardItems) {
            const imageTypes = item.types.filter(type => type.startsWith('image/'));

            if (imageTypes.length > 0) {
                const imageType = imageTypes[0];
                const blob = await item.getType(imageType);
                const reader = new FileReader();

                reader.onload = (e) => {
                    if (missingImageElement.value) {
                        missingImageElement.value.src = e.target.result;
                        missingImageElement.value.classList.remove('local-image');
                        updateSlideContent(selectedSlideIndex.value);
                    }
                };

                reader.readAsDataURL(blob);
                return;
            }
        }

        alert(currentLocale.value === 'ar' ? 'لم يتم العثور على صورة في الحافظة' : 'No image found in clipboard');
    } catch (err) {
        console.error('Clipboard error:', err);
        alert(currentLocale.value === 'ar'
            ? 'فشل في قراءة الحافظة. يرجى التأكد من منح إذن الحافظة'
            : 'Failed to read clipboard. Please make sure you have granted clipboard permission.');
    }
};

const replaceContent = () => {
    if (!selectedElement.value || selectedSlideIndex.value === null) {
        console.warn('No element or slide selected');
        return;
    }

    try {
        // Get the parent content-editable div
        const contentEditableDiv = selectedElement.value.closest('.content-editable');
        if (!contentEditableDiv) {
            throw new Error('Content editable container not found');
        }

        // Update the selected element's content
        selectedElement.value.innerHTML = replacementContent.value;

        // Update the form content with the entire content-editable div's HTML
        if (Array.isArray(form.value.content) && form.value.content[selectedSlideIndex.value]) {
            form.value.content[selectedSlideIndex.value].content = contentEditableDiv.innerHTML;
            emit('update:modelValue', form.value.content);
        }

        // Close the modal
        closeReplaceModal();
    } catch (error) {
        console.error('Error replacing content:', error);
        alert(currentLocale.value === 'ar'
            ? 'حدث خطأ أثناء تحديث المحتوى'
            : 'An error occurred while updating the content');
    }
};

const updateSlideContent = (slideIndex) => {
    if (!Array.isArray(form.value.content) || !form.value.content[slideIndex]) {
        console.warn('Invalid slide index or content array');
        return;
    }

    try {
        const contentDiv = document.createElement('div');
        contentDiv.innerHTML = form.value.content[slideIndex].content;
        form.value.content[slideIndex].content = contentDiv.innerHTML;
        emit('update:modelValue', form.value.content);
    } catch (error) {
        console.error('Error updating slide content:', error);
        alert(currentLocale.value === 'ar'
            ? 'حدث خطأ أثناء تحديث المحتوى'
            : 'An error occurred while updating the content');
    }
};

const deleteSelectedElement = (slideIndex) => {
    if (!selectedElement.value || selectedSlideIndex.value !== slideIndex) {
        alert(locale.value === 'ar' ? 'الرجاء تحديد عنصر أولاً' : 'Please select an element first');
        return;
    }

    selectedElement.value.remove();
    updateSlideContent(slideIndex);
    selectedElement.value = null;
    selectedSlideIndex.value = null;
};

// Add click handler to clear selection when clicking outside
onMounted(() => {
    const handleClickOutside = (event) => {
        // Only clear selection if clicking outside content-editable and replace modal
        if (!event.target.closest('.content-editable') &&
            !event.target.closest('.replace-modal') &&
            !replaceModalOpen.value) {
            if (selectedElement.value) {
                selectedElement.value.classList.remove('element-selected');
                selectedElement.value = null;
                selectedSlideIndex.value = null;
            }
        }
    };

    document.addEventListener('click', handleClickOutside);

    onBeforeUnmount(() => {
        document.removeEventListener('click', handleClickOutside);
    });
});

onBeforeUnmount(() => {
    document.removeEventListener('click', () => {});
});
</script>

<style scoped>
.content-editable {
    position: relative;
    min-height: 100px;
    padding: 1rem;
}

.content-editable :deep(*) {
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s ease;
}

.content-editable :deep(*:hover) {
    border-color: #4f46e5;
}

.content-editable :deep(.element-selected) {
    border: 2px solid #2563eb;
    background-color: rgba(37, 99, 235, 0.1);
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

/* Editor styles */
:deep(.w-e-text-container) {
    background-color: #fff !important;
}

.missing-image {
    min-width: 100px;
    min-height: 100px;
    background-color: #f3f4f6;
    border: 2px dashed #d1d5db;
    padding: 1rem;
    display: block;
}

.replace-image-btn {
    z-index: 10;
    opacity: 0.9;
    transition: opacity 0.2s;
}

.replace-image-btn:hover {
    opacity: 1;
}

.local-image {
    min-width: 100px;
    min-height: 50px;
    border: 2px dashed #d1d5db;
    background-color: #f3f4f6;
    padding: 4px;
}

.image-container {
    display: inline-block;
    position: relative;
}

.image-container:hover .replace-image-btn {
    display: block;
}

/* Add these styles to your existing <style> section */
.image-container {
    position: relative;
    display: inline-block;
}

.image-container:hover .replace-image-btn {
    opacity: 1;
}

.local-image {
    max-width: 100%;
    height: auto;
    border: 2px dashed #cbd5e1;
    padding: 4px;
}

.replace-image-btn {
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}

.replace-modal {
    max-width: 600px;
    width: 100%;
}

.element-selected {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}
</style>

<style>
/* Global styles for WangEditor */
.w-e-toolbar {
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

.w-e-text-container {
    border-bottom-left-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}
</style>

























