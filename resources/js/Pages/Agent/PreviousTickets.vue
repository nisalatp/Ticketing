<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    TicketIcon, 
    FunnelIcon,
    MagnifyingGlassIcon,
    ClockIcon,
    CheckCircleIcon,
    ArchiveBoxIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    tickets: Object,
    filters: Object,
    types: Array,
    topics: Array,
});

function debounce(fn, delay) {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

const search = ref(props.filters.search || '');
const type_id = ref(props.filters.type_id || '');
const topic_id = ref(props.filters.topic_id || '');
const date_from = ref(props.filters.date_from || '');
const date_to = ref(props.filters.date_to || '');

const filter = debounce(() => {
    router.get(route('agent.previous-tickets'), {
        search: search.value,
        type_id: type_id.value,
        topic_id: topic_id.value,
        date_from: date_from.value,
        date_to: date_to.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

watch([search, type_id, topic_id, date_from, date_to], () => filter());

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
        case 'Resolved': return 'bg-emerald-50 text-emerald-800 border-emerald-100';
        case 'Closed': return 'bg-slate-50 text-slate-800 border-slate-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};
</script>

<template>
    <Head title="Previous Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                        <ArchiveBoxIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Previous Tickets</h1>
                        <p class="text-xs text-slate-500 font-medium tracking-tight">Viewing your historically completed tickets.</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-10 space-y-6">
            <!-- Search & Filters -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Search -->
                    <div class="lg:col-span-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Search</label>
                        <div class="relative">
                            <MagnifyingGlassIcon class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input v-model="search" type="text" placeholder="Ticket # or subject..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-slate-200">
                        </div>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Type</label>
                        <select v-model="type_id" class="w-full py-2 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-slate-200">
                            <option value="">All Types</option>
                            <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </select>
                    </div>

                    <!-- Topic -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Topic</label>
                        <select v-model="topic_id" class="w-full py-2 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-slate-200">
                            <option value="">All Topics</option>
                            <option v-for="topic in topics" :key="topic.id" :value="topic.id">{{ topic.name }}</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">From Date</label>
                        <input v-model="date_from" type="date" class="w-full py-2 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-slate-200">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">To Date</label>
                        <input v-model="date_to" type="date" class="w-full py-2 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-slate-200">
                    </div>
                </div>
            </div>

            <!-- Tickets List -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Ticket</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Requester</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Type / Topic</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Dates</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-slate-50/50 transition-colors group cursor-pointer" @click="$inertia.visit(route('tickets.show', ticket.id))">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-slate-400 tracking-widest uppercase">{{ ticket.ticket_no }}</span>
                                    <span class="text-sm font-bold text-slate-900 group-hover:text-csired transition-colors">{{ ticket.subject }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500 overflow-hidden">
                                        <img v-if="!ticket.is_anonymous && ticket.requester?.profile_photo_path" :src="'/storage/' + ticket.requester.profile_photo_path" class="w-full h-full object-cover">
                                        <span v-else>{{ ticket.is_anonymous ? 'A' : (ticket.requester?.name?.charAt(0) || '?') }}</span>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600">{{ ticket.is_anonymous ? 'Anonymous' : ticket.requester?.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span v-if="ticket.type" :class="['w-max px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border', ticket.type.color_code]">
                                        {{ ticket.type.name }}
                                    </span>
                                    <span class="text-xs font-medium text-slate-500">{{ ticket.topic?.name || 'General' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-slate-400">Created: {{ new Date(ticket.created_at).toLocaleDateString() }}</span>
                                    <span class="text-[10px] font-bold text-emerald-600">Resolved: {{ ticket.resolved_at ? new Date(ticket.resolved_at).toLocaleDateString() : 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span :class="['px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border', getStatusColor(ticket.status)]">
                                    {{ ticket.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="tickets.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center">
                                <ArchiveBoxIcon class="w-8 h-8 text-slate-200 mx-auto mb-2" />
                                <p class="text-sm font-bold text-slate-400">No historical tickets found matching your criteria.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="tickets.links.length > 3" class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-center gap-1">
                    <Link v-for="link in tickets.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold transition-all shadow-sm" :class="{'text-csired border-csired/20 bg-red-50/50': link.active, 'text-slate-400 hover:text-slate-600': !link.active, 'opacity-50 cursor-not-allowed': !link.url}" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
