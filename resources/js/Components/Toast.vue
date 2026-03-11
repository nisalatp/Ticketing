<script setup>
import { onMounted } from 'vue';
import { 
    CheckCircleIcon, 
    ExclamationCircleIcon, 
    XCircleIcon,
    XMarkIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    message: String,
    type: {
        type: String,
        default: 'success'
    },
    duration: {
        type: Number,
        default: 5000
    }
});

const emit = defineEmits(['remove']);

onMounted(() => {
    setTimeout(() => {
        emit('remove');
    }, props.duration);
});
</script>

<template>
    <div 
        class="flex items-center gap-3 p-4 mb-3 rounded-2xl shadow-xl border backdrop-blur-md transition-all duration-500 animate-in fade-in slide-in-from-right-10 pointer-events-auto min-w-[320px] max-w-md"
        :class="[
            type === 'success' ? 'bg-emerald-50/90 border-emerald-100 text-emerald-800' :
            type === 'warning' ? 'bg-amber-50/90 border-amber-100 text-amber-800' :
            'bg-rose-50/90 border-rose-100 text-rose-800'
        ]"
    >
        <div :class="[
            'w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm',
            type === 'success' ? 'bg-emerald-100' :
            type === 'warning' ? 'bg-amber-100' :
            'bg-rose-100'
        ]">
            <CheckCircleIcon v-if="type === 'success'" class="w-6 h-6" />
            <ExclamationCircleIcon v-else-if="type === 'warning'" class="w-6 h-6" />
            <XCircleIcon v-else class="w-6 h-6" />
        </div>

        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold tracking-tight">
                {{ type === 'success' ? 'Success' : type === 'warning' ? 'Warning' : 'Error' }}
            </p>
            <p class="text-xs font-medium opacity-90 leading-snug">{{ message }}</p>
        </div>

        <button @click="emit('remove')" class="p-1 hover:bg-white/50 rounded-lg transition-colors opacity-50 hover:opacity-100">
            <XMarkIcon class="w-4 h-4" />
        </button>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}
.animate-in {
    animation: fade-in 0.3s ease-out forwards;
}
</style>
