<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
  variant?: 'default' | 'secondary' | 'destructive' | 'outline';
  size?: 'default' | 'sm' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'default',
});

const classes = computed(() => {
  return cn(
    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    {
      'border-transparent bg-primary text-primary-foreground hover:bg-primary/80': props.variant === 'default',
      'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80': props.variant === 'secondary',
      'border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80': props.variant === 'destructive',
      'text-foreground': props.variant === 'outline',
      'px-2 py-0.5 text-xs': props.size === 'sm',
      'px-3 py-1 text-sm': props.size === 'lg',
    }
  );
});
</script>

<template>
  <div :class="classes">
    <slot />
  </div>
</template> 