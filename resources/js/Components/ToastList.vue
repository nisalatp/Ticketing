<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

const toasts = ref([]);
const page = usePage();

const addToast = (message, type = 'success') => {
    const id = Date.now();
    toasts.value.push({
        id,
        message,
        type
    });
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

// Expose addToast for manual calls from components
defineExpose({ addToast });

// Listen for Inertia flush messages
watch(() => page.props.flash, (flash) => {
    if (flash?.message) {
        addToast(flash.message, 'success');
    }
    if (flash?.error) {
        addToast(flash.error, 'error');
    }
    if (flash?.warning) {
        addToast(flash.warning, 'warning');
    }
}, { deep: true, immediate: true });

// Listen for validation errors
watch(() => page.props.errors, (errors) => {
    const errorKeys = Object.keys(errors);
    if (errorKeys.length > 0) {
        // Just show the first one or two to avoid overwhelming
        errorKeys.slice(0, 2).forEach(key => {
            addToast(errors[key], 'error');
        });
    }
}, { deep: true });
</script>

<template>
    <div class="fixed top-20 right-6 z-[100] flex flex-col items-end pointer-events-none">
        <Toast 
            v-for="toast in toasts" 
            :key="toast.id" 
            :message="toast.message" 
            :type="toast.type"
            @remove="removeToast(toast.id)"
        />
    </div>
</template>
