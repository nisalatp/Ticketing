<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    tickets: Object,
    agents: Array,
    transferReasons: Array,
    filters: Object,
});

const activeStatusFilter = ref(props.filters.status || 'all');
const assigningTicket = ref(null);

const form = useForm({
    user_id: '',
    transfer_reason_id: '',
    note: '',
});

watch(activeStatusFilter, (newVal) => {
    router.get(route('admin.ticket-assignment.index'), { status: newVal }, {
        preserveState: true,
        replace: true,
    });
});

const openAssignModal = (ticket) => {
    assigningTicket.value = ticket;
    form.reset();
    form.clearErrors();
};

const closeAssignModal = () => {
    assigningTicket.value = null;
    form.reset();
};

const submitAssignment = () => {
    form.put(route('admin.ticket-assignment.assign', assigningTicket.value.id), {
        onSuccess: () => {
            closeAssignModal();
        },
    });
};
</script>

<template>
    <Head title="Ticket Assignment" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Ticket Assignment Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 border-l-4 border-csired">
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-slate-900">Manage Ticket Flow</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                View and manually override ticket assignments for any department or category.
                            </p>
                        </div>
                        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                            <select
                                v-model="activeStatusFilter"
                                class="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-csired sm:text-sm sm:leading-6"
                            >
                                <option value="all">All Tickets</option>
                                <option value="unassigned">Unassigned Only</option>
                                <option value="New">Status: New</option>
                                <option value="Assigned">Status: Assigned</option>
                                <option value="In Progress">Status: In Progress</option>
                                <option value="Waiting">Status: Waiting</option>
                                <option value="Resolved">Status: Resolved</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tickets Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-300">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">ID / Priority</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Subject</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Department / Topic</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Requester</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Current Assignee</th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-slate-900">Status</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 whitespace-nowrap text-right text-sm font-medium">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr v-for="ticket in tickets.data" :key="ticket.id" class="transition hover:bg-slate-50">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-slate-900 sm:pl-6">
                                        {{ ticket.ticket_no }}
                                        <div class="text-xs mt-1" :class="{
                                            'text-red-600': ticket.priority === 'Urgent',
                                            'text-orange-600': ticket.priority === 'High',
                                            'text-yellow-600': ticket.priority === 'Medium',
                                            'text-green-600': ticket.priority === 'Low'
                                        }">
                                            {{ ticket.priority }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-3 text-sm text-slate-500">
                                        <div class="font-medium text-slate-900">{{ ticket.subject }}</div>
                                        <div class="text-xs line-clamp-1 mt-1">{{ ticket.description }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                                        {{ ticket.department?.name }}
                                        <div class="text-xs text-slate-400 mt-1">{{ ticket.topic?.name || 'General' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                                        <div class="flex items-center gap-2">
                                            <div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                                {{ ticket.is_anonymous ? 'A' : ticket.requester?.name.charAt(0) }}
                                            </div>
                                            {{ ticket.is_anonymous ? 'Anonymous' : ticket.requester?.name }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span v-if="ticket.assignee" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                            {{ ticket.assignee.name }}
                                        </span>
                                        <span v-else class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                            Unassigned
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border"
                                            :class="{
                                                'bg-yellow-50 text-yellow-800 border-yellow-200': ticket.status === 'New',
                                                'bg-blue-50 text-blue-800 border-blue-200': ticket.status === 'Assigned',
                                                'bg-purple-50 text-purple-800 border-purple-200': ticket.status === 'In Progress',
                                                'bg-orange-50 text-orange-800 border-orange-200': ticket.status === 'Waiting',
                                                'bg-green-50 text-green-800 border-green-200': ticket.status === 'Resolved'
                                            }"
                                        >
                                            {{ ticket.status }}
                                        </span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <button @click="openAssignModal(ticket)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition">
                                            Assign
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="tickets.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-slate-900">No tickets found</h3>
                                            <p class="mt-1 text-sm text-slate-500">No tickets match the current filter criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <!-- Simple pagination implementation for time, consider using Laravel's standard Tailwind paginator -->
                    <div class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between" v-if="tickets.links && tickets.links.length > 3">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link :href="tickets.prev_page_url" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" :class="{'opacity-50 cursor-not-allowed pointer-events-none': !tickets.prev_page_url}">Previous</Link>
                            <Link :href="tickets.next_page_url" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" :class="{'opacity-50 cursor-not-allowed pointer-events-none': !tickets.next_page_url}">Next</Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span class="font-medium">{{ tickets.from }}</span> to <span class="font-medium">{{ tickets.to }}</span> of <span class="font-medium">{{ tickets.total }}</span> results
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <Link v-for="(link, index) in tickets.links" :key="index" :href="link.url" 
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-20 focus:outline-offset-0"
                                        :class="[
                                            link.active ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0',
                                            !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : '',
                                            index === 0 ? 'rounded-l-md' : '',
                                            index === tickets.links.length - 1 ? 'rounded-r-md' : '',
                                        ]"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Modal -->
        <Modal :show="assigningTicket !== null" @close="closeAssignModal" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-medium text-slate-900 mb-4">
                    Assign Ticket {{ assigningTicket?.ticket_no }}
                </h2>
                
                <form @submit.prevent="submitAssignment">
                    <div class="mb-4">
                        <InputLabel for="user_id" value="Select Agent" />
                        <SelectInput
                            id="user_id"
                            v-model="form.user_id"
                            class="mt-1 block w-full"
                            required
                        >
                            <option value="" disabled>Choose an agent...</option>
                            <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                                {{ agent.name }} ({{ agent.email }})
                            </option>
                        </SelectInput>
                        <InputError :message="form.errors.user_id" class="mt-2" />
                    </div>

                    <div class="mb-4" v-if="assigningTicket && assigningTicket.assignee && assigningTicket.assignee.id !== form.user_id && form.user_id">
                        <InputLabel for="transfer_reason_id" value="Transfer Reason" />
                        <SelectInput
                            id="transfer_reason_id"
                            v-model="form.transfer_reason_id"
                            class="mt-1 block w-full"
                            required
                        >
                            <option value="" disabled>Select reason for handoff...</option>
                            <option v-for="reason in transferReasons" :key="reason.id" :value="reason.id">
                                {{ reason.name }}
                            </option>
                        </SelectInput>
                        <InputError :message="form.errors.transfer_reason_id" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <InputLabel for="note" value="Assignment Note (Optional - Internal Only)" />
                        <TextInput
                            id="note"
                            v-model="form.note"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Reason for manual assignment..."
                        />
                        <InputError :message="form.errors.note" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                        <SecondaryButton @click="closeAssignModal">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Assign Ticket
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
