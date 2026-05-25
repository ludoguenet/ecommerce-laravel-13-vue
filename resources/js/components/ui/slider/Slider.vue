<script setup lang="ts">
import type { SliderRootEmits, SliderRootProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { SliderRange, SliderRoot, SliderThumb, SliderTrack, useForwardPropsEmits } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<SliderRootProps & { class?: HTMLAttributes['class'] }>();
const emits = defineEmits<SliderRootEmits>();

const delegatedProps = reactiveOmit(props, 'class');
const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <SliderRoot
        data-slot="slider"
        v-bind="forwarded"
        :class="cn('relative flex w-full touch-none select-none items-center', props.class)"
    >
        <SliderTrack data-slot="slider-track" class="relative h-1.5 w-full grow overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-800">
            <SliderRange data-slot="slider-range" class="absolute h-full bg-neutral-900 dark:bg-neutral-50" />
        </SliderTrack>
        <SliderThumb
            v-for="(_, i) in modelValue ?? [0]"
            :key="i"
            data-slot="slider-thumb"
            class="border-neutral-900 bg-white shadow-sm dark:border-neutral-50 dark:bg-neutral-950 block size-4 shrink-0 rounded-full border-2 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:focus-visible:ring-neutral-300 dark:focus-visible:ring-offset-neutral-950"
        />
    </SliderRoot>
</template>
