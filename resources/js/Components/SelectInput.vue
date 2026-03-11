<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: [String, Number, Boolean, null],
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <select
        class="border-slate-300 focus:border-csired focus:ring-csired rounded-xl shadow-sm bg-white text-sm"
        v-model="model"
        ref="input"
    >
        <slot />
    </select>
</template>
