<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    TicketIcon, 
    ExclamationTriangleIcon,
    DocumentTextIcon,
    InformationCircleIcon,
    PaperClipIcon,
    XMarkIcon,
    PlusIcon,
    ArrowLeftIcon,
    BuildingOfficeIcon,
    TagIcon,
    ChevronRightIcon,
    CameraIcon,
    CheckCircleIcon,
    ExclamationCircleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    departments: Array,
    ticketTypes: Array,
});

const form = useForm({
    type_id: '',
    department_id: '',
    topic_id: '',
    priority: '',
    subject: '',
    description: '',
    is_anonymous: false,
    attachments: [],
});

const currentStep = ref(1);
const totalSteps = 6;

const stepTitles = [
    "Ticket Type",
    "Department",
    "Topic Identifier",
    "Urgency Level",
    "Details & Files",
    "Final Review"
];

const stepDescriptions = [
    "What kind of issue are you reporting?",
    "Who should handle this request?",
    "Help us route your ticket faster",
    "How critical is this issue?",
    "Provide details and any supporting files",
    "Confirm your submission details"
];

const selectedDepartment = computed(() => {
    return props.departments.find(d => d.id === form.department_id);
});

const selectedType = computed(() => {
    return props.ticketTypes.find(t => t.id === form.type_id);
});

const selectedTopic = computed(() => {
    if (!selectedDepartment.value) return null;
    if (!form.topic_id) return { name: 'General Support' };
    return selectedDepartment.value.topics.find(t => t.id === form.topic_id) || { name: 'General Support' };
});

const progressPercentage = computed(() => {
    return (currentStep.value / totalSteps) * 100;
});

