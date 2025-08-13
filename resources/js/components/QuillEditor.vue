<template>
  <div>
    <div class="p-2 bg-blue-100 border border-blue-300 rounded mb-2">
      <p class="text-blue-800 text-sm">QuillEditor Component Loaded</p>
    </div>
    <div v-if="!quillInitialized" class="p-4 border border-gray-300 rounded-md bg-gray-50">
      <p class="text-gray-600">Loading Quill Editor...</p>
    </div>
    <div ref="editorContainer" class="quill-editor-container"></div>
    <InputError :message="error" class="mt-2" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import InputError from '@/components/InputError.vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Write something...'
  },
  error: {
    type: String,
    default: ''
  },
  height: {
    type: String,
    default: '200px'
  }
});

const emit = defineEmits(['update:modelValue']);

const editorContainer = ref(null);
const quillInitialized = ref(false);
let quill = null;

onMounted(async () => {
  console.log('QuillEditor: onMounted called');
  console.log('QuillEditor: editorContainer.value:', editorContainer.value);
  
  try {
    // Dynamic import to avoid SSR issues
    const Quill = (await import('quill')).default;
    await import('quill/dist/quill.snow.css');
    
    console.log('QuillEditor: Quill imported successfully');
    
    // Initialize Quill editor
    quill = new Quill(editorContainer.value, {
      theme: 'snow',
      placeholder: props.placeholder,
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline'],
          [{ 'list': 'ordered'}, { 'list': 'bullet' }],
          ['link']
        ]
      }
    });

    console.log('QuillEditor: Quill initialized:', quill);
    quillInitialized.value = true;

    // Set initial content
    if (props.modelValue) {
      quill.root.innerHTML = props.modelValue;
    }

    // Listen for text changes
    quill.on('text-change', () => {
      const html = quill.root.innerHTML;
      emit('update:modelValue', html);
    });

    // Set editor height
    if (props.height) {
      quill.root.style.height = props.height;
    }
  } catch (error) {
    console.error('QuillEditor: Error initializing Quill:', error);
  }
});

onUnmounted(() => {
  if (quill) {
    quill = null;
  }
});

// Watch for external changes to modelValue
watch(() => props.modelValue, (newValue) => {
  if (quill && newValue !== quill.root.innerHTML) {
    quill.root.innerHTML = newValue;
  }
});
</script>

<style scoped>
.quill-editor-container {
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: white;
}

.quill-editor-container:hover {
  border-color: #9ca3af;
}

.quill-editor-container:focus-within {
  border-color: #6366f1;
  box-shadow: 0 0 0 1px #6366f1;
}

/* Custom Quill styles */
:deep(.ql-editor) {
  min-height: 120px;
  font-size: 14px;
  line-height: 1.5;
}

:deep(.ql-toolbar) {
  border-top: none;
  border-left: none;
  border-right: none;
  border-bottom: 1px solid #d1d5db;
  background-color: #f9fafb;
}

:deep(.ql-container) {
  border: none;
}

:deep(.ql-editor.ql-blank::before) {
  color: #9ca3af;
  font-style: italic;
}
</style>
