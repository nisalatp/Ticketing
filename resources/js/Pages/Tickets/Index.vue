<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

let refreshInterval = null;
onMounted(() => { refreshInterval = setInterval(() => router.reload({ preserveScroll: true, preserveState: true }), 10000); });
onUnmounted(() => { clearInterval(refreshInterval); });
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TicketTimer from '@/Components/TicketTimer.vue';
import { 
    TicketIcon, 
    PlusIcon,
    ExclamationTriangleIcon,
    DocumentTextIcon,
    InformationCircleIcon,
    ChevronRightIcon,
    ChevronDownIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    groupedTickets: Object,
});

const collapsedSections = ref({
    unassigned: false,
    progressing: false,
    completed: true,
});

const toggleSection = (section) => {
    collapsedSections.value[section] = !collapsedSections.value[section];
};

const getPriorityColor = (priority) => {
    switch (priority) {
        case 'Urgent': return 'bg-red-50 text-red-700 border-red-100';
        case 'High': return 'bg-orange-50 text-orange-700 border-orange-100';
        case 'Medium': return 'bg-blue-50 text-blue-700 border-blue-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'Assigned': return 'bg-blue-50 text-blue-800 border-blue-100';
        case 'In Progress': return 'bg-indigo-50 text-indigo-800 border-indigo-100';
        case 'Waiting': return 'bg-amber-50 text-amber-800 border-amber-100';
        case 'Resolved': return 'bg-emerald-50 text-emerald-800 border-emerald-100';
        case 'Closed': return 'bg-slate-50 text-slate-800 border-slate-100';
        case 'Pending': return 'bg-slate-50 text-slate-600 border-slate-100 italic';
        default: return 'bg-slate-50 text-slate-800 border-slate-100';
    }
};
</script>

<template>
    <Head title="My Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                        <TicketIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">My Tickets</h1>
                        <p class="text-xs text-slate-500 font-medium tracking-tight">Track the status of your requests and complaints.</p>
                    </div>
                </div>
                <Link :href="route('tickets.create')" class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-csired rounded-xl hover:bg-[#D31920] active:bg-[#B3151B] transition-all shadow-lg shadow-red-100/50">
                    <PlusIcon class="w-4 h-4" />
                    New Ticket
                </Link>
            </div>
        </template>

        <div class="py-10 space-y-8">
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
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <div v-for="ticket in tickets" :key="ticket.id" 
                            @click="$inertia.visit(route('tickets.show', ticket.id))" 
                            class="card-nova p-6 hover:border-csired/30 transition-all cursor-pointer group flex flex-col h-full hover:shadow-xl hover:shadow-red-500/5"
                        >
                            <!-- Card Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-black text-slate-400 tracking-widest uppercase">{{ ticket.ticket_no }}</span>
                                    <span v-if="ticket.type" :class="['w-max px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest border', ticket.type.color_code]">
                                        {{ ticket.type.name }}
                                    </span>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span :class="['px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border', getStatusColor(ticket.status)]">
                                        {{ ticket.status }}
                                    </span>
                                    <span :class="['px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border shadow-sm', getPriorityColor(ticket.priority)]">
                                        {{ ticket.priority }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="flex-1 mb-6">
                                <h3 class="font-black text-slate-800 group-hover:text-csired transition-colors uppercase tracking-tight text-sm leading-tight mb-2">
                                    {{ ticket.subject }}
                                </h3>
                                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <span>{{ ticket.department?.name || 'Management Services' }}</span>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] font-bold text-slate-400">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                                        <img v-if="ticket.assignee?.profile_photo_path" :src="'/storage/' + ticket.assignee.profile_photo_path" class="w-full h-full object-cover" @error="$event.target.style.display = 'none'; $event.target.nextElementSibling.style.display = 'flex'">
                                        <span class="text-[10px] font-black text-slate-400 uppercase" :class="{'hidden': ticket.assignee?.profile_photo_path}">{{ ticket.assignee ? ticket.assignee.name.charAt(0) : '?' }}</span>
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-slate-700 truncate max-w-[120px]">{{ ticket.assignee?.name || 'Unassigned' }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <div class="flex flex-col items-end">
                                        <span class="text-slate-300 uppercase leading-none mb-0.5">Created</span>
                                        <span class="text-slate-500 whitespace-nowrap">{{ new Date(ticket.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
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
