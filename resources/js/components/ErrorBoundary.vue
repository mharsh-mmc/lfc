<template>
  <div v-if="hasError" class="error-boundary">
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-2xl mx-auto">
      <div class="flex items-center space-x-3 mb-4">
        <div class="flex-shrink-0">
          <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-medium text-red-800">Something went wrong</h3>
          <p class="text-sm text-red-600">The family tree encountered an error and couldn't load properly.</p>
        </div>
      </div>
      
      <div class="bg-red-100 border border-red-200 rounded-md p-4 mb-4">
        <div class="text-sm text-red-800">
          <strong>Error Details:</strong>
          <pre class="mt-2 text-xs overflow-auto">{{ errorDetails }}</pre>
        </div>
      </div>
      
      <div class="flex items-center justify-between">
        <div class="text-sm text-red-600">
          <p>This might be due to:</p>
          <ul class="list-disc list-inside mt-1 space-y-1">
            <li>Missing API endpoints</li>
            <li>Network connectivity issues</li>
            <li>Browser compatibility problems</li>
            <li>Data format issues</li>
          </ul>
        </div>
        
        <div class="flex space-x-3">
          <button
            @click="retry"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
          >
            Try Again
          </button>
          <button
            @click="reset"
            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
          >
            Reset
          </button>
        </div>
      </div>
      
      <div class="mt-4 pt-4 border-t border-red-200">
        <p class="text-xs text-red-500">
          If the problem persists, please contact support or refresh the page.
        </p>
      </div>
    </div>
  </div>
  
  <slot v-else />
</template>

<script setup lang="ts">
import { ref, onErrorCaptured, provide } from 'vue';

const hasError = ref(false);
const errorDetails = ref('');

// Provide error handling to child components
provide('reportError', (error: Error, context?: string) => {
  handleError(error, context);
});

const handleError = (error: Error, context?: string = 'Unknown'): void => {
  console.error('Error caught by boundary:', error);
  console.error('Error context:', context);
  
  hasError.value = true;
  errorDetails.value = `Context: ${context}\nError: ${error.message}\nStack: ${error.stack || 'No stack trace available'}`;
};

const retry = (): void => {
  hasError.value = false;
  errorDetails.value = '';
  // Emit retry event for parent components
  emit('retry');
};

const reset = (): void => {
  hasError.value = false;
  errorDetails.value = '';
  // Emit reset event for parent components
  emit('reset');
};

const emit = defineEmits<{
  retry: [];
  reset: [];
}>();

// Capture errors from child components
onErrorCaptured((error: Error, instance: any, info: string) => {
  handleError(error, `Component: ${instance?.$options?.name || 'Unknown'}, Info: ${info}`);
  return false; // Prevent error from propagating further
});
</script>

<style scoped>
.error-boundary {
  @apply w-full h-full flex items-center justify-center p-4;
}

pre {
  @apply bg-white bg-opacity-50 rounded p-2;
}
</style>