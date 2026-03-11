<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted, inject } from 'vue';

let refreshInterval = null;
onMounted(() => { refreshInterval = setInterval(() => router.reload({ preserveScroll: true, preserveState: true }), 10000); });
onUnmounted(() => { clearInterval(refreshInterval); });
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import draggable from 'vuedraggable';
import { 
    TicketIcon, 
    UserIcon, 
    CalendarIcon,
    ChatBubbleLeftEllipsisIcon,
    PaperClipIcon,
    HashtagIcon,
    ClockIcon,
    XMarkIcon,
    ArrowUpIcon,
    PaperAirplaneIcon,
    PhotoIcon,
    DocumentIcon,
    VideoCameraIcon,
    ExclamationTriangleIcon,
    DocumentTextIcon,
    InformationCircleIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon
} from '@heroicons/vue/24/outline';
import TicketTimer from '@/Components/TicketTimer.vue';

const props = defineProps({
    tickets: Array,
    incoming: Array,
    transfers: Array,
    users: Array,
    transferReasons: Array,
});

const collapseSidebar = inject('collapseSidebar', null);

const columns = ref([
    { id: 'Incoming', name: 'Incoming', color: 'bg-indigo-50 text-indigo-700' },
    { id: 'Assigned', name: 'Assigned', color: 'bg-blue-50 text-blue-700' },
    { id: 'In Progress', name: 'In Progress', color: 'bg-sky-50 text-sky-700' },
    { id: 'Waiting', name: 'Waiting', color: 'bg-amber-50 text-amber-700' },
    { id: 'Resolved', name: 'Resolved', color: 'bg-emerald-50 text-emerald-700' },
    { id: 'Transfers', name: 'Transfers', color: 'bg-rose-50 text-rose-700' },
]);

const collapsedIncoming = ref(true);
const collapsedTransfers = ref(true);

const selectedTicket = ref(null);
const isDrawerOpen = ref(false);

const messageForm = useForm({
    message: '',
    attachments: [],
});

// Use reactive refs for each column to allow vuedraggable mutations
const kanbanData = ref({
    'Incoming': [],
    'Assigned': [],
    'In Progress': [],
    'Waiting': [],
    'Resolved': [],
    'Transfers': [],
});

const syncKanbanData = () => {
    kanbanData.value = {
        'Incoming': [...(props.incoming || [])],
        'Assigned': [],
        'In Progress': [],
        'Waiting': [],
        'Resolved': [],
        'Transfers': [...(props.transfers || [])],
    };
    props.tickets.forEach(ticket => {
        if (kanbanData.value[ticket.status]) {
            kanbanData.value[ticket.status].push(ticket);
        }
    });
};

onMounted(() => {
    syncKanbanData();
    if (collapseSidebar) {
        collapseSidebar(true);
    }
});

// Update local data when props change (e.g. after a router reload)
watch(() => [props.tickets, props.incoming, props.transfers], () => {
    syncKanbanData();
}, { deep: true });

watch(() => props.tickets, (newTickets) => {
    if (selectedTicket.value) {
        const updated = newTickets.find(t => t.id === selectedTicket.value.id);
        if (updated) {
            selectedTicket.value = updated;
        } else {
            closeDrawer();
        }
    }
}, { deep: true });

const openTicket = (ticket) => {
    selectedTicket.value = ticket;
    isDrawerOpen.value = true;
};

const closeDrawer = () => {
    isDrawerOpen.value = false;
    selectedTicket.value = null;
    messageForm.reset();
};

const isTransferModalOpen = ref(false);
const pendingTransferTicket = ref(null);
const transferForm = useForm({
    to_user_id: '',
    transfer_reason_id: '',
    notes: '',
});

const openTransferModal = (ticket) => {
    pendingTransferTicket.value = ticket;
    transferForm.reset();
    isTransferModalOpen.value = true;
};

const closeTransferModal = () => {
    isTransferModalOpen.value = false;
    pendingTransferTicket.value = null;
    transferForm.reset();
    router.reload({ only: ['tickets', 'incoming', 'transfers'] });
};

