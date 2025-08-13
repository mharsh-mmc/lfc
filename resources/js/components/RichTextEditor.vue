<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import Quill from 'quill'
import 'quill/dist/quill.snow.css'

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: 'Start writing...'
    }
})

const emit = defineEmits(['update:modelValue'])

const editorRef = ref(null)
const quillEditor = ref(null)

// Quill toolbar configuration to match the image
const toolbarOptions = [
    ['bold', 'italic', 'underline'],
    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
    ['link']
]

onMounted(() => {
    nextTick(() => {
        if (editorRef.value) {
            // Initialize Quill editor
            quillEditor.value = new Quill(editorRef.value, {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: props.placeholder,
                readOnly: false
            })

            // Set initial content
            if (props.modelValue) {
                quillEditor.value.root.innerHTML = props.modelValue
            }

            // Listen for text changes
            quillEditor.value.on('text-change', () => {
                const html = quillEditor.value.root.innerHTML
                emit('update:modelValue', html)
            })
        }
    })
})

// Watch for modelValue changes
watch(() => props.modelValue, (newValue) => {
    if (quillEditor.value && quillEditor.value.root.innerHTML !== newValue) {
        quillEditor.value.root.innerHTML = newValue
    }
})

// Watch for placeholder changes
watch(() => props.placeholder, (newPlaceholder) => {
    if (quillEditor.value) {
        quillEditor.value.root.setAttribute('data-placeholder', newPlaceholder)
    }
})
</script>

<template>
    <div class="quill-editor-container">
        <!-- Quill editor will be mounted here -->
        <div ref="editorRef" class="quill-editor"></div>
    </div>
</template>

<style scoped>
.quill-editor-container {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    overflow: hidden;
}

.quill-editor {
    min-height: 200px;
}

/* Custom Quill styles to match the image */
:deep(.ql-toolbar) {
    background-color: #f9fafb;
    border-bottom: 1px solid #d1d5db;
    padding: 8px;
}

:deep(.ql-container) {
    border: none;
    font-size: 14px;
    line-height: 1.5;
}

:deep(.ql-editor) {
    padding: 16px;
    min-height: 200px;
    outline: none;
}

:deep(.ql-editor.ql-blank::before) {
    color: #9ca3af;
    font-style: italic;
    left: 16px;
    right: 16px;
}

/* Button styles to match the image */
:deep(.ql-toolbar button) {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    margin-right: 2px;
    transition: background-color 0.2s;
}

:deep(.ql-toolbar button:hover) {
    background-color: #e5e7eb;
}

:deep(.ql-toolbar button.ql-active) {
    background-color: #dbeafe;
    color: #2563eb;
}

/* Icon styles */
:deep(.ql-bold) {
    font-weight: bold;
}

:deep(.ql-italic) {
    font-style: italic;
}

:deep(.ql-underline) {
    text-decoration: underline;
}

/* List styles */
:deep(.ql-list[data-value="ordered"]) {
    list-style-type: decimal;
}

:deep(.ql-list[data-value="bullet"]) {
    list-style-type: disc;
}

/* Link styles */
:deep(.ql-link) {
    color: #2563eb;
    text-decoration: underline;
}

/* Content area styles */
:deep(.ql-editor p) {
    margin: 0 0 8px 0;
}

:deep(.ql-editor ul),
:deep(.ql-editor ol) {
    padding-left: 20px;
    margin: 8px 0;
}

:deep(.ql-editor li) {
    margin: 4px 0;
}

:deep(.ql-editor a) {
    color: #2563eb;
    text-decoration: underline;
}

:deep(.ql-editor a:hover) {
    text-decoration: none;
}
</style> 