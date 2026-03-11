<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Cog6ToothIcon, PlusIcon, PencilSquareIcon, TrashIcon, XMarkIcon, ClockIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    policies: Array,
    departments: Array,
    topics: Array,
});

const isModalOpen = ref(false);
const editingPolicy = ref(null);
const policyToDelete = ref(null);

const form = useForm({
    name: '',
    response_minutes: 60,
    resolution_minutes: 240,
    is_active: true,
    scopes: [
        { department_id: '', topic_ids: [], priority: '' }
    ]
});

const openCreateModal = () => {
    editingPolicy.value = null;
    form.reset();
    form.scopes = [{ department_id: '', topic_ids: [], priority: '' }];
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (policy) => {
    editingPolicy.value = policy;
    form.name = policy.name;
    form.response_minutes = policy.response_minutes;
    form.resolution_minutes = policy.resolution_minutes;
    form.is_active = policy.is_active;

    if (policy.scopes && policy.scopes.length > 0) {
        const groupedScopes = [];
        policy.scopes.forEach(s => {
            let existing = groupedScopes.find(g => g.department_id === (s.department_id || '') && g.priority === (s.priority || ''));
            if (existing) {
                if (s.topic_id && !existing.topic_ids.includes(s.topic_id)) {
                    existing.topic_ids.push(s.topic_id);
                }
            } else {
                groupedScopes.push({
                    department_id: s.department_id || '',
                    topic_ids: s.topic_id ? [s.topic_id] : [],
                    priority: s.priority || ''
                });
            }
        });
        form.scopes = groupedScopes;
    } else {
        form.scopes = [{ department_id: '', topic_ids: [], priority: '' }];
    }

    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        form.reset();
        form.clearErrors();
        editingPolicy.value = null;
    }, 200);
};

const submitForm = () => {
    const params = {
        name: form.name,
        response_minutes: form.response_minutes,
        resolution_minutes: form.resolution_minutes,
        is_active: form.is_active,
        scopes: form.scopes.map(s => ({
            department_id: s.department_id === '' ? null : s.department_id,
            topic_ids: s.topic_ids && s.topic_ids.length > 0 ? (s.topic_ids.includes('') ? [] : s.topic_ids) : [],
            priority: s.priority === '' ? null : s.priority
        }))
    };

    if (editingPolicy.value) {
        router.put(route('configuration.sla-policies.update', editingPolicy.value.id), params, {
            onSuccess: () => closeModal(),
        });
    } else {
        router.post(route('configuration.sla-policies.store'), params, {
            onSuccess: () => closeModal(),
        });
    }
};

const openDeleteModal = (policy) => {
    policyToDelete.value = policy;
};

const confirmDeletion = () => {
    if (policyToDelete.value) {
        useForm({}).delete(route('configuration.sla-policies.destroy', policyToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                policyToDelete.value = null;
            }
        });
    }
};

const addScope = () => {
    form.scopes.push({ department_id: '', topic_ids: [], priority: '' });
};

const removeScope = (index) => {
    form.scopes.splice(index, 1);
};

const priorities = ['Low', 'Medium', 'High', 'Urgent'];

const formatTime = (minutes) => {
    if (minutes < 60) return `${minutes}m`;
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
};
</script>

