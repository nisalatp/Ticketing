<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    TicketIcon, 
    ArrowLeftIcon,
    ArrowUpIcon,
    ExclamationTriangleIcon,
    DocumentTextIcon,
    InformationCircleIcon,
    PaperClipIcon,
    XMarkIcon,
    DocumentIcon,
    PhotoIcon,
    VideoCameraIcon
} from '@heroicons/vue/24/outline';
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({
    ticket: Object,
});

const getPriorityColor = (priority) => {
    switch (priority) {
        case 'Urgent': return 'bg-red-100 text-red-700';
        case 'High': return 'bg-orange-100 text-orange-700';
        case 'Medium': return 'bg-blue-100 text-blue-700';
        default: return 'bg-slate-100 text-slate-600';
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'Assigned': return 'bg-blue-100 text-blue-800';
        case 'In Progress': return 'bg-indigo-100 text-indigo-800';
        case 'Waiting': return 'bg-amber-100 text-amber-800';
        case 'Resolved': return 'bg-emerald-100 text-emerald-800';
        case 'Closed': return 'bg-slate-100 text-slate-800';
        default: return 'bg-slate-100 text-slate-800';
    }
};

const getTicketTypeIcon = (iconName) => {
    switch (iconName) {
        case 'ExclamationTriangleIcon': return ExclamationTriangleIcon;
        case 'DocumentTextIcon': return DocumentTextIcon;
        case 'InformationCircleIcon': return InformationCircleIcon;
        default: return TicketIcon;
    }
};

const getFileIcon = (mime) => {
    if (mime.startsWith('image/')) return PhotoIcon;
    if (mime.startsWith('video/')) return VideoCameraIcon;
    return DocumentIcon;
};

const form = useForm({
    message: '',
    attachments: [],
});

const handleFileChange = (e) => {
    form.attachments = Array.from(e.target.files);
};

const submitReply = () => {
    if (!form.message.trim() && form.attachments.length === 0) return;
    
    form.post(route('tickets.store-message', props.ticket.id), {
        onSuccess: () => {
            form.reset();
            scrollToBottom();
        }
    });
};

const messagesContainer = ref(null);

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
});

const getFileUrl = (path) => {
    if (!path) return '';
    return '/storage/' + path;
};

</script>

