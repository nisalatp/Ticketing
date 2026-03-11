<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { 
    TrashIcon, 
    FunnelIcon, 
    XMarkIcon, 
    MagnifyingGlassIcon,
    UserGroupIcon,
    QueueListIcon,
    TagIcon,
    FireIcon,
    ClockIcon,
    Squares2X2Icon
} from '@heroicons/vue/20/solid';
import { QueueListIcon as QueueListOutlineIcon } from '@heroicons/vue/24/outline';

// Local debounce implementation to avoid resolution issues
const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn(...args);
        }, delay);
    };
};

const props = defineProps({
    tickets: Object,
    stats: Object,
    filters: Object,
    agents: Array,
    departments: Array,
    types: Array,
    topics: Array,
    transferReasons: Array,
});

const search = ref(props.filters.search || '');
const agent_id = ref(props.filters.agent_id || '');
const department_id = ref(props.filters.department_id || '');
const type_id = ref(props.filters.type_id || '');
const topic_id = ref(props.filters.topic_id || '');
const priority = ref(props.filters.priority || '');
const status = ref(props.filters.status || 'all');

const ticketToDelete = ref(null);
const confirmDeletionForm = useForm({});

const assigningTicket = ref(null);
const assignmentForm = useForm({
    user_id: '',
    transfer_reason_id: '',
    note: '',
});

