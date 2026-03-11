<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import { ref, watch } from 'vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { 
    PlusIcon, 
    PencilSquareIcon, 
    TrashIcon, 
    BuildingOfficeIcon,
    XMarkIcon,
    TagIcon,
    Bars3BottomLeftIcon,
    Bars4Icon,
    QueueListIcon,
    BriefcaseIcon,
    ChevronDoubleRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    departments: Array,
});

const isModalOpen = ref(false);
const isManageModalOpen = ref(false);
const editingDepartment = ref(null);
const localLevels = ref([]);
const activeTab = ref('levels'); // 'levels', 'topics', 'queues', 'categories', 'types'
const activeCategory = ref(null);

// Keep the editing department reference fresh and sync local levels when Inertia updates props
watch(() => props.departments, (newDepts) => {
    if (editingDepartment.value) {
        const updatedDept = newDepts.find(d => d.id === editingDepartment.value.id);
        if (updatedDept) {
            editingDepartment.value = updatedDept;
            localLevels.value = [...updatedDept.levels];
        }
    }
}, { deep: true });

const form = useForm({
    name: '',
    is_shared_service: false,
});

const levelForm = useForm({
    department_id: '',
    name: '',
    rank: 1,
});

const topicForm = useForm({
    department_id: '',
    name: '',
});



const openModal = (dept = null) => {
    editingDepartment.value = dept;
    if (dept) {
        form.name = dept.name;
        form.is_shared_service = !!dept.is_shared_service;
    } else {
        form.name = '';
        form.is_shared_service = false;
    }
    isModalOpen.value = true;
};

const openManageModal = (dept) => {
    editingDepartment.value = dept;
    localLevels.value = [...dept.levels];
    levelForm.department_id = dept.id;
    topicForm.department_id = dept.id;
    activeTab.value = 'levels';
    isManageModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    isManageModalOpen.value = false;
    editingDepartment.value = null;
    form.reset();
    levelForm.reset();
    topicForm.reset();
};

