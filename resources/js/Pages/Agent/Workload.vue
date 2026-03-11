<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

let refreshInterval = null;
onMounted(() => { refreshInterval = setInterval(() => router.reload({ preserveScroll: true, preserveState: true }), 10000); });
onUnmounted(() => { clearInterval(refreshInterval); });
import { 
    ExclamationTriangleIcon, 
    CheckCircleIcon, 
    ClockIcon, 
    InboxIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline';

defineProps({
    statusCounts: Object,
    priorityCounts: Object,
    urgentTickets: Array,
    metrics: Object,
});
</script>

<template>
    <Head title="Agent Workload" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                    <ChartBarIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">My Workload Dashboard</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-tight">Real-time capacity and urgency metrics for your assigned tickets.</p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-slate-400 flex items-center gap-4">
                        <div class="p-3 bg-slate-50 rounded-full text-slate-500">
                            <InboxIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Assigned</p>
                            <p class="text-2xl font-bold text-slate-900">{{ (statusCounts['Assigned'] || 0) + (statusCounts['New'] || 0) + (statusCounts['In Progress'] || 0) }}</p>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-csired flex items-center gap-4">
                        <div class="p-3 bg-red-50 rounded-full text-csired">
                            <ExclamationTriangleIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Urgent/High</p>
                            <p class="text-2xl font-bold text-slate-900">{{ (priorityCounts['Urgent'] || 0) + (priorityCounts['High'] || 0) }}</p>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-amber-500 flex items-center gap-4">
                        <div class="p-3 bg-amber-50 rounded-full text-amber-500">
                            <ClockIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Waiting</p>
                            <p class="text-2xl font-bold text-slate-900">{{ statusCounts['Waiting'] || 0 }}</p>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-emerald-500 flex items-center gap-4">
                        <div class="p-3 bg-emerald-50 rounded-full text-emerald-500">
                            <CheckCircleIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Resolved</p>
                            <p class="text-2xl font-bold text-slate-900">{{ statusCounts['Resolved'] || 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Advanced Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-200 flex flex-col justify-center text-center">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Agent Efficiency</h3>
                        <div class="text-3xl font-black" :class="metrics?.efficiency >= 90 ? 'text-emerald-600' : (metrics?.efficiency >= 75 ? 'text-amber-500' : 'text-csired')">
                            {{ metrics?.efficiency || 100 }}%
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-200 flex flex-col justify-center text-center">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Backlog Weight</h3>
                        <div class="text-3xl font-black text-slate-900">{{ metrics?.backlogWeight || 0 }}</div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-200 flex flex-col justify-center text-center">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">7d Burndown</h3>
                        <div class="text-3xl font-black text-slate-900">{{ metrics?.burndownRate || 0 }}%</div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-200 flex flex-col justify-center text-center">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Transferred Out</h3>
                        <div class="text-3xl font-black text-orange-600">{{ metrics?.transfersOut || 0 }}</div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-5 border border-slate-200 flex flex-col justify-center items-center">
                        <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 text-center">Completed (Urgency)</h3>
                        <div class="flex gap-2">
                            <div title="Urgent" class="flex flex-col items-center">
                                <div class="h-6 w-6 rounded-md bg-red-100 text-red-700 flex items-center justify-center text-xs font-bold">{{ metrics?.resolvedByUrgency?.Urgent || 0 }}</div>
                                <span class="text-[8px] mt-1 text-slate-400 uppercase font-bold tracking-tighter">Urg</span>
                            </div>
                            <div title="High" class="flex flex-col items-center">
                                <div class="h-6 w-6 rounded-md bg-orange-100 text-orange-700 flex items-center justify-center text-xs font-bold">{{ metrics?.resolvedByUrgency?.High || 0 }}</div>
                                <span class="text-[8px] mt-1 text-slate-400 uppercase font-bold tracking-tighter">Hgh</span>
                            </div>
                            <div title="Medium" class="flex flex-col items-center">
                                <div class="h-6 w-6 rounded-md bg-yellow-100 text-yellow-700 flex items-center justify-center text-xs font-bold">{{ metrics?.resolvedByUrgency?.Medium || 0 }}</div>
                                <span class="text-[8px] mt-1 text-slate-400 uppercase font-bold tracking-tighter">Med</span>
                            </div>
                            <div title="Low" class="flex flex-col items-center">
                                <div class="h-6 w-6 rounded-md bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold">{{ metrics?.resolvedByUrgency?.Low || 0 }}</div>
                                <span class="text-[8px] mt-1 text-slate-400 uppercase font-bold tracking-tighter">Low</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Priority Pipeline & Urgent Tickets -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1 bg-white shadow-sm sm:rounded-lg border border-slate-200 p-6">
                        <h3 class="text-lg font-medium text-slate-900 flex items-center gap-2 mb-4">
                            <ChartBarIcon class="w-5 h-5 text-slate-500" />
                            Active Priority Breakdown
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-sm font-medium mb-1">
                                    <span class="text-red-700">Urgent</span>
                                    <span>{{ priorityCounts['Urgent'] || 0 }}</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-red-500 h-2 rounded-full" :style="`width: ${Math.min(((priorityCounts['Urgent'] || 0) / 10) * 100, 100)}%`"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm font-medium mb-1">
                                    <span class="text-orange-600">High</span>
                                    <span>{{ priorityCounts['High'] || 0 }}</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-orange-400 h-2 rounded-full" :style="`width: ${Math.min(((priorityCounts['High'] || 0) / 10) * 100, 100)}%`"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm font-medium mb-1">
                                    <span class="text-yellow-600">Medium</span>
                                    <span>{{ priorityCounts['Medium'] || 0 }}</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-yellow-400 h-2 rounded-full" :style="`width: ${Math.min(((priorityCounts['Medium'] || 0) / 10) * 100, 100)}%`"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-sm font-medium mb-1">
                                    <span class="text-green-600">Low</span>
                                    <span>{{ priorityCounts['Low'] || 0 }}</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-green-400 h-2 rounded-full" :style="`width: ${Math.min(((priorityCounts['Low'] || 0) / 10) * 100, 100)}%`"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg border border-slate-200">
                        <div class="p-6 border-b border-slate-200">
                            <h3 class="text-lg font-medium text-slate-900 flex items-center gap-2">
                                <ExclamationTriangleIcon class="w-5 h-5 text-csired" />
                                Action Required (Urgent/High)
                            </h3>
                        </div>
                        <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                            <div v-for="ticket in urgentTickets" :key="ticket.id" class="p-4 hover:bg-slate-50 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <a :href="ticket.id ? route('tickets.show', ticket.id) : '#'" class="text-sm font-bold text-indigo-600 hover:text-indigo-900">
                                            {{ ticket.ticket_no }} - {{ ticket.subject }}
                                        </a>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Requester: {{ ticket.is_anonymous ? 'Anonymous' : ticket.requester?.name }} | Dept: {{ ticket.department?.name }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-100 text-red-800">
                                        {{ ticket.priority }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="urgentTickets.length === 0" class="p-8 text-center text-slate-500 text-sm">
                                You have no urgent or high priority tickets right now. Great job!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
