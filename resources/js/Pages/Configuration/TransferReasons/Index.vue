<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { Cog6ToothIcon, PlusIcon, PencilSquareIcon, TrashIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    reasons: Array,
});

const isModalOpen = ref(false);
const editingReason = ref(null);
const reasonToDelete = ref(null);

const form = useForm({
    name: '',
    is_active: true,
});

const openCreateModal = () => {
    editingReason.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (reason) => {
    editingReason.value = reason;
    form.name = reason.name;
    form.is_active = reason.is_active;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        form.reset();
        form.clearErrors();
        editingReason.value = null;
    }, 200);
};

const submitForm = () => {
    if (editingReason.value) {
        form.put(route('configuration.transfer-reasons.update', editingReason.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('configuration.transfer-reasons.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const openDeleteModal = (reason) => {
    reasonToDelete.value = reason;
};

const confirmDeletion = () => {
    if (reasonToDelete.value) {
        useForm({}).delete(route('configuration.transfer-reasons.destroy', reasonToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                reasonToDelete.value = null;
            }
        });
    }
};
</script>

<template>
    <Head title="Transfer Reasons" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                    <ArrowRightOnRectangleIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Transfer Reasons</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-tight">Configure standardized reasons for ticket handoffs between agents.</p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                
                <div class="w-full text-right mb-4">
                    <button
                        @click="openCreateModal"
                        type="button"
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-xs font-bold rounded-xl text-white bg-csired hover:bg-[#D31920] shadow-lg shadow-red-100 transition-all active:scale-95 uppercase tracking-widest"
                    >
                        <PlusIcon class="w-4 h-4 mr-2" />
                        New Reason
                    </button>
                </div>

                <div class="bg-white shadow-xl sm:rounded-[1rem] border border-slate-100 overflow-hidden">
                    <div class="min-w-full overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-widest">Reason</th>
                                    <th scope="col" class="px-3 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                    <th scope="col" class="relative py-4 pl-3 pr-6 text-right">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr v-for="reason in reasons" :key="reason.id" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="whitespace-nowrap py-4 pl-6 pr-3 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600">
                                            <ArrowRightOnRectangleIcon class="w-4 h-4" />
                                        </div>
                                        <div class="text-sm font-bold text-slate-900">{{ reason.name }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-center">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                            :class="reason.is_active ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-slate-50 text-slate-600 ring-slate-500/10'">
                                            {{ reason.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openEditModal(reason)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Edit">
                                                <PencilSquareIcon class="h-5 w-5" />
                                            </button>
                                            <button @click="openDeleteModal(reason)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete">
                                                <TrashIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="reasons.length === 0">
                                    <td colspan="3" class="py-12 text-center text-sm font-medium text-slate-500">
                                        No transfer reasons configured.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-6">
                    {{ editingReason ? 'Edit Transfer Reason' : 'Create Transfer Reason' }}
                </h2>
                
                <form @submit.prevent="submitForm" class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Reason Title / Description" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="e.g. Escalate to Level 2"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="block">
                        <label class="flex items-center">
                            <Checkbox name="is_active" v-model:checked="form.is_active" />
                            <span class="ml-2 text-sm text-slate-600 font-medium">Active (Available for selection)</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <SecondaryButton type="button" @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Save Reason
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation -->
        <ConfirmationModal :show="reasonToDelete !== null" @close="reasonToDelete = null">
            <template #title>Delete Transfer Reason</template>
            <template #content>
                Are you sure you want to delete this transfer reason? This action heavily affects historic analytics and cannot be undone.
            </template>
            <template #footer>
                <SecondaryButton @click="reasonToDelete = null">Cancel</SecondaryButton>
                <DangerButton class="ml-3" @click="confirmDeletion">Delete</DangerButton>
            </template>
        </ConfirmationModal>

    </AuthenticatedLayout>
</template>
