<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    assignedAt: String,
    breachAt: String,
    status: String,
});

const now = ref(new Date());
let intervalId = null;

const formatDuration = (start, end) => {
    if (!start) return '--';
    
    const startTime = new Date(start).getTime();
    const endTime = end.getTime();
    let diff = Math.abs(endTime - startTime);

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    diff -= days * (1000 * 60 * 60 * 24);

    const hours = Math.floor(diff / (1000 * 60 * 60));
    diff -= hours * (1000 * 60 * 60);

    const minutes = Math.floor(diff / (1000 * 60));
    diff -= minutes * (1000 * 60);

    const seconds = Math.floor(diff / 1000);

    const parts = [];
    if (days > 0) parts.push(`${days}d`);
    if (hours > 0 || days > 0) parts.push(`${hours}h`);
    parts.push(`${minutes}m`);
    parts.push(`${seconds}s`);

    return parts.join(' ');
};

const elapsedTime = computed(() => {
    if (!props.assignedAt) return 'Unassigned';
    return formatDuration(props.assignedAt, now.value);
});

const isBreached = computed(() => {
    if (!props.breachAt) return false;
    if (props.status === 'Resolved' || props.status === 'Closed') return false;
    return now.value > new Date(props.breachAt);
});

onMounted(() => {
    intervalId = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});
</script>

<template>
    <div :class="[
        'flex items-center gap-1 font-black tabular-nums transition-colors duration-300',
        isBreached ? 'text-csired' : 'text-emerald-500'
    ]" :title="isBreached ? 'SLA Breached' : 'Within SLA'">
        <slot name="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </slot>
        <span>{{ elapsedTime }}</span>
    </div>
</template>
