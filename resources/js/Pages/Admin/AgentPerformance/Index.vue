<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import SelectInput from '@/Components/SelectInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { 
    ChartBarIcon, 
    ArrowTrendingUpIcon, 
    InboxStackIcon, 
    ArrowRightOnRectangleIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    agents: Array,
    globalMetrics: Object,
    departments: Array,
    topics: Array,
    filters: Object,
});

const departmentFilter = ref(props.filters.department_id || 'all');
const topicFilter = ref(props.filters.topic_id || 'all');

const availableTopics = computed(() => {
    if (departmentFilter.value === 'all') {
        return props.topics;
    }
    return props.topics.filter(t => t.department_id == departmentFilter.value);
});

watch([departmentFilter, topicFilter], () => {
    router.get(route('admin.agent-performance.index'), {
        department_id: departmentFilter.value,
        topic_id: topicFilter.value,
    }, { preserveState: true, replace: true });
});

</script>

<template>
    <Head title="Agent Performance Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                    <ChartBarIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Agent Performance Matrix</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-tight">Monitor SLA efficiency, backlogs, and transfer patterns across the team.</p>
                </div>
            </div>
        </template>

        <div class="py-6 space-y-6">
            <!-- Global KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl border border-slate-100 p-6 flex items-center shadow-sm">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mr-4">
                        <InboxStackIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Global Open</p>
                        <p class="text-2xl font-black text-slate-900">{{ globalMetrics.total_open }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 p-6 flex items-center shadow-sm">
                    <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Global Resolved</p>
                        <p class="text-2xl font-black text-slate-900">{{ globalMetrics.total_resolved }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 p-6 flex items-center shadow-sm">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mr-4">
                        <ArrowTrendingUpIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Avg Efficiency</p>
                        <p class="text-2xl font-black text-slate-900" :class="{'text-red-600': globalMetrics.average_efficiency < 80}">{{ globalMetrics.average_efficiency }}%</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-100 p-6 flex items-center shadow-sm">
                    <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center mr-4">
                        <ArrowRightOnRectangleIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Total Transfers</p>
                        <p class="text-2xl font-black text-slate-900">{{ globalMetrics.total_transfers_out }}</p>
                    </div>
                </div>
            </div>

            <div class="w-full px-4 sm:px-6 lg:px-8 space-y-4">
                
                <!-- Filters -->
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-end gap-4">
                    <div class="w-64">
                        <InputLabel value="Filter by Department" />
                        <SelectInput v-model="departmentFilter" class="mt-1 w-full">
                            <option value="all">All Departments</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </SelectInput>
                    </div>
                    <div class="w-64">
                        <InputLabel value="Filter by Topic" />
                        <SelectInput v-model="topicFilter" class="mt-1 w-full">
                            <option value="all">All Topics</option>
                            <option v-for="topic in availableTopics" :key="topic.id" :value="topic.id">{{ topic.name }}</option>
                        </SelectInput>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white shadow-xl sm:rounded-[1rem] border border-slate-100 overflow-hidden">
                    <div class="min-w-full overflow-x-auto p-1">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th scope="col" class="py-4 pl-6 pr-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Agent</th>
                                    <th scope="col" class="px-3 py-4 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Open / Backlog</th>
                                    <th scope="col" class="px-3 py-4 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Completed Urgency</th>
                                    <th scope="col" class="px-3 py-4 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">SLA Efficiency</th>
                                    <th scope="col" class="px-3 py-4 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Burndown (7d)</th>
                                    <th scope="col" class="pr-6 py-4 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Transferred Out</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-for="agent in agents" :key="agent.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-700 font-bold border border-indigo-100">
                                                {{ agent.name.charAt(0) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-slate-900">{{ agent.name }}</div>
                                                <div class="text-[10px] text-slate-500 font-semibold">{{ agent.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="whitespace-nowrap px-3 py-4 text-center">
                                        <div class="text-lg font-black text-slate-800">{{ agent.metrics.open_tickets }}</div>
                                        <div class="text-[10px] font-semibold text-slate-500">Weight: {{ agent.metrics.backlog_weight }}</div>
                                    </td>

                                    <td class="whitespace-nowrap px-3 py-4 flex gap-1 justify-center items-center h-full mt-2">
                                        <div title="Urgent" class="w-8 flex flex-col items-center">
                                            <div class="h-6 w-6 rounded-md bg-red-100 text-red-700 flex items-center justify-center text-xs font-bold">{{ agent.metrics.resolved_by_urgency.Urgent }}</div>
                                            <span class="text-[8px] mt-1 text-slate-400 uppercase font-bold tracking-tighter">Urg</span>
                                        </div>
                                        <div title="High" class="w-8 flex flex-col items-center">
                                            <div class="h-6 w-6 rounded-md bg-orange-100 text-orange-700 flex items-center justify-center text-xs font-bold">{{ agent.metrics.resolved_by_urgency.High }}</div>
                                            <span class="text-[8px] mt-1 text-slate-400 uppercase font-bold tracking-tighter">Hgh</span>
                                        </div>
                                        <div title="Medium" class="w-8 flex flex-col items-center">
                                            <div class="h-6 w-6 rounded-md bg-yellow-100 text-yellow-700 flex items-center justify-center text-xs font-bold">{{ agent.metrics.resolved_by_urgency.Medium }}</div>
                                            <span class="text-[8px] mt-1 text-slate-400 uppercase font-bold tracking-tighter">Med</span>
                                        </div>
                                        <div title="Low" class="w-8 flex flex-col items-center">
                                            <div class="h-6 w-6 rounded-md bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold">{{ agent.metrics.resolved_by_urgency.Low }}</div>
                                            <span class="text-[8px] mt-1 text-slate-400 uppercase font-bold tracking-tighter">Low</span>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-3 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black border"
                                            :class="{
                                                'bg-green-50 text-green-700 border-green-200': agent.metrics.efficiency_percentage >= 90,
                                                'bg-yellow-50 text-yellow-700 border-yellow-200': agent.metrics.efficiency_percentage >= 70 && agent.metrics.efficiency_percentage < 90,
                                                'bg-red-50 text-red-700 border-red-200': agent.metrics.efficiency_percentage < 70
                                            }">
                                            {{ agent.metrics.efficiency_percentage }}%
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-3 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="flex items-end justify-center gap-0.5 h-6 w-full px-2 cursor-pointer" :title="'7-day trend. Total closed: ' + agent.metrics.resolved_last_week">
                                                <div v-for="(val, i) in agent.metrics.burndown_data" :key="i"
                                                    class="w-1.5 bg-indigo-200 rounded-t-sm transition-colors duration-200 hover:bg-indigo-500"
                                                    :style="{ height: Math.max(15, (val / Math.max(...agent.metrics.burndown_data, 1)) * 100) + '%' }"
                                                    :title="val + ' tickets'">
                                                </div>
                                            </div>
                                            <div class="text-[8px] text-slate-400 uppercase font-bold mt-1 tracking-tighter">{{ agent.metrics.resolved_last_week }} Closed (7D)</div>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap pr-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center px-3 py-1 bg-slate-50 text-slate-600 rounded-lg text-sm font-black ring-1 ring-inset ring-slate-200">
                                            {{ agent.metrics.transfers_out }}
                                        </span>
                                    </td>
                                </tr>
                                
                                <tr v-if="agents.length === 0">
                                    <td colspan="6" class="py-12 text-center text-sm font-medium text-slate-500">
                                        No agents found for the selected criteria.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
