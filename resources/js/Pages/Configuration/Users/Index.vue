<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { 
    UserIcon, 
    PencilSquareIcon, 
    BuildingOfficeIcon,
    AcademicCapIcon,
    TagIcon,
    XMarkIcon,
    ShieldCheckIcon,
    UserGroupIcon,
    PlusIcon,
    ChevronDownIcon,
    ChevronRightIcon,
    MagnifyingGlassIcon,
    FunnelIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    users: Array,
    departments: Array,
    topics: Array,
    filters: Object,
});

// Filtering logic
const search = ref(props.filters.search || '');
const department_id = ref(props.filters.department_id || '');
const topic_id = ref(props.filters.topic_id || '');

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        if (timeoutId) clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn(...args);
        }, delay);
    };
};

const applyFilters = debounce(() => {
    router.get(route('configuration.users.index'), {
        search: search.value,
        department_id: department_id.value,
        topic_id: topic_id.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search, department_id, topic_id], () => {
    applyFilters();
});

const clearFilters = () => {
    search.value = '';
    department_id.value = '';
    topic_id.value = '';
};

const isModalOpen = ref(false);
const editingUser = ref(null);

const form = useForm({
    memberships: [], // Array of { department_id, level_id }
    topic_ids: [],
    is_admin: false,
    is_manager: false,
    is_active: true,
});

// Topics selection moved inside department cards

const openModal = (user) => {
    editingUser.value = user;
    form.memberships = user.departments.map(d => ({
        department_id: d.id,
        level_id: d.pivot.level_id || '',
        is_expanded: false
    }));
    form.topic_ids = user.topics.map(t => t.id);
    form.is_admin = !!user.is_admin;
    form.is_manager = !!user.is_manager;
    form.is_active = !!user.is_active;
    isModalOpen.value = true;
};

const addMembership = () => {
    form.memberships.push({ department_id: '', level_id: '', is_expanded: true });
};

const removeMembership = (index) => {
    form.memberships.splice(index, 1);
};

const getLevelsForDept = (deptId) => {
    const dept = props.departments.find(d => d.id == deptId);
    return dept ? dept.levels : [];
};

const closeModal = () => {
    isModalOpen.value = false;
    editingUser.value = null;
    form.reset();
};

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    form.memberships = [];
    form.topic_ids = [];
    form.is_admin = false;
    form.is_manager = false;
    form.is_active = true;
    form.name = '';
    form.email = '';
    form.job_title = '';
    isModalOpen.value = true;
};

const submit = () => {
    if (editingUser.value) {
        form.put(route('configuration.users.update', editingUser.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('configuration.users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const getInitials = (name) => {
    return name?.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) || '??';
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                    <UserGroupIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">User Management</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-tight">Assign roles, departments, and topics to employees.</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="ml-auto inline-flex items-center gap-2 px-4 py-2 bg-csired hover:bg-[#D31920] text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-red-100 transition-all active:scale-95"
                >
                    <PlusIcon class="w-4 h-4" />
                    Add User
                </button>
            </div>
        </template>

        <div class="py-2">
            <div class="w-full px-2">
                <!-- Filters Section -->
                <div class="bg-white shadow-sm sm:rounded-xl mb-6 border border-slate-100 overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2 text-slate-900">
                                <FunnelIcon class="w-4 h-4 text-csired" />
                                <h3 class="font-black text-[9px] uppercase tracking-widest">Filters</h3>
                            </div>
                            <button @click="clearFilters" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-csired transition-colors flex items-center gap-1">
                                <XMarkIcon class="w-3 h-3" />
                                Reset
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="relative">
                                <MagnifyingGlassIcon class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                <input 
                                    v-model="search" 
                                    type="text" 
                                    placeholder="SEARCH BY NAME OR EMAIL..." 
                                    class="w-full pl-9 pr-3 py-1.5 bg-slate-50 border-none rounded-lg text-[10px] font-black focus:ring-2 focus:ring-csired/20 uppercase tracking-widest" 
                                />
                            </div>

                            <select v-model="department_id" class="bg-slate-50 border-none rounded-lg text-[10px] font-black focus:ring-2 focus:ring-csired/20 py-1.5 uppercase tracking-widest">
                                <option value="">ALL DEPARTMENTS</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>

                            <select v-model="topic_id" class="bg-slate-50 border-none rounded-lg text-[10px] font-black focus:ring-2 focus:ring-csired/20 py-1.5 uppercase tracking-widest">
                                <option value="">ALL TOPICS</option>
                                <option v-for="topic in topics" :key="topic.id" :value="topic.id">{{ topic.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- User Listing Table -->
                <div class="bg-white shadow-xl sm:rounded-[1rem] border border-slate-100 overflow-hidden">
                    <div class="min-w-full overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th scope="col" class="py-4 pl-8 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">Employee</th>
                                    <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">Department & Level</th>
                                    <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">Topics</th>
                                    <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                    <th scope="col" class="relative py-4 pl-3 pr-8 text-right">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="whitespace-nowrap py-4 pl-8 pr-3">
                                        <div class="flex items-center gap-3">
                                            <div v-if="user.profile_photo_path" class="w-10 h-10 rounded-full overflow-hidden border border-slate-100 shadow-sm">
                                                <img :src="'/storage/' + user.profile_photo_path" class="w-full h-full object-cover" @error="$event.target.style.display = 'none'; $event.target.parentElement.innerHTML = '<div class=\'w-full h-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs uppercase\'>' + getInitials(user.name) + '</div>'" />
                                            </div>
                                            <div v-else class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs border border-slate-200 uppercase">
                                                {{ getInitials(user.name) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-slate-900 flex items-center gap-1.5 uppercase tracking-tight">
                                                    {{ user.name }}
                                                    <ShieldCheckIcon v-if="user.is_admin" class="w-4 h-4 text-csired" title="Admin Access" />
                                                </div>
                                                <div class="text-xs text-slate-400 font-medium">{{ user.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <div v-if="user.departments.length > 0" class="space-y-2">
                                            <div v-for="dept in user.departments" :key="dept.id" class="p-2 bg-slate-50 rounded-xl border border-slate-100 flex flex-col gap-1">
                                                <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-700 uppercase tracking-tight">
                                                    <BuildingOfficeIcon class="w-3.5 h-3.5 text-slate-400" />
                                                    {{ dept.name }}
                                                </div>
                                                <div v-if="dept.pivot.level_id" class="flex items-center gap-1 text-[9px] font-bold text-csired uppercase tracking-widest px-1.5 py-0.5 bg-red-100/50 rounded-md w-fit">
                                                    <AcademicCapIcon class="w-2.5 h-2.5" />
                                                    Level {{ props.departments.find(d => d.id == dept.id)?.levels.find(l => l.id == dept.pivot.level_id)?.rank }}: {{ props.departments.find(d => d.id == dept.id)?.levels.find(l => l.id == dept.pivot.level_id)?.name }}
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-xs font-bold text-slate-400 italic">| Client Employee |</div>
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            <span v-for="topic in user.topics" :key="topic.id" 
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 uppercase tracking-tighter"
                                            >
                                                {{ topic.name }}
                                            </span>
                                            <span v-if="user.topics.length === 0" class="text-[10px] text-slate-400">No topics</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <span :class="[
                                            'px-2 py-0.5 text-[10px] font-bold rounded-full uppercase tracking-widest',
                                            user.is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-400 border border-slate-200'
                                        ]">
                                            {{ user.is_active ? 'Active' : 'Deactive' }}
                                        </span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-8 text-right text-sm font-medium">
                                        <button 
                                            @click="openModal(user)"
                                            class="p-2 text-slate-400 hover:text-csired hover:bg-red-50 rounded-lg transition-all"
                                            title="Configure Permissions"
                                        >
                                            <PencilSquareIcon class="w-5 h-5" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div @click="closeModal" class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-white">
                    <div class="bg-white p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center">
                                    <ShieldCheckIcon class="w-7 h-7 text-csired" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900 tracking-tight" id="modal-title">
                                        {{ editingUser ? 'Configure Employee' : 'Create New User' }}
                                    </h3>
                                    <p class="text-xs text-slate-500 font-medium uppercase tracking-widest">{{ editingUser ? editingUser.name : 'System Access' }}</p>
                                </div>
                            </div>
                            <button @click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 rounded-full transition-colors">
                                <XMarkIcon class="w-6 h-6" />
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Basic Info (Only for New User) -->
                            <div v-if="!editingUser" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest ml-1">Full Name</label>
                                    <input v-model="form.name" type="text" required class="block w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-red-500/10 placeholder-slate-300 transition-all outline-none" placeholder="John Doe" />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                                    <input v-model="form.email" type="email" required class="block w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-red-500/10 placeholder-slate-300 transition-all outline-none" placeholder="john@example.com" />
                                </div>
                            </div>
                            <div v-if="!editingUser" class="space-y-1.5">
                                <label class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest ml-1">Job Title</label>
                                <input v-model="form.job_title" type="text" class="block w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-red-500/10 placeholder-slate-300 transition-all outline-none" placeholder="Senior Support Lead" />
                            </div>

                            <!-- Multi-Department Management -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Department Memberships</label>
                                    <button type="button" @click="addMembership" class="text-[10px] font-bold text-csired hover:text-[#D31920] uppercase tracking-widest flex items-center gap-1">
                                        <PlusIcon class="w-3.5 h-3.5" /> Add Dept
                                    </button>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="(m, index) in form.memberships" :key="index" class="p-4 bg-slate-50 rounded-2xl border border-slate-100 relative group transition-all">
                                        <button @click="removeMembership(index)" type="button" class="absolute -top-2 -right-2 p-1 bg-white shadow-md border border-slate-100 rounded-full text-slate-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all z-10">
                                            <XMarkIcon class="w-4 h-4" />
                                        </button>
                                        
                                        <!-- Top Row: Dept & Level -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Select Department</label>
                                                <select v-model="m.department_id" class="w-full text-sm font-bold bg-white border-transparent rounded-xl focus:ring-2 focus:ring-red-100 py-2">
                                                    <option value="" disabled>Choose...</option>
                                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Select Level</label>
                                                <div class="flex items-center gap-2">
                                                    <select v-model="m.level_id" class="flex-1 text-sm font-bold bg-white border-transparent rounded-xl focus:ring-2 focus:ring-red-100 py-2">
                                                        <option value="">No Level</option>
                                                        <option v-for="level in getLevelsForDept(m.department_id)" :key="level.id" :value="level.id">Level {{ level.rank }}: {{ level.name }}</option>
                                                    </select>

                                                    <!-- Expand/Collapse Topics Toggle -->
                                                    <button 
                                                        v-if="m.department_id && departments.find(d => d.id === m.department_id)?.topics?.length > 0"
                                                        type="button" 
                                                        @click="m.is_expanded = !m.is_expanded" 
                                                        class="w-10 h-10 flex items-center justify-center bg-white rounded-xl border border-slate-200 text-slate-400 hover:text-csired hover:bg-red-50 transition-colors shrink-0"
                                                        title="Toggle Topics"
                                                    >
                                                        <ChevronDownIcon v-if="m.is_expanded" class="w-5 h-5" />
                                                        <ChevronRightIcon v-else class="w-5 h-5" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Collapsible Topics Area -->
                                        <div v-show="m.department_id && m.is_expanded && departments.find(d => d.id === m.department_id)?.topics?.length > 0" class="mt-4 pt-4 border-t border-slate-200">
                                            <div class="flex items-center gap-2 mb-3">
                                                <TagIcon class="w-4 h-4 text-slate-400" />
                                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">Assign Topics</span>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <div v-for="topic in departments.find(d => d.id === m.department_id)?.topics" :key="topic.id" 
                                                    @click="form.topic_ids.includes(topic.id) ? form.topic_ids = form.topic_ids.filter(id => id !== topic.id) : form.topic_ids.push(topic.id)"
                                                    :class="[
                                                        'px-3 py-1.5 rounded-lg border-2 cursor-pointer transition-all flex items-center gap-1.5',
                                                        form.topic_ids.includes(topic.id) ? 'bg-red-50 border-csired text-csired' : 'bg-white border-transparent text-slate-500 hover:bg-slate-100 shadow-sm'
                                                    ]"
                                                >
                                                    <span class="text-[10px] font-bold truncate max-w-[120px]" :title="topic.name">{{ topic.name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="form.memberships.length === 0" class="py-4 text-center border-2 border-dashed border-slate-100 rounded-2xl text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        No Department Memberships (Client Role)
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-8 pt-4">
                                <div class="flex items-center gap-3">
                                    <button 
                                        type="button"
                                        @click="form.is_admin = !form.is_admin"
                                        :class="['w-12 h-6 rounded-full transition-colors relative', form.is_admin ? 'bg-csired' : 'bg-slate-200']"
                                    >
                                        <span :class="['absolute top-1 w-4 h-4 bg-white rounded-full transition-all', form.is_admin ? 'left-7' : 'left-1']"></span>
                                    </button>
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-widest">Admin Access</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button 
                                        type="button"
                                        @click="form.is_manager = !form.is_manager"
                                        :class="['w-12 h-6 rounded-full transition-colors relative', form.is_manager ? 'bg-csired' : 'bg-slate-200']"
                                    >
                                        <span :class="['absolute top-1 w-4 h-4 bg-white rounded-full transition-all', form.is_manager ? 'left-7' : 'left-1']"></span>
                                    </button>
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-widest">Manager Access</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button 
                                        type="button"
                                        @click="form.is_active = !form.is_active"
                                        :class="['w-12 h-6 rounded-full transition-colors relative', form.is_active ? 'bg-emerald-500' : 'bg-slate-200']"
                                    >
                                        <span :class="['absolute top-1 w-4 h-4 bg-white rounded-full transition-all', form.is_active ? 'left-7' : 'left-1']"></span>
                                    </button>
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-widest">Active Status</span>
                                </div>
                            </div>

                            <div class="pt-6 flex gap-3">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 inline-flex justify-center px-6 py-4 border border-transparent text-sm font-bold rounded-2xl text-white bg-csired hover:bg-[#D31920] shadow-lg shadow-red-100 focus:outline-none transition-all active:scale-95 disabled:opacity-50"
                                >
                                    Save Changes
                                </button>
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="flex-1 inline-flex justify-center px-6 py-4 border border-slate-100 text-sm font-bold rounded-2xl text-slate-600 bg-slate-50 hover:bg-slate-100 transition-all active:scale-95"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