const nextStep = () => {
    if (currentStep.value < totalSteps) {
        if (currentStep.value === 5 && (!form.subject || !form.description)) return;
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value === 4 && selectedDepartment.value && selectedDepartment.value.topics.length === 0) {
        currentStep.value = 2;
    } else if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const selectTypeAndAdvance = (id) => {
    form.type_id = id;
    setTimeout(() => nextStep(), 200);
};

const selectDepartmentAndAdvance = (id) => {
    form.department_id = id;
    form.topic_id = ''; 
    setTimeout(() => {
        const dept = props.departments.find(d => d.id === id);
        if (dept && dept.topics.length === 0) {
            currentStep.value = 4;
        } else {
            currentStep.value = 3;
        }
    }, 200);
};

const selectTopicAndAdvance = (id) => {
    form.topic_id = id;
    setTimeout(() => nextStep(), 200);
};

const selectPriorityAndAdvance = (priority) => {
    form.priority = priority;
    setTimeout(() => nextStep(), 200);
};

const isAttachmentsOpen = ref(false);

const handleFileChange = (e) => {
    const newFiles = Array.from(e.target.files);
    form.attachments = [...form.attachments, ...newFiles];
};

const removeAttachment = (index) => {
    form.attachments.splice(index, 1);
};

const getTicketTypeIcon = (iconName) => {
    switch (iconName) {
        case 'ExclamationTriangleIcon': return ExclamationTriangleIcon;
        case 'DocumentTextIcon': return DocumentTextIcon;
        case 'InformationCircleIcon': return InformationCircleIcon;
        default: return TicketIcon;
    }
};

const submit = () => {
    form.post(route('tickets.store'));
};
</script>

<template>
    <Head title="Create Ticket" />

    <AuthenticatedLayout>
        <!-- Custom Header for Wizard to maximize space -->
        <template #header>
            <div class="flex items-center justify-between -mt-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-200">
                        <PlusIcon class="w-6 h-6 text-white" />
                    </div>
                    <div class="flex items-start gap-3 divide-x divide-slate-100">
                        <div class="pr-1">
                            <h1 class="text-[14px] font-black text-slate-900 leading-none uppercase tracking-tight">Create Ticket</h1>
                            <div class="flex items-center gap-2 mt-1.5">
                                <div class="w-20 h-1 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-csired shadow-[0_0_8px_rgba(185,28,28,0.4)] transition-all duration-500 ease-out" :style="{ width: progressPercentage + '%' }"></div>
                                </div>
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ currentStep }} <span class="text-slate-200">/</span> {{ totalSteps }}</span>
                            </div>
                        </div>
                        <div class="pl-3 hidden sm:block">
                            <h2 class="text-[14px] font-black text-csired leading-none uppercase tracking-tight">{{ stepTitles[currentStep-1] }}</h2>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 leading-none">{{ stepDescriptions[currentStep-1] }}</p>
                        </div>
                    </div>
                </div>
                <Link :href="route('tickets.index')" class="p-2 text-slate-400 hover:text-csired hover:bg-red-50 rounded-xl transition-all">
                    <XMarkIcon class="w-6 h-6" />
                </Link>
            </div>
        </template>

        <div class="h-[calc(100vh-160px)] flex flex-col overflow-hidden -mx-4 sm:mx-0">
            <!-- Step Content with Auto-Height & No Scroll -->
            <div class="flex-1 px-4 py-2 overflow-y-auto no-scrollbar relative">
                <form @submit.prevent="submit" class="h-full">
                    <Transition name="slide-fade" mode="out-in">
                        
                        <!-- Step 1: Type -->
                        <div v-if="currentStep === 1" :key="1" class="flex flex-col h-full pt-4">
                            <!-- Mobile only titles -->
                            <div class="sm:hidden text-center mb-6">
                                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ stepTitles[0] }}</h2>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">{{ stepDescriptions[0] }}</p>
                            </div>

                            <!-- Anonymous Toggle -->
                            <div class="max-w-2xl mx-auto w-full mb-8">
                                <div @click="form.is_anonymous = !form.is_anonymous" 
                                    :class="[
                                        'p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-center justify-between group active:scale-95',
                                        form.is_anonymous ? 'bg-red-50 border-csired shadow-md' : 'bg-white border-slate-100 hover:border-slate-200'
                                    ]"
                                >
                                    <div class="flex items-center gap-4">
                                        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center transition-colors', form.is_anonymous ? 'bg-csired text-white' : 'bg-slate-50 text-slate-400 group-hover:bg-slate-100']">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-sm font-black uppercase tracking-widest block" :class="form.is_anonymous ? 'text-csired' : 'text-slate-900'">Anonymous Submission</span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 block">Hide your identity from everyone but superadmin</span>
                                        </div>
                                    </div>
                                    <div :class="['w-12 h-6 rounded-full p-1 transition-colors relative', form.is_anonymous ? 'bg-csired' : 'bg-slate-200']">
                                        <div :class="['w-4 h-4 bg-white rounded-full transition-all shadow-sm', form.is_anonymous ? 'translate-x-6' : 'translate-x-0']"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 pb-12 max-w-4xl mx-auto w-full">
                                <div v-for="(type, index) in ticketTypes" :key="type.id" 
                                    @click="selectTypeAndAdvance(type.id)"
                                    :class="[
                                        'p-4 sm:p-6 rounded-[2rem] border-2 cursor-pointer transition-all flex flex-col items-center text-center gap-3 sm:gap-4 group active:scale-95',
                                        form.type_id === type.id ? 'bg-red-50 border-csired shadow-xl shadow-red-100' : 'bg-white border-slate-100 hover:border-slate-200 hover:shadow-lg',
                                        (ticketTypes.length % 2 !== 0 && index === ticketTypes.length - 1) ? 'col-span-2 sm:col-span-1 justify-self-center w-full max-w-[calc(50%-6px)]' : ''
                                    ]"
                                >
                                    <div :class="['w-12 h-12 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center shadow-sm transition-transform group-hover:scale-110', form.type_id === type.id ? 'bg-csired text-white' : 'bg-slate-50 text-slate-400']">
                                        <component :is="getTicketTypeIcon(type.icon)" class="w-6 h-6 sm:w-8 sm:h-8" />
                                    </div>
                                    <div>
                                        <span class="text-[11px] sm:text-sm font-black uppercase tracking-widest block" :class="form.type_id === type.id ? 'text-csired' : 'text-slate-900'">{{ type.name }}</span>
                                        <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 block">Severity: {{ type.severity_factor }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Department -->
                        <div v-else-if="currentStep === 2" :key="2" class="flex flex-col h-full pt-4">
                            <!-- Mobile only titles -->
                            <div class="sm:hidden text-center mb-6">
                                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ stepTitles[1] }}</h2>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">{{ stepDescriptions[1] }}</p>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 pb-12 max-w-4xl mx-auto w-full">
                                <div v-for="(dept, index) in departments" :key="dept.id" 
                                    @click="selectDepartmentAndAdvance(dept.id)"
                                    :class="[
                                        'p-4 sm:p-6 rounded-[2rem] border-2 cursor-pointer transition-all flex flex-col items-center text-center gap-3 sm:gap-4 group active:scale-95',
                                        form.department_id === dept.id ? 'bg-red-50 border-csired shadow-xl shadow-red-100' : 'bg-white border-slate-100 hover:border-slate-200 hover:shadow-lg',
                                        (departments.length % 2 !== 0 && index === departments.length - 1) ? 'col-span-2 sm:col-span-1 justify-self-center w-full max-w-[calc(50%-6px)]' : ''
                                    ]"
                                >
                                    <div :class="['w-12 h-12 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center shadow-sm transition-transform group-hover:scale-110', form.department_id === dept.id ? 'bg-csired text-white' : 'bg-slate-50 text-slate-400']">
                                        <BuildingOfficeIcon class="w-6 h-6 sm:w-8 sm:h-8" />
                                    </div>
                                    <span class="text-[11px] sm:text-sm font-black uppercase tracking-widest leading-tight" :class="form.department_id === dept.id ? 'text-csired' : 'text-slate-900'">{{ dept.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Topic -->
                        <div v-else-if="currentStep === 3" :key="3" class="flex flex-col h-full pt-4">
                            <!-- Mobile only titles -->
                            <div class="sm:hidden text-center mb-6">
                                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ stepTitles[2] }}</h2>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">{{ stepDescriptions[2] }}</p>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 pb-8 max-w-4xl mx-auto w-full">
                                <div @click="selectTopicAndAdvance('')"
                                    :class="[
                                        'p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-center gap-3 group active:scale-95',
                                        form.topic_id === '' ? 'bg-red-50 border-csired shadow-md' : 'bg-white border-slate-100 hover:border-slate-200 hover:shadow-md',
                                        ((selectedDepartment?.topics?.length + 1) % 2 !== 0 && selectedDepartment?.topics?.length === 0) ? 'col-span-2 sm:col-span-1 justify-self-center w-full max-w-[calc(50%-6px)]' : ''
                                    ]"
                                >
                                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center transition-colors shrink-0', form.topic_id === '' ? 'bg-csired text-white' : 'bg-slate-50 text-slate-300 group-hover:bg-slate-100']">
                                        <InformationCircleIcon class="w-5 h-5" />
                                    </div>
                                    <span class="text-[11px] font-black uppercase tracking-widest leading-tight" :class="form.topic_id === '' ? 'text-csired' : 'text-slate-900'">General Support</span>
                                </div>

                                <div v-for="(topic, index) in selectedDepartment?.topics" :key="topic.id" 
                                    @click="selectTopicAndAdvance(topic.id)"
                                    :class="[
                                        'p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-center gap-3 group active:scale-95',
                                        form.topic_id === topic.id ? 'bg-red-50 border-csired shadow-md' : 'bg-white border-slate-100 hover:border-slate-200 hover:shadow-md',
                                        ((selectedDepartment?.topics?.length + 1) % 2 !== 0 && index === selectedDepartment?.topics?.length - 1) ? 'col-span-2 sm:col-span-1 justify-self-center w-full max-w-[calc(50%-6px)]' : ''
                                    ]"
                                >
                                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center transition-colors shrink-0', form.topic_id === topic.id ? 'bg-csired text-white' : 'bg-slate-50 text-slate-300 group-hover:bg-slate-100']">
                                        <TagIcon class="w-5 h-5" />
                                    </div>
                                    <span class="text-[11px] font-black uppercase tracking-widest leading-tight" :class="form.topic_id === topic.id ? 'text-csired' : 'text-slate-900'">{{ topic.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Priority -->
                        <div v-else-if="currentStep === 4" :key="4" class="flex flex-col h-full pt-4">
                            <!-- Mobile only titles -->
                            <div class="sm:hidden text-center mb-6">
                                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ stepTitles[3] }}</h2>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">{{ stepDescriptions[3] }}</p>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 pb-8 max-w-5xl mx-auto w-full">
                                <div v-for="(p, index) in [
                                    { name: 'Low', color: 'bg-emerald-500', icon: CheckCircleIcon, iconColor: 'text-emerald-500', bgColor: 'bg-emerald-50', text: 'Minor query or request with no immediate impact.' },
                                    { name: 'Medium', color: 'bg-amber-500', icon: InformationCircleIcon, iconColor: 'text-amber-500', bgColor: 'bg-amber-50', text: 'Important but non-critical issue with workarounds.' },
                                    { name: 'High', color: 'bg-orange-500', icon: ExclamationCircleIcon, iconColor: 'text-orange-500', bgColor: 'bg-orange-50', text: 'Significant impact on business operations.' },
                                    { name: 'Urgent', color: 'bg-csired', icon: ExclamationTriangleIcon, iconColor: 'text-csired', bgColor: 'bg-red-50', text: 'Critical system failure or security hazard.' }
                                ]" :key="p.name"
                                    @click="selectPriorityAndAdvance(p.name)"
                                    :class="[
                                        'p-5 rounded-[2rem] border-2 cursor-pointer transition-all flex flex-col items-center text-center gap-4 group active:scale-95 min-h-[180px] relative',
                                        form.priority === p.name ? 'border-csired bg-red-50 shadow-xl shadow-red-100' : 'bg-white border-slate-100 hover:border-slate-200 hover:shadow-lg',
                                        ((index === 3 && true) ? '' : '') 
                                    ]"
                                >
                                    <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm transition-transform group-hover:scale-110', form.priority === p.name ? 'bg-csired text-white' : 'bg-slate-50 ' + p.iconColor]">
                                        <component :is="p.icon" class="w-7 h-7" />
                                    </div>
                                    <div>
                                        <span class="text-xs font-black uppercase tracking-widest block mb-1" :class="form.priority === p.name ? 'text-csired' : 'text-slate-900'">{{ p.name }}</span>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tight leading-tight px-2">{{ p.text }}</p>
                                    </div>
                                    <div v-if="form.priority === p.name" class="absolute top-4 right-4 w-6 h-6 bg-csired rounded-full flex items-center justify-center shadow-lg shadow-red-200">
                                        <CheckCircleIcon class="w-4 h-4 text-white" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Details & Attachments Combined -->
                        <div v-else-if="currentStep === 5" :key="5" class="flex flex-col h-full max-w-3xl mx-auto w-full pt-4">
                            <!-- Mobile only titles -->
                            <div class="sm:hidden text-center mb-6">
                                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ stepTitles[4] }}</h2>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">{{ stepDescriptions[4] }}</p>
                            </div>

                            <div class="flex-1 flex flex-col gap-6 mb-8">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Ticket Subject</label>
                                    <input v-model="form.subject" type="text" placeholder="GIVE IT A CLEAR TITLE..." class="w-full text-sm font-black bg-slate-50 border-2 border-transparent focus:border-csired focus:bg-white rounded-[1.25rem] py-5 px-6 focus:ring-4 focus:ring-red-50 transition-all uppercase tracking-widest outline-none" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Full Description</label>
                                    <textarea v-model="form.description" placeholder="ELABORATE ON THE ISSUE..." class="w-full min-h-[150px] text-sm font-bold bg-slate-50 border-2 border-transparent focus:border-csired focus:bg-white rounded-[2rem] p-6 focus:ring-4 focus:ring-red-50 transition-all resize-none leading-relaxed outline-none"></textarea>
                                </div>

                                <!-- Collapsible Attachments -->
                                <div class="border-2 border-slate-100 rounded-[1.5rem] overflow-hidden transition-all" :class="{ 'border-csired/20 bg-red-50/10': isAttachmentsOpen }">
                                    <button type="button" @click="isAttachmentsOpen = !isAttachmentsOpen" class="w-full p-4 flex items-center justify-between group">
                                        <div class="flex items-center gap-3">
                                            <div :class="['w-10 h-10 rounded-xl flex items-center justify-center transition-all', form.attachments.length > 0 ? 'bg-csired text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200']">
                                                <PaperClipIcon class="w-5 h-5" />
                                            </div>
                                            <div class="text-left">
                                                <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Attachments</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tight mt-0.5">{{ form.attachments.length > 0 ? `${form.attachments.length} files selected` : 'None added' }}</p>
                                            </div>
                                        </div>
                                        <ChevronRightIcon :class="['w-5 h-5 text-slate-300 transition-transform duration-300', isAttachmentsOpen ? 'rotate-90 text-csired' : '']" />
                                    </button>

                                    <div v-if="isAttachmentsOpen" class="p-6 border-t border-slate-100 bg-white">
                                        <div class="relative bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-8 flex flex-col items-center justify-center text-center gap-3 group hover:bg-red-50/50 hover:border-csired/30 transition-all cursor-pointer">
                                            <input type="file" multiple class="absolute inset-0 opacity-0 cursor-pointer" @change="handleFileChange">
                                            <CameraIcon class="w-8 h-8 text-slate-300 group-hover:text-csired transition-colors" />
                                            <div>
                                                <p class="text-xs font-black uppercase tracking-widest text-slate-900">Upload Evidence</p>
                                                <p class="text-[9px] font-bold uppercase tracking-tighter text-slate-400 mt-1">Tap or drag files here</p>
                                            </div>
                                        </div>
                                        
                                        <div v-if="form.attachments.length > 0" class="mt-4 space-y-2">
                                            <div v-for="(file, index) in form.attachments" :key="index" class="p-2 bg-slate-50 rounded-lg flex items-center justify-between border border-slate-100">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <DocumentTextIcon class="w-4 h-4 text-slate-300" />
                                                    <span class="text-[9px] font-bold text-slate-600 truncate uppercase">{{ file.name }}</span>
                                                </div>
                                                <button type="button" @click="removeAttachment(index)" class="p-1 text-slate-300 hover:text-csired transition-colors"><XMarkIcon class="w-4 h-4"/></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6: Review -->
                        <div v-else-if="currentStep === 6" :key="6" class="flex flex-col h-full max-w-3xl mx-auto w-full pt-4">
                            <!-- Mobile only titles -->
                            <div class="sm:hidden text-center mb-6">
                                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ stepTitles[5] }}</h2>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">{{ stepDescriptions[5] }}</p>
                            </div>

                            <div class="flex-1 space-y-6 overflow-y-auto mb-8 pr-2 no-scrollbar">
                                <!-- Core Info -->
                                <div class="bg-white rounded-[2rem] border-2 border-slate-50 p-6 shadow-xl shadow-slate-100/50 flex items-center gap-6">
                                    <div :class="['w-20 h-20 rounded-3xl flex items-center justify-center text-white shadow-lg shadow-red-100', form.priority === 'Urgent' ? 'bg-csired animation-pulse' : 'bg-slate-900']">
                                        <component :is="getTicketTypeIcon(selectedType?.icon)" class="w-10 h-10" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Classification</p>
                                        <h3 class="text-base font-black text-slate-900 uppercase tracking-tight truncate">{{ selectedType?.name }}</h3>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span :class="['px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg', 
                                                form.priority === 'Urgent' ? 'bg-red-100 text-csired' : 'bg-slate-100 text-slate-600']">
                                                {{ form.priority }} Priority
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Routing -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Target</p>
                                        <p class="text-xs font-black text-slate-900 uppercase truncate">{{ selectedDepartment?.name }}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Topic</p>
                                        <p class="text-xs font-black text-slate-900 uppercase truncate">{{ selectedTopic?.name || 'General' }}</p>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="bg-white rounded-[2rem] border-2 border-red-50 p-8 shadow-sm">
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest border-b border-red-50 pb-4 mb-4">{{ form.subject }}</h4>
                                    <p class="text-xs text-slate-600 font-medium leading-relaxed whitespace-pre-wrap">{{ form.description }}</p>
                                    
                                    <div v-if="form.attachments.length > 0" class="mt-6 pt-6 border-t border-red-50">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Attachments ({{ form.attachments.length }})</p>
                                        <div class="flex flex-wrap gap-2">
                                            <div v-for="(file, i) in form.attachments" :key="i" class="px-3 py-1.5 bg-slate-50 rounded-lg text-[9px] font-bold text-slate-500 uppercase tracking-tight">
                                                {{ file.name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </form>
            </div>

            <!-- Compact Navigation Bar -->
            <div class="shrink-0 p-5 bg-white border-t border-slate-100 flex items-center gap-3">
                <button v-if="currentStep > 1" @click="prevStep" class="h-14 w-14 flex items-center justify-center bg-slate-50 text-slate-400 rounded-2xl hover:bg-slate-100 hover:text-slate-600 transition-all active:scale-90">
                    <ArrowLeftIcon class="w-6 h-6" />
                </button>
                <div v-else class="h-14 w-14 invisible"></div>

                <div class="flex-1">
                    <button v-if="currentStep < 5" @click="nextStep" class="w-full h-14 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-2xl disabled:opacity-30 transition-all active:scale-[0.98]" :disabled="currentStep === 1 && !form.type_id || currentStep === 2 && !form.department_id || currentStep === 4 && !form.priority">
                        Next Phase
                    </button>
                    <button v-else-if="currentStep === 5" @click="nextStep" class="w-full h-14 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-2xl disabled:opacity-30 transition-all active:scale-[0.98]" :disabled="currentStep === 5 && (!form.subject || !form.description)">
                        Review Details
                    </button>
                    <button v-else @click="submit" class="w-full h-14 bg-csired text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-2xl shadow-red-500/30 disabled:opacity-50 transition-all active:scale-[0.98] animate-pulse-subtle" :disabled="form.processing">
                        {{ form.processing ? '...' : 'SUBMIT TICKET' }}
                    </button>
                </div>

                <div v-if="currentStep === totalSteps" class="h-14 w-14 flex items-center justify-center bg-emerald-50 text-emerald-500 rounded-2xl border-2 border-emerald-100 shadow-sm transition-all animate-bounce-subtle">
                    <CheckCircleIcon class="w-7 h-7" />
                </div>
                <div v-else-if="currentStep >= 5" class="h-14 w-14 flex items-center justify-center bg-slate-50 rounded-2xl text-[9px] font-black text-slate-400 flex flex-col leading-none border border-slate-100">
                    <span class="mb-0.5">STEP</span>
                    <span class="text-sm text-slate-600">{{ currentStep }}</span>
                </div>
                <div v-else class="h-14 w-14 invisible"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

@keyframes pulse-subtle {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.95; transform: scale(0.99); }
}
.animate-pulse-subtle { animation: pulse-subtle 3s infinite ease-in-out; }

@keyframes bounce-subtle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
.animate-bounce-subtle { animation: bounce-subtle 2s infinite ease-in-out; }

/* Grid adjustments for compact mobile */
@media (max-width: 639px) {
    .grid-cols-1 { grid-template-columns: 1fr !important; }
}

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(20px);
  opacity: 0;
}
</style>