const submitTransfer = () => {
    transferForm.post(route('agent.tickets.transfers.initiate', pendingTransferTicket.value.id), {
        onSuccess: () => closeTransferModal(),
    });
};

const onDragEnd = (status, event) => {
    if (event.added) {
        const ticket = event.added.element;

        // Prevent dragging TO Incoming
        if (status === 'Incoming') {
            router.reload({ only: ['tickets', 'incoming', 'transfers'] });
            return;
        }

        const options = {
            preserveScroll: true,
            onError: () => {
                router.reload({ only: ['tickets', 'incoming', 'transfers'] });
            }
        };

        if (status === 'Transfers') {
            openTransferModal(ticket);
            return;
        }

        // Did it come from Incoming?
        if (props.incoming?.some(t => Number(t.id) === Number(ticket.id))) {
            router.put(route('agent.tickets.transfers.accept', ticket.id), {
                status: status
            }, options);
            return;
        }

        // Did it come from Transfers? (Transfer cancellation)
        // Check props.transfers directly (source of truth for transfers out)
        if (props.transfers?.some(t => Number(t.id) === Number(ticket.id))) {
            router.put(route('agent.tickets.transfers.cancel', ticket.id), {
                status: status
            }, { 
                ...options,
                onSuccess: () => {
                    router.reload({ only: ['tickets', 'incoming', 'transfers'] });
                }
            });
            return;
        }

        // Normal status update
        router.put(route('agent.tickets.update-status', ticket.id), {
            status: status
        }, options);
    }
};

const submitMessage = () => {
    messageForm.post(route('agent.tickets.store-message', selectedTicket.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            messageForm.reset();
        },
    });
};

const handleFileChange = (e) => {
    messageForm.attachments = Array.from(e.target.files);
};

const confirmingEscalation = ref(false);

const escalateTicket = () => {
    confirmingEscalation.value = true;
};

const executeEscalation = () => {
    router.put(route('agent.tickets.escalate', selectedTicket.value.id), {}, {
        onSuccess: () => {
            confirmingEscalation.value = false;
            closeDrawer();
        },
    });
};

const confirmingRejection = ref(false);

const rejectTransfer = () => {
    confirmingRejection.value = true;
};

const executeRejection = () => {
    router.put(route('agent.tickets.transfers.reject', selectedTicket.value.id), {}, {
        onSuccess: () => {
            confirmingRejection.value = false;
            closeDrawer();
        },
    });
};