<template>
    <Head title="SLA Policies" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                        <ClockIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 tracking-tight">SLA Policies</h1>
                        <p class="text-xs text-slate-500 font-medium tracking-tight">Standardize response and resolution targets based on ticket classification.</p>
                    </div>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 rounded-md bg-csired px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-csidark transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-csired"
                >
                    <PlusIcon class="-ml-0.5 h-5 w-5" aria-hidden="true" />
                    New Policy
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-300">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">Name</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Applied Scopes (Dept &rarr; Topic &rarr; Priority)</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Target Response</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Target Resolution</th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-slate-900">Status</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr v-for="policy in policies" :key="policy.id" class="transition hover:bg-slate-50">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-slate-900 sm:pl-6">
                                        {{ policy.name }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                                        <div class="flex flex-col gap-1.5">
                                            <div v-for="(scope, i) in policy.scopes" :key="i" class="flex items-center gap-1.5 text-xs">
                                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                    {{ scope.department ? scope.department.name : 'Global' }}
                                                </span>
                                                <span class="text-slate-300">&rarr;</span>
                                                <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
                                                    {{ scope.topic ? scope.topic.name : 'Any Topic' }}
                                                </span>
                                                <span class="text-slate-300">&rarr;</span>
                                                <span class="inline-flex items-center rounded-md px-2 py-0.5 font-medium ring-1 ring-inset"
                                                    :class="{
                                                        'bg-red-50 text-red-700 ring-red-600/10': scope.priority === 'Urgent',
                                                        'bg-orange-50 text-orange-700 ring-orange-600/10': scope.priority === 'High',
                                                        'bg-yellow-50 text-yellow-800 ring-yellow-600/20': scope.priority === 'Medium',
                                                        'bg-green-50 text-green-700 ring-green-600/10': scope.priority === 'Low',
                                                        'bg-slate-50 text-slate-600 ring-slate-500/10': !scope.priority
                                                    }">
                                                    {{ scope.priority || 'Any Priority' }}
                                                </span>
                                            </div>
                                            <div v-if="policy.scopes.length === 0" class="text-xs text-slate-400 italic">No scopes defined</div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 font-medium">
                                        {{ formatTime(policy.response_minutes) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 font-medium">
                                        {{ formatTime(policy.resolution_minutes) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                        <span v-if="policy.is_active" class="inline-flex h-2 w-2 rounded-full bg-emerald-500" title="Active"></span>
                                        <span v-else class="inline-flex h-2 w-2 rounded-full bg-slate-300" title="Inactive"></span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex items-center justify-end gap-3">
                                            <button @click="openEditModal(policy)" class="text-indigo-600 hover:text-indigo-900 transition" title="Edit Policy">
                                                <PencilSquareIcon class="h-5 w-5" />
                                            </button>
                                            <button @click="openDeleteModal(policy)" class="text-red-400 hover:text-red-600 transition" title="Delete Policy">
                                                <TrashIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="policies.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                        <Cog6ToothIcon class="mx-auto h-12 w-12 text-slate-300" />
                                        <h3 class="mt-2 text-sm font-medium text-slate-900">No SLA Policies</h3>
                                        <p class="mt-1 text-sm text-slate-500">Get started by creating a new policy.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="isModalOpen" @close="closeModal" maxWidth="3xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-slate-900 mb-6">
                    {{ editingPolicy ? 'Edit SLA Policy' : 'Create SLA Policy' }}
                </h2>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Policy Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                            placeholder="e.g. Bronze Support, IT Urgent"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center px-1">
                            <InputLabel value="SLA Scopes (Rule Matrix)" />
                            <button type="button" @click="addScope" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                <PlusIcon class="w-3 h-3" /> Add Scope
                            </button>
                        </div>
                        
                        <div v-for="(scope, index) in form.scopes" :key="index" class="p-4 bg-slate-50 border border-slate-200 rounded-xl relative group">
                            <button v-if="form.scopes.length > 1" type="button" @click="removeScope(index)" class="absolute -top-2 -right-2 bg-white rounded-full p-1 text-slate-400 hover:text-red-600 shadow-sm border border-slate-100 opacity-0 group-hover:opacity-100 transition-opacity">
                                <XMarkIcon class="w-4 h-4" />
                            </button>
                            
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <InputLabel :for="'dept_'+index" value="Department" class="text-[10px]" />
                                    <SelectInput :id="'dept_'+index" v-model="scope.department_id" class="mt-1 block w-full py-1.5 text-xs h-9">
                                        <option value="">Global (All)</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                    </SelectInput>
                                </div>
                                <div>
                                    <InputLabel :for="'topic_'+index" value="Topic(s) - Cmd/Ctrl click multiple" class="text-[10px]" />
                                    <SelectInput :id="'topic_'+index" v-model="scope.topic_ids" multiple class="mt-1 block w-full py-1.5 text-xs h-20 overflow-y-auto">
                                        <option value="">Any (All Topics)</option>
                                        <option v-for="topic in topics" :key="topic.id" :value="topic.id">{{ topic.name }}</option>
                                    </SelectInput>
                                </div>
                                <div>
                                    <InputLabel :for="'pri_'+index" value="Priority" class="text-[10px]" />
                                    <SelectInput :id="'pri_'+index" v-model="scope.priority" class="mt-1 block w-full py-1.5 text-xs h-9">
                                        <option value="">Any Priority</option>
                                        <option v-for="p in priorities" :key="p" :value="p">{{ p }}</option>
                                    </SelectInput>
                                </div>
                            </div>
                        </div>
                        <InputError :message="form.errors.scopes" class="mt-1" />
                    </div>
                    <p class="mt-1 text-[10px] text-slate-500">The engine matches the most specific scope rule first.</p>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="response_minutes" value="First Response Time (Mins)" />
                            <TextInput
                                id="response_minutes"
                                v-model.number="form.response_minutes"
                                type="number"
                                min="0"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.response_minutes" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="resolution_minutes" value="Total Resolution Time (Mins)" />
                            <TextInput
                                id="resolution_minutes"
                                v-model.number="form.resolution_minutes"
                                type="number"
                                min="0"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.resolution_minutes" class="mt-2" />
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">Times should be defined in total minutes. (e.g., 2 hours = 120 minutes)</p>

                    <div class="pt-2">
                        <label class="flex items-center">
                            <Checkbox name="is_active" v-model:checked="form.is_active" />
                            <span class="ml-2 text-sm text-slate-600">Policy is active</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ editingPolicy ? 'Save Changes' : 'Create Policy' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="policyToDelete !== null"
            title="Delete SLA Policy"
            message="Are you sure you want to delete this SLA Policy? Active tickets currently using this policy will not be re-calculated automatically."
            confirmText="Delete Policy"
            :processing="false"
            @close="policyToDelete = null"
            @confirm="confirmDeletion"
        />

    </AuthenticatedLayout>
</template>