const applyFilters = debounce(() => {
    router.get(route('admin.tickets.index'), {
        search: search.value,
        agent_id: agent_id.value,
        department_id: department_id.value,
        type_id: type_id.value,
        topic_id: topic_id.value,
        priority: priority.value,
        status: status.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search, agent_id, department_id, type_id, topic_id, priority, status], () => {
    applyFilters();
});

const clearFilters = () => {
    search.value = '';
    agent_id.value = '';
    department_id.value = '';
    type_id.value = '';
    topic_id.value = '';
    priority.value = '';
    status.value = 'all';
};

const deleteTicket = (ticket) => {
    ticketToDelete.value = ticket;
};

const confirmDelete = () => {
    confirmDeletionForm.delete(route('admin.tickets.destroy', ticketToDelete.value.id), {
        onSuccess: () => {
            ticketToDelete.value = null;
        },
    });
};

const openAssignModal = (ticket) => {
    assigningTicket.value = ticket;
    assignmentForm.reset();
    assignmentForm.clearErrors();
};

const closeAssignModal = () => {
    assigningTicket.value = null;
    assignmentForm.reset();
};

const submitAssignment = () => {
    assignmentForm.put(route('admin.tickets.assign', assigningTicket.value.id), {
        onSuccess: () => {
            closeAssignModal();
        },
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'New': return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'Assigned': return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'In Progress': return 'bg-indigo-50 text-indigo-600 border-indigo-100';
        case 'Waiting': return 'bg-rose-50 text-rose-600 border-rose-100';
        case 'Resolved': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'Closed': return 'bg-slate-50 text-slate-400 border-slate-100';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};

const getPriorityColor = (priority) => {
    switch (priority) {
        case 'Urgent': return 'bg-red-50 text-red-600 border-red-100 shadow-sm shadow-red-100';
        case 'High': return 'bg-orange-50 text-orange-600 border-orange-100';
        case 'Medium': return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'Low': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        default: return 'bg-slate-50 text-slate-500 border-slate-100';
    }
};

// Summary Stats for Cards
const totalTickets = computed(() => props.tickets.total);
const urgentCount = computed(() => props.stats.by_urgency['Urgent'] || 0);
const pendingAssigment = computed(() => props.stats.by_status['New'] || 0);
const resolvedCount = computed(() => props.stats.by_status['Resolved'] || 0);

</script>

<template>
    <Head title="Ticket Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                        <QueueListOutlineIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Ticket Management Hub</h2>
                        <p class="text-xs text-slate-500 font-medium">Full visibility and control over all system tickets.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                     <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest">
                        System-wide: {{ totalTickets }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex items-center gap-3 transition-transform hover:scale-[1.01]">
                        <div class="w-10 h-10 bg-csired/10 rounded-lg flex items-center justify-center shrink-0">
                            <QueueListIcon class="w-5 h-5 text-csired" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Total Active</p>
                            <p class="text-xl font-black text-slate-900 leading-none">{{ totalTickets }}</p>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex items-center gap-3 transition-transform hover:scale-[1.01]">
                        <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center shrink-0 border border-amber-100">
                            <UserGroupIcon class="w-5 h-5 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Pending Assignment</p>
                            <p class="text-xl font-black text-slate-900 leading-none">{{ pendingAssigment }}</p>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex items-center gap-3 transition-transform hover:scale-[1.01]">
                        <div class="w-10 h-10 bg-rose-50 rounded-lg flex items-center justify-center shrink-0 border border-rose-100">
                            <FireIcon class="w-5 h-5 text-rose-600" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Critical/Urgent</p>
                            <p class="text-xl font-black text-slate-900 leading-none">{{ urgentCount }}</p>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex items-center gap-3 transition-transform hover:scale-[1.01]">
                        <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center shrink-0 border border-emerald-100">
                            <ClockIcon class="w-5 h-5 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Resolved Today</p>
                            <p class="text-xl font-black text-slate-900 leading-none">{{ resolvedCount }}</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white shadow-sm sm:rounded-xl mb-6 border border-slate-100 overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2 text-slate-900">
                                <FunnelIcon class="w-4 h-4 text-csired" />
                                <h3 class="font-black text-[9px] uppercase tracking-widest">Filters</h3>
                            </div>
                            <button @click="clearFilters" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-csired transition-colors flex items-center gap-1">
                                <XMarkIcon class="w-3 h-3" />
                                Reset Filters
                            </button>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                            <div class="relative col-span-2 md:col-span-1">
                                <MagnifyingGlassIcon class="w-3 h-3 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" />
                                <input v-model="search" type="text" placeholder="SEARCH..." class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border-none rounded-lg text-[10px] font-black focus:ring-2 focus:ring-csired/20" />
                            </div>

                            <select v-model="agent_id" class="bg-slate-50 border-none rounded-lg text-[10px] font-black focus:ring-2 focus:ring-csired/20 py-1.5">
                                <option value="">All Agents</option>
                                <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                            </select>

                            <select v-model="status" class="bg-slate-50 border-none rounded-lg text-[10px] font-black focus:ring-2 focus:ring-csired/20 py-1.5">
                                <option value="all">STATUS</option>
                                <option value="New">NEW</option>
                                <option value="Assigned">ASSIGNED</option>
                                <option value="In Progress">PROGRESS</option>
                                <option value="Waiting">WAITING</option>
                                <option value="Resolved">RESOLVED</option>
                                <option value="Closed">CLOSED</option>
                            </select>

                            <select v-model="priority" class="bg-slate-50 border-none rounded-lg text-[10px] font-black focus:ring-2 focus:ring-csired/20 py-1.5">
                                <option value="">PRIORITY</option>
                                <option value="Low">LOW</option>
                                <option value="Medium">MEDIUM</option>
                                <option value="High">HIGH</option>
                                <option value="Urgent">URGENT</option>
                            </select>

                            <select v-model="department_id" class="bg-slate-50 border-none rounded-lg text-[10px] font-black focus:ring-2 focus:ring-csired/20 py-1.5">
                                <option value="">DEPARTMENT</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name.toUpperCase() }}</option>
                            </select>

                            <select v-model="type_id" class="bg-slate-50 border-none rounded-lg text-[10px] font-black focus:ring-2 focus:ring-csired/20 py-1.5">
                                <option value="">TYPE</option>
                                <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name.toUpperCase() }}</option>
                            </select>

                            <select v-model="topic_id" class="bg-slate-50 border-none rounded-lg text-[10px] font-black focus:ring-2 focus:ring-csired/20 py-1.5">
                                <option value="">TOPIC</option>
                                <option v-for="topic in topics" :key="topic.id" :value="topic.id">{{ topic.name.toUpperCase() }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white shadow-sm sm:rounded-xl border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-4 py-3 text-[9px] font-black uppercase tracking-widest text-slate-400">ID / Urgency</th>
                                    <th class="px-4 py-3 text-[9px] font-black uppercase tracking-widest text-slate-400">Details</th>
                                    <th class="px-4 py-3 text-[9px] font-black uppercase tracking-widest text-slate-400">Org/Topic</th>
                                    <th class="px-4 py-3 text-[9px] font-black uppercase tracking-widest text-slate-400 text-center">Status</th>
                                    <th class="px-4 py-3 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-[11px] font-black text-slate-900 tracking-tight">{{ ticket.ticket_no }}</span>
                                            <span :class="['mt-0.5 text-[8px] font-black uppercase tracking-tighter px-1 rounded w-fit border', getPriorityColor(ticket.priority)]">
                                                {{ ticket.priority }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div @click="$inertia.visit(route('tickets.show', ticket.id))" class="cursor-pointer max-w-[200px]">
                                            <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight truncate group-hover:text-csired transition-colors">{{ ticket.subject }}</p>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide truncate">
                                                    {{ ticket.is_anonymous ? 'Anonymous' : ticket.requester?.name }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">{{ ticket.department?.name }}</span>
                                            <span class="text-[8px] font-bold text-slate-400 uppercase truncate max-w-[120px]">{{ ticket.topic?.name || 'General' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span :class="['px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-widest border shadow-sm', getStatusColor(ticket.status)]">
                                            {{ ticket.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openAssignModal(ticket)" class="px-2 py-1 bg-slate-100 hover:bg-csired hover:text-white text-[9px] font-black uppercase tracking-widest rounded-md transition-all">
                                                Assign
                                            </button>
                                            <button @click="deleteTicket(ticket)" class="p-1 text-slate-300 hover:text-csired hover:bg-red-50 rounded-md transition-all">
                                                <TrashIcon class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="tickets.links.length > 3" class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Showing {{ tickets.from }}-{{ tickets.to }} of {{ tickets.total }}
                        </div>
                        <div class="flex items-center gap-1">
                            <Link v-for="(link, i) in tickets.links" :key="i" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 rounded-lg text-[10px] font-black transition-all border" :class="[ link.active ? 'bg-csired text-white border-csired shadow-lg' : 'bg-white text-slate-500 border-slate-200 hover:border-csired hover:text-csired', !link.url ? 'opacity-30 cursor-not-allowed' : '' ]" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Modal -->
        <Modal :show="assigningTicket !== null" @close="closeAssignModal" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shrink-0">
                        <UserGroupIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Manual Assignment</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ assigningTicket?.ticket_no }}</p>
                    </div>
                </div>
                
                <form @submit.prevent="submitAssignment" class="space-y-6">
                    <div>
                        <InputLabel for="user_id" value="Select Target Agent" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2" />
                        <SelectInput id="user_id" v-model="assignmentForm.user_id" class="mt-1 block w-full bg-slate-50 border-none rounded-xl text-xs font-bold focus:ring-csired/20" required>
                            <option value="" disabled>Choose an agent...</option>
                            <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                        </SelectInput>
                        <InputError :message="assignmentForm.errors.user_id" class="mt-2" />
                    </div>

                    <div v-if="assigningTicket && assigningTicket.assignee && assignmentForm.user_id && assigningTicket.assignee.id !== assignmentForm.user_id">
                        <InputLabel for="transfer_reason_id" value="Reason for Handoff" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2" />
                        <SelectInput id="transfer_reason_id" v-model="assignmentForm.transfer_reason_id" class="mt-1 block w-full bg-slate-50 border-none rounded-xl text-xs font-bold focus:ring-csired/20" required>
                            <option value="" disabled>Select reason...</option>
                            <option v-for="reason in transferReasons" :key="reason.id" :value="reason.id">{{ reason.name }}</option>
                        </SelectInput>
                        <InputError :message="assignmentForm.errors.transfer_reason_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="note" value="Administrative Note (Internal)" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2" />
                        <TextInput id="note" v-model="assignmentForm.note" type="text" class="mt-1 block w-full bg-slate-50 border-none rounded-xl text-xs font-bold focus:ring-csired/20" placeholder="Reason for override..." />
                        <InputError :message="assignmentForm.errors.note" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-50">
                        <SecondaryButton @click="closeAssignModal" class="uppercase text-[10px] tracking-widest font-black">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': assignmentForm.processing }" :disabled="assignmentForm.processing" class="bg-csired hover:bg-csires uppercase text-[10px] tracking-widest font-black">
                            Confirm Assignment
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="ticketToDelete !== null" @close="ticketToDelete = null" maxWidth="sm">
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <TrashIcon class="w-8 h-8 text-csired" />
                </div>
                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-2">Permanent Deletion?</h3>
                <p class="text-xs text-slate-500 mb-8 font-bold uppercase tracking-widest leading-relaxed">
                    This will permanently destroy ticket <span class="text-slate-900">{{ ticketToDelete?.ticket_no }}</span> and all historical data. This cannot be undone.
                </p>
                <div class="flex flex-col gap-3">
                    <DangerButton @click="confirmDelete" :class="{ 'opacity-25': confirmDeletionForm.processing }" :disabled="confirmDeletionForm.processing" class="w-full justify-center py-3 uppercase text-[10px] font-black tracking-widest">
                        Destroy Irreversibly
                    </DangerButton>
                    <SecondaryButton @click="ticketToDelete = null" class="w-full justify-center py-3 uppercase text-[10px] font-black tracking-widest border-none hover:bg-slate-50">
                        Keep Ticket
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
