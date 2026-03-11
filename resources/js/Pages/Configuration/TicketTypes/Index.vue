<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { 
    PlusIcon, 
    PencilSquareIcon, 
    TrashIcon, 
    TicketIcon,
    XMarkIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon,
    DocumentTextIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    ticketTypes: Array,
});

const isModalOpen = ref(false);
const editingType = ref(null);

const form = useForm({
    name: '',
    icon: 'InformationCircleIcon',
    color_code: 'bg-blue-50 text-blue-700 border-blue-100',
    severity_factor: 1,
    is_confidential: false,
    allow_anonymous: false,
});

const openModal = (type = null) => {
    editingType.value = type;
    if (type) {
        form.name = type.name;
        form.icon = type.icon;
        form.color_code = type.color_code;
        form.severity_factor = type.severity_factor;
        form.is_confidential = !!type.is_confidential;
        form.allow_anonymous = !!type.allow_anonymous;
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingType.value = null;
    form.reset();
};

const submit = () => {
    if (editingType.value) {
        form.put(route('configuration.ticket-types.update', editingType.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('configuration.ticket-types.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmingDeletion = ref(false);
const typeToDelete = ref(null);

const deleteType = (id) => {
    typeToDelete.value = id;
    confirmingDeletion.value = true;
};

const executeDeletion = () => {
    router.delete(route('configuration.ticket-types.destroy', typeToDelete.value), {
        onSuccess: () => confirmingDeletion.value = false,
    });
};

const getIcon = (name) => {
    switch (name) {
        case 'InformationCircleIcon': return InformationCircleIcon;
        case 'ExclamationTriangleIcon': return ExclamationTriangleIcon;
        case 'DocumentTextIcon': return DocumentTextIcon;
        default: return TicketIcon;
    }
};

const iconOptions = [
    { name: 'InformationCircleIcon', label: 'Info' },
    { name: 'ExclamationTriangleIcon', label: 'Warning/Report' },
    { name: 'DocumentTextIcon', label: 'Note/Request' },
    { name: 'TicketIcon', label: 'General' },
];

const colorOptions = [
    { code: 'bg-blue-50 text-blue-700 border-blue-100', label: 'Blue (Request)' },
    { code: 'bg-orange-50 text-orange-700 border-orange-100', label: 'Orange (Complaint)' },
    { code: 'bg-slate-50 text-slate-600 border-slate-100', label: 'Gray (Notify)' },
    { code: 'bg-red-50 text-csired border-red-100', label: 'Red (Critical)' },
];
</script>

<template>
    <Head title="Ticket Types Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                    <TicketIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Ticket Types</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-tight">Global classification and severity weights for all tickets.</p>
                </div>
            </div>
        </template>

        <div class="py-2 mt-4">
            <div class="w-full px-4 sm:px-6 lg:px-8 text-right mb-4">
                 <button
                    @click="openModal()"
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-xs font-bold rounded-xl text-white bg-csired hover:bg-[#D31920] shadow-lg shadow-red-100 transition-all active:scale-95 uppercase tracking-widest"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    New Ticket Type
                </button>
            </div>

            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="type in ticketTypes" :key="type.id" class="bg-white rounded-[1.5rem] p-6 border border-slate-100 shadow-xl shadow-slate-200/20 hover:shadow-2xl hover:shadow-red-50 transition-all group relative overflow-hidden">
                        <!-- Decorative bg -->
                        <div class="absolute -right-4 -bottom-4 w-24 h-24 text-slate-50 opacity-50 group-hover:scale-110 transition-transform">
                            <component :is="getIcon(type.icon)" class="w-full h-full" />
                        </div>

                        <div class="flex items-center gap-4 relative z-10">
                            <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm border', type.color_code || 'bg-slate-50 text-slate-400 border-slate-100']">
                                <component :is="getIcon(type.icon)" class="w-7 h-7" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-black text-slate-900 uppercase tracking-tight truncate">{{ type.name }}</h3>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Weight: {{ type.severity_factor }}x</span>
                                    <span v-if="type.is_confidential" class="text-[8px] px-1.5 py-0.5 bg-red-100 text-csired font-black rounded uppercase">Confidential</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-between relative z-10">
                            <div class="flex gap-2">
                                <button @click="openModal(type)" class="p-2 text-slate-400 hover:text-csired hover:bg-red-50 rounded-lg transition-all">
                                    <PencilSquareIcon class="w-5 h-5" />
                                </button>
                                <button @click="deleteType(type.id)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                            <div class="flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest gap-1 group-hover:text-csired transition-colors">
                                Details <ChevronRightIcon class="w-3 h-3" />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="ticketTypes.length === 0" class="py-20 text-center bg-white rounded-[2rem] border border-dashed border-slate-200">
                    <div class="flex flex-col items-center">
                        <TicketIcon class="w-12 h-12 text-slate-200 mb-4" />
                        <h3 class="text-slate-900 font-bold uppercase tracking-tight text-sm">No ticket types defined</h3>
                        <p class="text-xs text-slate-400 mt-1 uppercase font-bold tracking-widest">Global types help classify issues properly.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div @click="closeModal" class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-white">
                    <div class="bg-white p-8">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight" id="modal-title">
                                {{ editingType ? 'Update Ticket Type' : 'New Ticket Type' }}
                            </h3>
                            <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 rounded-full transition-colors">
                                <XMarkIcon class="w-6 h-6" />
                            </button>
                        </div>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Type Name</label>
                                    <input type="text" v-model="form.name" class="block w-full px-4 py-4 text-slate-900 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-red-50 focus:border-csired transition-all font-semibold" placeholder="e.g. Server Issue" required />
                                    <div v-if="form.errors.name" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.name }}</div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Severity Weight</label>
                                        <input type="number" step="0.1" v-model="form.severity_factor" class="block w-full px-4 py-4 text-slate-900 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:ring-4 focus:ring-red-50 focus:border-csired transition-all font-bold" required />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Icon</label>
                                        <select v-model="form.icon" class="block w-full px-4 py-4 text-slate-900 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:ring-4 focus:ring-red-50 focus:border-csired transition-all font-bold">
                                            <option v-for="opt in iconOptions" :key="opt.name" :value="opt.name">{{ opt.label }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Color Aesthetic</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button 
                                            v-for="opt in colorOptions" 
                                            :key="opt.code" 
                                            type="button"
                                            @click="form.color_code = opt.code"
                                            :class="['p-3 rounded-xl border-2 text-[10px] font-black uppercase tracking-tighter transition-all', form.color_code === opt.code ? 'border-csired ring-4 ring-red-50' : 'border-slate-100 opacity-60 hover:opacity-100', opt.code]"
                                        >
                                            {{ opt.label }}
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-6 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" v-model="form.is_confidential" class="w-5 h-5 rounded border-slate-300 text-csired focus:ring-csired">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">Confidential</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" v-model="form.allow_anonymous" class="w-5 h-5 rounded border-slate-300 text-csired focus:ring-csired">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">Anonymous</span>
                                    </label>
                                </div>
                            </div>
                            <div class="pt-4 flex gap-3">
                                <button type="submit" :disabled="form.processing" class="flex-1 px-6 py-4 border border-transparent text-sm font-bold rounded-2xl text-white bg-csired hover:bg-[#D31920] shadow-lg shadow-red-100 transition-all active:scale-95 disabled:opacity-50 uppercase tracking-widest">Save Type</button>
                                <button type="button" @click="closeModal" class="flex-1 px-6 py-4 border border-slate-100 text-sm font-bold rounded-2xl text-slate-600 bg-slate-50 hover:bg-slate-100 transition-all active:scale-95 uppercase tracking-widest">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Delete Ticket Type</template>
            <template #content>Are you sure you want to delete this ticket type? This will affect any templates using it, but existing tickets will remain intact.</template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false">Cancel</SecondaryButton>
                <DangerButton class="ml-3" @click="executeDeletion">Delete</DangerButton>
            </template>
        </ConfirmationModal>
    </AuthenticatedLayout>
</template>