<template>
    <Head :title="ticket.ticket_no" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm border border-slate-200">
                        <Link :href="route('tickets.index')" class="text-slate-400 hover:text-csired transition-colors">
                            <ArrowLeftIcon class="w-5 h-5" />
                        </Link>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ ticket.ticket_no }}</h1>
                            <span :class="['text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-widest', getStatusColor(ticket.status)]">
                                {{ ticket.status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium tracking-tight truncate max-w-xl">{{ ticket.subject }}</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 h-[calc(100vh-140px)]">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 flex flex-col h-full overflow-hidden relative">
                <!-- Header Background Tint -->
                <div v-if="ticket.type" class="absolute top-0 left-0 right-0 h-32 opacity-10 pointer-events-none" :class="ticket.type.color_code.split(' ').find(c => c.startsWith('bg-')) || 'bg-slate-50'"></div>
                
                <!-- Ticket Details Header -->
                <div class="p-6 md:p-8 border-b border-slate-100 relative z-10">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-4">
                        <div class="flex-1">
                            <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight mb-3">{{ ticket.subject }}</h2>
                            <div class="flex flex-wrap items-center gap-3">
                                <span v-if="ticket.type" :class="['flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-bold border uppercase tracking-wider bg-white', ticket.type.color_code]">
                                    <component :is="getTicketTypeIcon(ticket.type.icon)" class="w-4 h-4" />
                                    {{ ticket.type.name }}
                                </span>
                                <span :class="['text-[10px] font-black px-2.5 py-1 rounded-md uppercase tracking-widest', getPriorityColor(ticket.priority)]">
                                    {{ ticket.priority }} Priority
                                </span>
                                <span class="text-sm font-medium text-slate-500 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                    <span>{{ ticket.department?.name }}</span>
                                    <span v-if="ticket.topic?.name" class="text-slate-400">&bull; {{ ticket.topic.name }}</span>
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                            <div class="flex flex-col items-end">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Assigned To</span>
                                <span class="text-sm font-bold text-slate-700">{{ ticket.assignee?.name || 'Pending Assignment' }}</span>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center overflow-hidden border-2 border-slate-200 shadow-sm">
                                <img v-if="ticket.assignee?.profile_photo_path" :src="'/storage/' + ticket.assignee.profile_photo_path" class="w-full h-full object-cover" @error="$event.target.style.display = 'none'; $event.target.nextElementSibling.style.display = 'flex'">
                                <span class="text-xs font-bold text-slate-500" :class="{'hidden': ticket.assignee?.profile_photo_path}">{{ ticket.assignee ? ticket.assignee.name.charAt(0) : '?' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-xl border border-slate-100 mt-4">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center overflow-hidden border-2 border-slate-200 shadow-sm">
                            <img v-if="!ticket.is_anonymous && ticket.requester?.profile_photo_path" :src="'/storage/' + ticket.requester.profile_photo_path" class="w-full h-full object-cover" @error="$event.target.style.display = 'none'; $event.target.nextElementSibling.style.display = 'flex'">
                            <span class="text-xs font-bold text-slate-500" :class="{'hidden': !ticket.is_anonymous && ticket.requester?.profile_photo_path}">
                                {{ ticket.is_anonymous ? 'A' : (ticket.requester ? ticket.requester.name.charAt(0) : '?') }}
                            </span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Requester</span>
                            <span class="text-sm font-bold text-slate-700">
                                {{ ticket.is_anonymous ? 'Anonymous' : ticket.requester?.name }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 md:p-6 bg-slate-50/50 rounded-2xl border border-slate-100">
                        <p class="text-sm text-slate-700 whitespace-pre-wrap leading-relaxed">{{ ticket.description }}</p>
                        
                        <!-- Original Attachments -->
                        <div v-if="ticket.attachments && ticket.attachments.length > 0" class="mt-4 pt-4 border-t border-slate-200/60 flex flex-wrap gap-2">
                            <a v-for="attachment in ticket.attachments" :key="attachment.id" :href="getFileUrl(attachment.path)" target="_blank"
                                class="flex items-center gap-2 px-3 py-2 bg-white rounded-xl border border-slate-200 shadow-sm hover:border-csired transition-colors group">
                                <component :is="getFileIcon(attachment.mime)" class="w-5 h-5 text-slate-400 group-hover:text-csired" />
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700 truncate max-w-[150px]">{{ attachment.filename }}</span>
                                    <span class="text-[9px] font-medium text-slate-400 uppercase tracking-wider">{{ (attachment.size / 1024).toFixed(1) }} KB</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6" ref="messagesContainer">
                    <div v-if="!ticket.messages || ticket.messages.length === 0" class="h-full flex flex-col items-center justify-center text-center opacity-50">
                        <InformationCircleIcon class="w-12 h-12 text-slate-400 mb-3" />
                        <p class="text-sm font-semibold text-slate-600">No replies yet.</p>
                        <p class="text-xs text-slate-500">Updates from the assigned agent will appear here.</p>
                    </div>
                    
                    <div v-for="message in ticket.messages" :key="message.id" 
                        :class="['flex gap-4 max-w-3xl', message.user_id === $page.props.auth.user.id ? 'ml-auto flex-row-reverse' : '']">
                        
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex-shrink-0 flex items-center justify-center overflow-hidden border border-slate-200 my-1">
                            <img v-if="!(message.user_id === ticket.requester_user_id && ticket.is_anonymous) && message.user?.profile_photo_path" :src="'/storage/' + message.user.profile_photo_path" class="w-full h-full object-cover" @error="$event.target.style.display = 'none'; $event.target.nextElementSibling.style.display = 'flex'">
                            <span class="text-[10px] font-bold text-slate-500" :class="{'hidden': !(message.user_id === ticket.requester_user_id && ticket.is_anonymous) && message.user?.profile_photo_path}">
                                {{ message.user_id === ticket.requester_user_id && ticket.is_anonymous ? 'A' : (message.user ? message.user.name.charAt(0) : '?') }}
                            </span>
                        </div>
                        
                        <div :class="['flex flex-col gap-1', message.user_id === $page.props.auth.user.id ? 'items-end' : '']">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-700">
                                    {{ message.user_id === ticket.requester_user_id && ticket.is_anonymous ? 'Anonymous' : message.user?.name }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">{{ new Date(message.created_at).toLocaleString([], {hour: '2-digit', minute:'2-digit', month:'short', day:'numeric'}) }}</span>
                            </div>
                            
                            <div :class="[
                                'px-5 py-3 rounded-2xl text-sm leading-relaxed shadow-sm',
                                message.user_id === $page.props.auth.user.id 
                                    ? 'bg-csired text-white rounded-tr-sm' 
                                    : 'bg-slate-50 text-slate-800 border border-slate-100 rounded-tl-sm'
                            ]" style="white-space: pre-wrap;">{{ message.message }}</div>
                            
                            <!-- Message Attachments -->
                            <div v-if="message.attachments && message.attachments.length > 0" class="flex flex-wrap gap-2 mt-2" :class="message.user_id === $page.props.auth.user.id ? 'justify-end' : ''">
                                <a v-for="att in message.attachments" :key="att.id" :href="getFileUrl(att.path)" target="_blank"
                                    :class="[
                                        'flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-semibold shadow-sm transition-colors',
                                        message.user_id === $page.props.auth.user.id 
                                            ? 'bg-red-50 border-red-100 text-csired hover:bg-red-100' 
                                            : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'
                                    ]">
                                    <component :is="getFileIcon(att.mime)" class="w-4 h-4 opacity-70" />
                                    <span class="truncate max-w-[120px]">{{ att.filename }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reply Input Area -->
                <div class="p-4 md:p-6 bg-slate-50 border-t border-slate-200">
                    <form @submit.prevent="submitReply">
                        <!-- Attachment Preview -->
                        <div v-if="form.attachments.length > 0" class="mb-3 flex flex-wrap gap-2">
                            <div v-for="(file, index) in form.attachments" :key="index" class="px-3 py-1 bg-white rounded-lg border border-slate-200 text-xs font-bold text-slate-600 flex items-center gap-2 shadow-sm">
                                <span class="truncate max-w-[150px]">{{ file.name }}</span>
                                <button type="button" @click="form.attachments.splice(index, 1)" class="text-slate-400 hover:text-csired transition-colors">
                                    <XMarkIcon class="w-4 h-4"/>
                                </button>
                            </div>
                        </div>
                        
                        <div class="relative bg-white rounded-2xl border border-slate-200 shadow-sm focus-within:ring-4 focus-within:ring-red-50 focus-within:border-csired transition-all flex items-end p-2 gap-2">
                            <label class="p-2 text-slate-400 hover:text-csired hover:bg-red-50 rounded-xl cursor-pointer transition-colors shrink-0">
                                <PaperClipIcon class="w-6 h-6" />
                                <input type="file" multiple class="hidden" @change="handleFileChange">
                            </label>
                            
                            <textarea 
                                v-model="form.message"
                                rows="1"
                                class="w-full border-0 focus:ring-0 resize-none py-2.5 px-2 text-sm text-slate-900 placeholder:text-slate-400 max-h-32 min-h-[44px] overflow-y-auto bg-transparent focus:outline-none"
                                placeholder="Type your reply..."
                                @keydown.enter.prevent="submitReply"
                            ></textarea>
                            
                            <button 
                                type="submit" 
                                :disabled="form.processing || (!form.message.trim() && form.attachments.length === 0)"
                                class="p-2.5 bg-csired text-white rounded-xl hover:bg-[#D31920] transition-colors shrink-0 disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-red-100"
                            >
                                <ArrowUpIcon class="w-5 h-5 font-bold stroke-2" />
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