const getPriorityColor = (priority) => {
    switch (priority) {
        case 'Urgent': return 'bg-rose-50 text-rose-700 border-rose-100';
        case 'High': return 'bg-orange-50 text-orange-700 border-orange-100';
        case 'Medium': return 'bg-blue-50 text-blue-700 border-blue-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};

const getFileIcon = (mime) => {
    if (mime.startsWith('image/')) return PhotoIcon;
    if (mime.startsWith('video/')) return VideoCameraIcon;
    return DocumentIcon;
};

const getTicketTypeIcon = (iconName) => {
    switch (iconName) {
        case 'ExclamationTriangleIcon': return ExclamationTriangleIcon;
        case 'DocumentTextIcon': return DocumentTextIcon;
        case 'InformationCircleIcon': return InformationCircleIcon;
        default: return TicketIcon;
    }
};

</script>

<template>
    <Head title="Agent Queue" />

    <AuthenticatedLayout :initialSidebarCollapsed="true">
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                    <TicketIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Agent Workspace</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-tight whitespace-nowrap overflow-hidden">Manage your assigned tickets and track progress.</p>
                </div>
            </div>
        </template>

        <div class="h-[calc(100vh-140px)] p-4 overflow-x-auto relative no-scrollbar">
            <div class="flex gap-4 h-full min-w-max">
                <div v-for="column in columns" :key="column.id" 
                    :class="[
                        'flex flex-col bg-slate-50/50 rounded-2xl border border-slate-100 shadow-sm transition-all duration-300 relative h-full',
                        column.id === 'Incoming' && collapsedIncoming ? 'w-12 shrink-0 overflow-hidden' : 
                        column.id === 'Transfers' && collapsedTransfers ? 'w-12 shrink-0 overflow-hidden' : 'flex-1 min-w-[330px] max-w-[450px]'
                    ]"
                >
                    <!-- Collapsed Strip (Drop Zone) -->
                    <draggable
                        v-if="(column.id === 'Incoming' && collapsedIncoming) || (column.id === 'Transfers' && collapsedTransfers)"
                        :list="kanbanData[column.id]"
                        group="tickets"
                        item-key="id"
                        @change="(e) => onDragEnd(column.id, e)"
                        @click="column.id === 'Incoming' ? collapsedIncoming = false : collapsedTransfers = false"
                        class="flex-1 flex flex-col items-center justify-center py-8 cursor-pointer hover:bg-slate-100/50 transition-colors relative"
                    >
                        <template #item="{ element }">
                            <div v-show="false"></div>
                        </template>
                        <template #header>
                            <div class="contents">
                                <div class="vertical-text font-black text-slate-400 uppercase tracking-widest text-sm whitespace-nowrap absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                                    {{ column.name }} ({{ kanbanData[column.id].length }})
                                </div>
                                <ChevronRightIcon v-if="column.id === 'Incoming'" class="w-5 h-5 text-slate-400 absolute bottom-4" />
                                <ChevronLeftIcon v-if="column.id === 'Transfers'" class="w-5 h-5 text-slate-400 absolute bottom-4" />
                            </div>
                        </template>
                    </draggable>

                    <!-- Full Column -->
                    <template v-else>
                        <div class="p-4 flex items-center justify-between border-b border-slate-100 bg-white rounded-t-2xl">
                            <div class="flex items-center gap-2">
                                <span :class="['px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider', column.color]">
                                    {{ column.name }}
                                </span>
                                <span class="text-xs font-bold text-slate-400">({{ kanbanData[column.id].length }})</span>
                            </div>
                            <!-- Collapse Toggle -->
                            <button v-if="column.id === 'Incoming'" @click="collapsedIncoming = true" class="p-1 hover:bg-slate-100 rounded-md text-slate-400 transition-colors">
                                <ChevronDoubleLeftIcon class="w-4 h-4" />
                            </button>
                            <button v-if="column.id === 'Transfers'" @click="collapsedTransfers = true" class="p-1 hover:bg-slate-100 rounded-md text-slate-400 transition-colors">
                                <ChevronDoubleRightIcon class="w-4 h-4" />
                            </button>
                        </div>

                        <draggable
                            :list="kanbanData[column.id]"
                            group="tickets"
                            item-key="id"
                            class="flex-1 p-3 space-y-3 overflow-y-auto"
                            @change="(e) => onDragEnd(column.id, e)"
                        >
                            <template #item="{ element }">
                                <div 
                                    @click="openTicket(element)"
                                    class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm hover:shadow-md hover:border-csired/20 transition-all cursor-grab active:cursor-grabbing group"
                                >
                                    <!-- Card Header -->
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ element.ticket_no }}</span>
                                        
                                        <div class="flex items-center gap-1.5">
                                            <!-- Critical Incident Special Badge -->
                                            <span v-if="element.type && element.type.name.toUpperCase() === 'CRITICAL INCIDENT'" class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-red-600 text-white uppercase tracking-widest shadow-sm">
                                                <ExclamationTriangleIcon class="w-3.5 h-3.5" />
                                                CRITICAL
                                            </span>
                                            
                                            <!-- Standard Type Badge -->
                                            <span v-else-if="element.type" :class="['flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black border uppercase tracking-widest shadow-sm', element.type.color_code]">
                                                <component :is="getTicketTypeIcon(element.type.icon)" class="w-3.5 h-3.5" />
                                                {{ element.type.name }}
                                            </span>

                                            <!-- Priority Badge with Severity Multiplier -->
                                            <span :class="['text-[10px] font-black px-2.5 py-1 rounded-lg uppercase tracking-widest shadow-sm border', getPriorityColor(element.priority)]">
                                                {{ element.priority }}
                                                <span v-if="element.type && element.type.severity_factor !== '1.00'" class="ml-1 opacity-70">(X{{ parseFloat(element.type.severity_factor).toString() }})</span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Subject -->
                                    <h4 class="text-base font-bold text-slate-900 mb-4 line-clamp-2 leading-snug group-hover:text-csired transition-colors">
                                        {{ element.subject }}
                                    </h4>

                                    <!-- Requester -->
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 ring-2 ring-white shadow-sm flex items-center justify-center overflow-hidden">
                                            <img v-if="!element.is_anonymous && element.requester.profile_photo_path" :src="'/storage/' + element.requester.profile_photo_path" class="w-full h-full object-cover" @error="$event.target.style.display = 'none'; $event.target.nextElementSibling.style.display = 'flex'">
                                            <UserIcon class="w-4 h-4 text-slate-400" :class="{'hidden': !element.is_anonymous && element.requester.profile_photo_path}" />
                                        </div>
                                        <span class="text-sm font-bold text-slate-600 truncate">{{ element.is_anonymous ? 'Anonymous' : element.requester.name }}</span>
                                    </div>

                                    <!-- Card Footer -->
                                    <div class="pt-3 border-t border-slate-50 flex items-center justify-between">
                                        <div class="flex flex-col gap-0.5">
                                            <div class="flex items-center gap-1.5 text-slate-400">
                                                <HashtagIcon class="w-3.5 h-3.5" />
                                                <span class="text-[11px] font-bold uppercase tracking-tight">{{ element.topic?.name || 'General' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-1">
                                            <div class="flex items-center gap-1.5 text-slate-400">
                                                <ClockIcon class="w-3.5 h-3.5" />
                                                <span class="text-[11px] font-bold uppercase tracking-tight">{{ new Date(element.created_at).toLocaleDateString('en-GB') }}</span>
                                            </div>
                                            <TicketTimer 
                                                :assigned-at="element.assigned_at" 
                                                :breach-at="element.sla_breach_at"
                                                :status="element.status"
                                                class="text-[9px]"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </template>
                </div>
            </div>

            <!-- Detail Drawer -->
            <Transition
                enter-active-class="transform transition ease-in-out duration-500"
                enter-from-class="translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transform transition ease-in-out duration-500"
                leave-from-class="translate-x-0"
                leave-to-class="translate-x-full"
            >
                <div v-if="isDrawerOpen" class="fixed inset-y-0 right-0 w-full max-w-xl bg-white shadow-2xl z-[60] flex flex-col border-l border-slate-100">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-white relative overflow-hidden">
                        <div v-if="selectedTicket.type" class="absolute inset-0 opacity-10 pointer-events-none" :class="selectedTicket.type.color_code.split(' ').find(c => c.startsWith('bg-')) || 'bg-slate-50'"></div>
                        
                        <div class="relative z-10 w-full">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ selectedTicket.ticket_no }}</span>
                                    <span v-if="selectedTicket.type" :class="['flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold border uppercase tracking-wider', selectedTicket.type.color_code]">
                                        <component :is="getTicketTypeIcon(selectedTicket.type.icon)" class="w-4 h-4" />
                                        {{ selectedTicket.type.name }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span :class="['text-[11px] font-bold px-3 py-1 rounded-full uppercase cursor-help', getPriorityColor(selectedTicket.priority)]" :title="'Severity Multiplier: ' + (selectedTicket.type?.severity_factor || '1.00')">
                                        {{ selectedTicket.priority }} <span v-if="selectedTicket.type && selectedTicket.type.severity_factor !== '1.00'" class="opacity-50 ml-1 tracking-normal">(X{{ parseFloat(selectedTicket.type.severity_factor).toString() }})</span>
                                    </span>
                                    <button @click="closeDrawer" class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 transition-all bg-white shadow-sm border border-slate-100">
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight pr-12">{{ selectedTicket.subject }}</h2>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 space-y-8">
                        <div class="bg-slate-50 rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-white border border-slate-200 overflow-hidden flex items-center justify-center shadow-sm">
                                    <img v-if="!selectedTicket.is_anonymous && selectedTicket.requester.profile_photo_path" :src="'/storage/' + selectedTicket.requester.profile_photo_path" class="w-full h-full object-cover" @error="$event.target.style.display = 'none'; $event.target.nextElementSibling.style.display = 'flex'">
                                    <UserIcon class="w-5 h-5 text-slate-400" :class="{'hidden': !selectedTicket.is_anonymous && selectedTicket.requester.profile_photo_path}" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ selectedTicket.is_anonymous ? 'Anonymous' : selectedTicket.requester.name }}</p>
                                    <p class="text-xs text-slate-500 font-medium">{{ selectedTicket.is_anonymous ? 'Anonymized Client' : (selectedTicket.requester.job_title || 'Client Employee') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button v-if="selectedTicket.status === 'Incoming'" @click="rejectTransfer" class="inline-flex items-center px-3 py-1.5 text-[10px] font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-lg transition-all uppercase tracking-widest">
                                    <XMarkIcon class="w-3 h-3 mr-1.5" />
                                    Reject Transfer
                                </button>
                                <button @click="escalateTicket" class="inline-flex items-center px-3 py-1.5 text-[10px] font-bold text-csired bg-red-50 hover:bg-red-100 border border-red-100 rounded-lg transition-all uppercase tracking-widest">
                                    <ArrowUpIcon class="w-3 h-3 mr-1.5" />
                                    Escalate
                                </button>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Description</h3>
                            <p class="text-sm text-slate-600 leading-relaxed bg-slate-50/50 p-4 rounded-xl border border-slate-50">
                                {{ selectedTicket.description }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Conversation</h3>
                            <div class="space-y-6">
                                <div v-for="msg in selectedTicket.messages" :key="msg.id" class="flex flex-col gap-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-900">{{ msg.user ? msg.user.name : 'System' }}</span>
                                            <span class="text-[10px] text-slate-400 font-medium">{{ new Date(msg.created_at).toLocaleString() }}</span>
                                        </div>
                                        <span v-if="msg.visibility === 'internal'" class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded uppercase tracking-tighter border border-amber-100">Internal Note</span>
                                    </div>
                                    <div :class="['text-sm p-4 rounded-2xl border', msg.user_id ? 'bg-white border-slate-100' : 'bg-slate-50 border-slate-100 text-slate-500 italic']">
                                        {{ msg.message }}
                                        
                                        <div v-if="msg.attachments && msg.attachments.length > 0" class="mt-4 flex flex-wrap gap-2">
                                            <a v-for="file in msg.attachments" :key="file.id" :href="'/storage/' + file.path" target="_blank" class="flex items-center gap-2 p-2 bg-slate-50 hover:bg-slate-100 rounded-lg border border-slate-100 transition-all">
                                                <component :is="getFileIcon(file.mime)" class="w-4 h-4 text-csired" />
                                                <span class="text-[10px] font-bold text-slate-600 truncate max-w-[120px]">{{ file.filename }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-slate-100 bg-slate-50/50">
                        <form @submit.prevent="submitMessage" class="space-y-4">
                            <div class="relative">
                                <textarea
                                    v-model="messageForm.message"
                                    rows="3"
                                    class="block w-full px-4 py-3 text-sm text-slate-900 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-red-50 focus:border-csired transition-all resize-none shadow-sm"
                                    placeholder="Add a comment or internal note..."
                                    required
                                ></textarea>
                                
                                <div class="absolute bottom-3 right-3 flex items-center gap-2">
                                    <label class="p-2 text-slate-400 hover:text-csired cursor-pointer transition-all rounded-full hover:bg-slate-50">
                                        <input type="file" multiple class="hidden" @change="handleFileChange">
                                        <PaperClipIcon class="w-5 h-5" />
                                    </label>
                                    <button
                                        type="submit"
                                        :disabled="messageForm.processing"
                                        class="p-2.5 bg-csired text-white rounded-xl shadow-lg shadow-red-100 hover:bg-[#D31920] transition-all disabled:opacity-50"
                                    >
                                        <PaperAirplaneIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            
                            <div v-if="messageForm.attachments.length > 0" class="flex flex-wrap gap-2">
                                <div v-for="(file, index) in messageForm.attachments" :key="index" class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-500 flex items-center gap-2 shadow-sm">
                                    {{ file.name }}
                                    <button @click="messageForm.attachments.splice(index, 1)" class="text-slate-400 hover:text-red-500"><XMarkIcon class="w-3 h-3"/></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>

            <div v-if="isDrawerOpen" @click="closeDrawer" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-50"></div>
        </div>

        <ConfirmationModal :show="confirmingEscalation" @close="confirmingEscalation = false">
            <template #title>
                Escalate Ticket
            </template>
            <template #content>
                Are you sure you want to escalate this ticket to the next level?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingEscalation = false">
                    Cancel
                </SecondaryButton>
                <DangerButton class="ml-3" @click="executeEscalation">
                    Escalate
                </DangerButton>
            </template>
        </ConfirmationModal>

        <ConfirmationModal :show="confirmingRejection" @close="confirmingRejection = false">
            <template #title>
                Reject Transfer
            </template>
            <template #content>
                Are you sure you want to reject this ticket transfer? The ticket will be returned to the assigning agent.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingRejection = false">
                    Cancel
                </SecondaryButton>
                <DangerButton class="ml-3" @click="executeRejection">
                    Reject Transfer
                </DangerButton>
            </template>
        </ConfirmationModal>

        <ConfirmationModal :show="isTransferModalOpen" @close="closeTransferModal">
            <template #title>
                Initiate Ticket Transfer
            </template>
            <template #content>
                <div class="space-y-4 text-left mt-4" v-if="pendingTransferTicket">
                    <p class="text-sm text-slate-600">Select an agent and provide a reason to hand off this ticket.</p>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Receiving Agent</label>
                        <select v-model="transferForm.to_user_id" class="block w-full text-sm text-slate-900 border-slate-200 rounded-xl focus:border-csired focus:ring-red-50" required>
                            <option value="" disabled>Select an Agent...</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} ({{ user.job_title || 'Agent' }})</option>
                        </select>
                        <div v-if="transferForm.errors.to_user_id" class="text-xs text-red-500 mt-1">{{ transferForm.errors.to_user_id }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Reason</label>
                        <select v-model="transferForm.transfer_reason_id" class="block w-full text-sm text-slate-900 border-slate-200 rounded-xl focus:border-csired focus:ring-red-50" required>
                            <option value="" disabled>Select a Reason...</option>
                            <option v-for="reason in transferReasons" :key="reason.id" :value="reason.id">{{ reason.name }}</option>
                        </select>
                        <div v-if="transferForm.errors.transfer_reason_id" class="text-xs text-red-500 mt-1">{{ transferForm.errors.transfer_reason_id }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Notes (Optional)</label>
                        <textarea v-model="transferForm.notes" rows="3" class="block w-full text-sm text-slate-900 border-slate-200 rounded-xl focus:border-csired focus:ring-red-50 resize-none" placeholder="Add context for the receiving agent..."></textarea>
                    </div>
                </div>
            </template>
            <template #footer>
                <SecondaryButton @click="closeTransferModal">
                    Cancel
                </SecondaryButton>
                <DangerButton class="ml-3" @click="submitTransfer" :disabled="transferForm.processing">
                    Initiate Transfer
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AuthenticatedLayout>
</template>

<style scoped>
.min-w-max {
    min-width: max-content;
}

.vertical-text {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    transform: rotate(180deg);
}

::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