const submit = () => {
    if (editingDepartment.value) {
        form.put(route('configuration.departments.update', editingDepartment.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('configuration.departments.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const addLevel = () => {
    levelForm.post(route('configuration.levels.store'), {
        preserveScroll: true,
        onSuccess: () => levelForm.reset('name', 'rank'),
    });
};

const confirmingLevelDeletion = ref(false);
const levelToDelete = ref(null);

const deleteLevel = (id) => {
    levelToDelete.value = id;
    confirmingLevelDeletion.value = true;
};

const executeLevelDeletion = () => {
    levelForm.delete(route('configuration.levels.destroy', levelToDelete.value), {
        preserveScroll: true,
        onSuccess: () => confirmingLevelDeletion.value = false,
    });
};

const addTopic = () => {
    topicForm.post(route('configuration.topics.store'), {
        preserveScroll: true,
        onSuccess: () => topicForm.reset('name'),
    });
};

const confirmingTopicDeletion = ref(false);
const topicToDelete = ref(null);

const deleteTopic = (id) => {
    topicToDelete.value = id;
    confirmingTopicDeletion.value = true;
};

const executeTopicDeletion = () => {
    topicForm.delete(route('configuration.topics.destroy', topicToDelete.value), {
        preserveScroll: true,
        onSuccess: () => confirmingTopicDeletion.value = false,
    });
};

const confirmingDepartmentDeletion = ref(false);
const departmentToDelete = ref(null);

const deleteDepartment = (id) => {
    departmentToDelete.value = id;
    confirmingDepartmentDeletion.value = true;
};

const executeDepartmentDeletion = () => {
    form.delete(route('configuration.departments.destroy', departmentToDelete.value), {
        preserveScroll: true,
        onSuccess: () => confirmingDepartmentDeletion.value = false,
    });
};

const updateLevelOrder = () => {
    const levelIds = localLevels.value.map(l => l.id);
    router.post(route('configuration.levels.reorder'), {
        level_ids: levelIds
    }, {
        preserveScroll: true,
    });
};



</script>

<template>
    <Head title="Departments Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                    <BuildingOfficeIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Departments</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-tight">Manage corporate structure and ticket routing entities.</p>
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
                    New Department
                </button>
            </div>

            <div class="w-full px-4 sm:px-6 lg:px-8">
                <!-- Content Area -->
                <div class="bg-white shadow-xl sm:rounded-[1rem] border border-slate-100 overflow-hidden">
                    <div class="min-w-full overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th scope="col" class="py-4 pl-8 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">Department</th>
                                    <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Hierarchy (Levels)</th>
                                    <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">Topics</th>
                                    <th scope="col" class="relative py-4 pl-3 pr-8 text-right">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-for="dept in departments" :key="dept.id" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="whitespace-nowrap py-4 pl-8 pr-3">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-bold text-slate-900 uppercase tracking-tight">{{ dept.name }}</div>
                                            <span v-if="dept.is_shared_service" class="px-1.5 py-0.5 bg-red-100 text-csired text-[8px] font-black rounded uppercase tracking-widest border border-red-200 shadow-sm">Mgmt. Services</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">ID: #{{ dept.id }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 bg-red-50 text-csired text-[10px] font-bold rounded-md border border-red-100">
                                                {{ dept.levels.length }} Levels
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-md border border-slate-200">
                                                {{ dept.topics.length }} Topics
                                            </span>
                                        </div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-8 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <button 
                                                @click="openManageModal(dept)"
                                                class="px-3 py-1.5 text-[10px] font-bold text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg transition-all uppercase tracking-widest"
                                                title="Manage Hierarchy & Topics"
                                            >
                                                Configure
                                            </button>
                                            <button 
                                                @click="openModal(dept)"
                                                class="p-2 text-slate-400 hover:text-csired hover:bg-red-50 rounded-lg transition-all"
                                                title="Rename"
                                            >
                                                <PencilSquareIcon class="w-5 h-5" />
                                            </button>
                                            <button 
                                                @click="deleteDepartment(dept.id)"
                                                class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                                title="Delete Department"
                                            >
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="departments.length === 0">
                                    <td colspan="4" class="py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <BuildingOfficeIcon class="w-12 h-12 text-slate-200 mb-4" />
                                            <h3 class="text-slate-900 font-bold uppercase tracking-tight text-sm">No departments found</h3>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rename/New Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div @click="closeModal" class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-white">
                    <div class="bg-white p-8">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight" id="modal-title">
                                {{ editingDepartment ? 'Rename Department' : 'New Department' }}
                            </h3>
                            <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 rounded-full transition-colors">
                                <XMarkIcon class="w-6 h-6" />
                            </button>
                        </div>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-6">
                                <div>
                                    <label for="name" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Department Name</label>
                                    <input type="text" id="name" v-model="form.name" class="block w-full px-4 py-4 text-slate-900 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-red-50 focus:border-csired transition-all font-semibold" placeholder="e.g. IT Support" required />
                                    <div v-if="form.errors.name" class="mt-2 text-sm text-red-600 font-medium">{{ form.errors.name }}</div>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <div class="flex items-center gap-3">
                                        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shadow-sm transition-colors', form.is_shared_service ? 'bg-csired' : 'bg-slate-200']">
                                            <BuildingOfficeIcon class="w-6 h-6 text-white" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">Management Services Layer</p>
                                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter">Global Escalation Hub</p>
                                        </div>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="form.is_shared_service = !form.is_shared_service"
                                        :class="['relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2', form.is_shared_service ? 'bg-csired' : 'bg-slate-200']"
                                    >
                                        <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', form.is_shared_service ? 'translate-x-5' : 'translate-x-0']" />
                                    </button>
                                </div>
                            </div>
                            <div class="pt-4 flex gap-3">
                                <button type="submit" :disabled="form.processing" class="flex-1 px-6 py-4 border border-transparent text-sm font-bold rounded-2xl text-white bg-csired hover:bg-[#D31920] shadow-lg shadow-red-100 transition-all active:scale-95 disabled:opacity-50">Save</button>
                                <button type="button" @click="closeModal" class="flex-1 px-6 py-4 border border-slate-100 text-sm font-bold rounded-2xl text-slate-600 bg-slate-50 hover:bg-slate-100 transition-all active:scale-95">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Hierarchy & Topics Modal -->
        <div v-if="isManageModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div @click="closeModal" class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-white">
                    <div class="bg-white p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center">
                                    <Bars3BottomLeftIcon class="w-6 h-6 text-csired" />
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 tracking-tight" id="modal-title">
                                    Configure: {{ editingDepartment?.name }}
                                </h3>
                            </div>
                            <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 rounded-full transition-colors">
                                <XMarkIcon class="w-6 h-6" />
                            </button>
                        </div>

                        <!-- Tab Headers -->
                        <div class="flex border-b border-slate-100 mb-6 overflow-x-auto">
                            <button @click="activeTab = 'levels'" :class="['px-6 py-3 text-xs font-bold uppercase tracking-widest transition-all border-b-2', activeTab === 'levels' ? 'border-csired text-csired' : 'border-transparent text-slate-400']">Hierarchy</button>
                            <button @click="activeTab = 'topics'" :class="['px-6 py-3 text-xs font-bold uppercase tracking-widest transition-all border-b-2', activeTab === 'topics' ? 'border-csired text-csired' : 'border-transparent text-slate-400']">Topics</button>
                        </div>

                        <!-- Levels Tab -->
                        <div v-if="activeTab === 'levels'" class="space-y-6">
                            <div class="bg-slate-50 p-4 rounded-2xl flex items-end gap-3">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Level Name</label>
                                    <input v-model="levelForm.name" type="text" class="w-full text-sm font-bold bg-white border-transparent rounded-xl focus:ring-2 focus:ring-red-100" placeholder="e.g. Lead Engineer" />
                                </div>
                                <div class="w-24">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Rank (Order)</label>
                                    <input v-model="levelForm.rank" type="number" class="w-full text-sm font-bold bg-white border-transparent rounded-xl focus:ring-2 focus:ring-red-100" />
                                </div>
                                <button @click="addLevel" class="p-2.5 bg-csired text-white rounded-xl shadow-lg shadow-red-100 hover:bg-[#D31920] transition-all"><PlusIcon class="w-5 h-5"/></button>
                            </div>
                            <draggable 
                                :list="localLevels" 
                                @change="updateLevelOrder" 
                                item-key="id" 
                                tag="div"
                                class="max-h-60 overflow-y-auto space-y-2 pr-2"
                                handle=".drag-handle"
                                :animation="200"
                                ghost-class="opacity-50"
                            >
                                <template #item="{element}">
                                    <div class="flex items-center justify-between p-2.5 bg-white border border-slate-100 rounded-xl group hover:border-red-100 hover:shadow-sm transition-all">
                                        <div class="flex items-center gap-3">
                                            <div class="drag-handle cursor-grab active:cursor-grabbing p-1 -ml-1 text-slate-300 hover:text-slate-500 transition-colors">
                                                <Bars4Icon class="w-5 h-5" />
                                            </div>
                                            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-csired text-[10px] font-black border border-red-100 uppercase tracking-tighter">L{{ element.rank }}</div>
                                            <span class="text-sm font-black text-slate-700 uppercase tracking-tight">{{ element.name }}</span>
                                        </div>
                                        <button @click.stop="deleteLevel(element.id)" class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                                            <TrashIcon class="w-4 h-4"/>
                                        </button>
                                    </div>
                                </template>
                            </draggable>
                        </div>

                        <!-- Topics Tab -->
                        <div v-if="activeTab === 'topics'" class="space-y-6">
                            <div class="bg-slate-50 p-4 rounded-2xl flex items-end gap-3">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Topic Title</label>
                                    <input v-model="topicForm.name" type="text" class="w-full text-sm font-bold bg-white border-transparent rounded-xl focus:ring-2 focus:ring-red-100" placeholder="e.g. Server Maintenance" />
                                </div>
                                <button @click="addTopic" class="p-2.5 bg-csired text-white rounded-xl shadow-lg shadow-red-100 hover:bg-[#D31920] transition-all"><PlusIcon class="w-5 h-5"/></button>
                            </div>
                            <div class="max-h-60 overflow-y-auto space-y-2 pr-2 font-bold uppercase tracking-tight">
                                <div v-for="topic in editingDepartment.topics" :key="topic.id" class="flex items-center justify-between p-3 bg-white border border-slate-100 rounded-xl group hover:border-red-100">
                                    <div class="flex items-center gap-3 text-slate-700">
                                        <TagIcon class="w-4 h-4 text-slate-300" />
                                        <span class="text-sm font-bold">{{ topic.name }}</span>
                                    </div>
                                    <button @click="deleteTopic(topic.id)" class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all opacity-0 group-hover:opacity-100"><TrashIcon class="w-4 h-4"/></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modals -->
        <ConfirmationModal :show="confirmingDepartmentDeletion" @close="confirmingDepartmentDeletion = false">
            <template #title>Delete Department</template>
            <template #content>Are you sure you want to delete this department? This action cannot be undone.</template>
            <template #footer>
                <SecondaryButton @click="confirmingDepartmentDeletion = false">Cancel</SecondaryButton>
                <DangerButton class="ml-3" @click="executeDepartmentDeletion" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Delete</DangerButton>
            </template>
        </ConfirmationModal>

        <ConfirmationModal :show="confirmingLevelDeletion" @close="confirmingLevelDeletion = false">
            <template #title>Delete Level</template>
            <template #content>Are you sure you want to delete this level? This action cannot be undone.</template>
            <template #footer>
                <SecondaryButton @click="confirmingLevelDeletion = false">Cancel</SecondaryButton>
                <DangerButton class="ml-3" @click="executeLevelDeletion" :class="{ 'opacity-25': levelForm.processing }" :disabled="levelForm.processing">Delete</DangerButton>
            </template>
        </ConfirmationModal>

        <ConfirmationModal :show="confirmingTopicDeletion" @close="confirmingTopicDeletion = false">
            <template #title>Delete Topic</template>
            <template #content>Are you sure you want to delete this topic? This action cannot be undone.</template>
            <template #footer>
                <SecondaryButton @click="confirmingTopicDeletion = false">Cancel</SecondaryButton>
                <DangerButton class="ml-3" @click="executeTopicDeletion" :class="{ 'opacity-25': topicForm.processing }" :disabled="topicForm.processing">Delete</DangerButton>
            </template>
        </ConfirmationModal>
    </AuthenticatedLayout>
</template>
