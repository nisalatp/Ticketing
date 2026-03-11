<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    TicketIcon, 
    CheckCircleIcon, 
    ClockIcon, 
    ExclamationTriangleIcon,
    ChevronRightIcon,
    ChevronDownIcon,
    Squares2X2Icon
} from '@heroicons/vue/24/outline';
import TicketTimer from '@/Components/TicketTimer.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: Object,
    groupedTickets: Object,
    filters: Object,
});

const collapsedSections = ref({
    unassigned: false,
    progressing: false,
    completed: true,
});

const toggleSection = (section) => {
    collapsedSections.value[section] = !collapsedSections.value[section];
};

const parsedStats = computed(() => [
    { name: 'Total Tickets', value: props.stats.total, icon: TicketIcon, color: 'text-csired', bg: 'bg-red-50' },
    { name: 'Open Tickets', value: props.stats.open, icon: ClockIcon, color: 'text-amber-600', bg: 'bg-amber-100' },
    { name: 'Resolved', value: props.stats.resolved, icon: CheckCircleIcon, color: 'text-emerald-600', bg: 'bg-emerald-100' },
    { name: 'Escalated', value: props.stats.escalated, icon: ExclamationTriangleIcon, color: 'text-rose-600', bg: 'bg-rose-100' },
]);

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffInMs = now - date;
    const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
    
    if (diffInDays === 0) {
        return 'Today';
    } else if (diffInDays === 1) {
        return 'Yesterday';
    } else if (diffInDays < 7) {
        return `${diffInDays} days ago`;
    } else {
        return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                        <Squares2X2Icon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Welcome back, {{ $page.props.auth.user.name }}</h1>
                        <p class="text-xs text-slate-500 font-medium tracking-tight mt-0.5">Here is what's happening with your support requests today.</p>
                    </div>
                </div>
                <Link :href="route('tickets.create')" class="btn-primary flex items-center gap-2">
                    <TicketIcon class="h-5 w-5" />
                    New Ticket
                </Link>
            </div>
        </template>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div v-for="stat in parsedStats" :key="stat.name" class="card-nova px-6 py-5 flex items-center gap-4 group hover:border-csired/20 transition-all duration-300">
                <div :class="['p-3 rounded-2xl transition-transform group-hover:scale-110 duration-300', stat.bg]">
                    <component :is="stat.icon" :class="['h-6 w-6', stat.color]" />
                </div>
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">{{ stat.name }}</p>
                    <p class="text-3xl font-black text-slate-900 tracking-tight">{{ stat.value }}</p>
                </div>
            </div>
        </div>

        <!-- Grouped Tickets Sections -->
        <div class="space-y-6">
            <div v-for="(tickets, group) in groupedTickets" :key="group" class="space-y-4">
                <!-- Section Header -->
                <button 
                    @click="toggleSection(group)"
                    class="w-full flex items-center justify-between p-4 bg-slate-50/50 rounded-2xl border border-slate-100 hover:bg-slate-50 transition-colors group"
                >
                    <div class="flex items-center gap-3">
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            {{ group }}
                            <span class="bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full text-[10px] font-black">
                                {{ tickets.length }}
                            </span>
                        </h2>
                    </div>
                    <component 
                        :is="collapsedSections[group] ? ChevronRightIcon : ChevronDownIcon" 
                        class="w-4 h-4 text-slate-400 group-hover:text-csired transition-colors"
                    />
                </button>

                <!-- Tickets Grid -->
                <div v-show="!collapsedSections[group]">
                    <div v-if="tickets.length === 0" class="py-12 text-center text-slate-400 font-bold text-[10px] uppercase tracking-widest bg-white rounded-2xl border border-dashed border-slate-200">
                         No tickets in this section
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                        <div v-for="ticket in tickets" :key="ticket.id" 
                            @click="$inertia.visit(route('tickets.show', ticket.id))"
                            class="card-nova p-5 hover:border-csired/30 transition-all cursor-pointer group flex flex-col h-full hover:shadow-xl hover:shadow-red-500/5"
                        >
                            <!-- Card Header -->
                            <div class="flex items-start justify-between mb-4">
                                <span class="text-[10px] font-black text-slate-400 tracking-widest uppercase">{{ ticket.ticket_no }}</span>
                                <div class="flex flex-col items-end gap-2">
                                    <span :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border',
                                        ticket.status === 'Resolved' || ticket.status === 'Closed' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' :
                                        ticket.status === 'Waiting' ? 'bg-amber-50 text-amber-600 border-amber-100' :
                                        'bg-red-50 text-csired border-red-100'
                                    ]">
                                        {{ ticket.status }}
                                    </span>
                                    <span :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border shadow-sm',
                                        ticket.priority === 'High' || ticket.priority === 'Urgent' ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-slate-50 text-slate-500 border-slate-100'
                                    ]">
                                        {{ ticket.priority }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <h3 class="font-black text-slate-800 group-hover:text-csired transition-colors uppercase tracking-tight text-sm leading-tight mb-4 flex-1">
                                {{ ticket.subject }}
                            </h3>

                            <!-- Card Footer -->
                            <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] font-bold text-slate-400">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 uppercase">
                                        {{ ticket.assignee ? ticket.assignee.name.charAt(0) : '?' }}
                                    </div>
                                    <span class="truncate max-w-[100px]">{{ ticket.assignee?.name || 'Unassigned' }}</span>
                                </div>
                                <div class="flex flex-col items-end gap-1 shrink-0">
                                    <div class="flex items-center gap-1">
                                        <ClockIcon class="w-3 h-3" />
                                        {{ formatDate(ticket.created_at) }}
                                    </div>
                                    <TicketTimer 
                                        :assigned-at="ticket.assigned_at" 
                                        :breach-at="ticket.sla_breach_at"
                                        :status="ticket.status"
                                        class="text-[9px]"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
