<script setup>
import { Link } from '@inertiajs/vue3';
import { 
    HomeIcon, 
    TicketIcon, 
    InboxIcon, 
    PlusIcon, 
    Cog6ToothIcon, 
    ChartBarIcon,
    UsersIcon,
    TableCellsIcon,
    InboxStackIcon,
    ArrowRightOnRectangleIcon,
    ArchiveBoxIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    auth: Object,
    collapsed: {
        type: Boolean,
        default: false
    }
});

const navigation = [
    { name: 'Dashboard', href: route('dashboard'), icon: HomeIcon, current: route().current('dashboard') },
    { name: 'My Tickets', href: route('tickets.index'), icon: TicketIcon, current: route().current('tickets.index') },
    { name: 'Create Ticket', href: route('tickets.create'), icon: PlusIcon, current: route().current('tickets.create') },
];

const agentNavigation = [
    { name: 'My Queue', href: route('agent.dashboard'), icon: InboxIcon, current: route().current('agent.dashboard') },
    { name: 'Workload', href: route('agent.workload'), icon: ChartBarIcon, current: route().current('agent.workload') },
    { name: 'Previous Tickets', href: route('agent.previous-tickets'), icon: ArchiveBoxIcon, current: route().current('agent.previous-tickets') },
];

const adminManagementNavigation = [
    { name: 'Ticket Management', href: route('admin.tickets.index'), icon: InboxStackIcon, current: route().current('admin.tickets.*') },
    { name: 'Agent Performance', href: route('admin.agent-performance.index'), icon: ChartBarIcon, current: route().current('admin.agent-performance.*') },
];

const adminConfigNavigation = [
    { name: 'Departments', href: route('configuration.departments.index'), icon: TableCellsIcon, current: route().current('configuration.departments.*') },
    { name: 'Ticket Types', href: route('configuration.ticket-types.index'), icon: TicketIcon, current: route().current('configuration.ticket-types.*') },
    { name: 'SLA Policies', href: route('configuration.sla-policies.index'), icon: Cog6ToothIcon, current: route().current('configuration.sla-policies.*') },
    { name: 'Transfer Reasons', href: route('configuration.transfer-reasons.index'), icon: ArrowRightOnRectangleIcon, current: route().current('configuration.transfer-reasons.*') },
    { name: 'User Management', href: route('configuration.users.index'), icon: UsersIcon, current: route().current('configuration.users.*') },
];
</script>

<template>
    <div class="flex h-full flex-col bg-white border-r border-slate-200 transition-all duration-300 overflow-hidden" :class="[collapsed ? 'w-20' : 'w-64']">
        <!-- Logo Area -->
        <div class="flex h-16 shrink-0 items-center border-b border-slate-100 transition-all duration-300" :class="[collapsed ? 'px-0 justify-center' : 'px-6']">
            <Link :href="route('dashboard')" class="flex items-center gap-2">
                <div class="w-10 h-10 bg-csidark rounded-xl flex items-center justify-center shadow-lg shadow-blue-900/20 transform transition-transform hover:scale-105">
                    <span class="text-white font-bold text-xl">I</span>
                </div>
                <span v-if="!collapsed" class="font-bold text-csidark tracking-tighter text-lg">IIMT Campus</span>
            </Link>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-8 no-scrollbar">
            <!-- Employee Section -->
            <div>
                <h3 v-if="!collapsed" class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Portal</h3>
                <div class="space-y-1.5" :class="{ 'flex flex-col items-center': collapsed }">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        :title="collapsed ? item.name : ''"
                        :class="[
                            item.current 
                                ? 'bg-red-50 text-csired shadow-sm' 
                                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900',
                            'group flex items-center transition-all duration-200 rounded-xl',
                            collapsed ? 'p-3 justify-center' : 'p-3 text-sm font-bold'
                        ]"
                    >
                        <component :is="item.icon" :class="[collapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5', 'shrink-0 transition-colors']" />
                        <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                    </Link>
                </div>
            </div>

            <!-- Agent Section -->
            <div v-if="$page.props.auth.user.is_agent || $page.props.auth.user.is_admin">
                <h3 v-if="!collapsed" class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Agent Workspace</h3>
                <div class="space-y-1.5" :class="{ 'flex flex-col items-center': collapsed }">
                    <Link
                        v-for="item in agentNavigation"
                        :key="item.name"
                        :href="item.href"
                        :title="collapsed ? item.name : ''"
                        :class="[
                            item.current 
                                ? 'bg-red-50 text-csired shadow-sm' 
                                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900',
                            'group flex items-center transition-all duration-200 rounded-xl',
                            collapsed ? 'p-3 justify-center' : 'p-3 text-sm font-bold'
                        ]"
                    >
                        <component :is="item.icon" :class="[collapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5', 'shrink-0 transition-colors']" />
                        <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                    </Link>
                </div>
            </div>

            <!-- Admin Section -->
            <div v-if="$page.props.auth.user.is_admin || $page.props.auth.user.is_manager" class="space-y-8">
                <div>
                    <h3 v-if="!collapsed" class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Management</h3>
                    <div class="space-y-1.5" :class="{ 'flex flex-col items-center': collapsed }">
                        <Link
                            v-for="item in adminManagementNavigation"
                            :key="item.name"
                            :href="item.href"
                            :title="collapsed ? item.name : ''"
                            :class="[
                                item.current 
                                    ? 'bg-red-50 text-csired shadow-sm' 
                                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900',
                                'group flex items-center transition-all duration-200 rounded-xl',
                                collapsed ? 'p-3 justify-center' : 'p-3 text-sm font-bold'
                            ]"
                        >
                            <component :is="item.icon" :class="[collapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5', 'shrink-0 transition-colors']" />
                            <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                        </Link>
                    </div>
                </div>

                <div>
                    <h3 v-if="!collapsed" class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Organization</h3>
                    <div class="space-y-1.5" :class="{ 'flex flex-col items-center': collapsed }">
                        <Link
                            v-for="item in adminConfigNavigation"
                            :key="item.name"
                            :href="item.href"
                            :title="collapsed ? item.name : ''"
                            :class="[
                                item.current 
                                    ? 'bg-red-50 text-csired shadow-sm' 
                                    : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900',
                                'group flex items-center transition-all duration-200 rounded-xl',
                                collapsed ? 'p-3 justify-center' : 'p-3 text-sm font-bold'
                            ]"
                        >
                            <component :is="item.icon" :class="[collapsed ? 'h-6 w-6' : 'mr-3 h-5 w-5', 'shrink-0 transition-colors']" />
                            <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <div class="border-t border-slate-100 p-4 transition-all duration-300" :class="[collapsed ? 'px-0' : 'p-4']">
            <div class="flex items-center gap-3" :class="{ 'justify-center': collapsed }">
                <div v-if="$page.props.auth.user.profile_photo_path" class="h-9 w-9 rounded-xl overflow-hidden border-2 border-slate-100 shadow-sm shrink-0">
                    <img :src="'/storage/' + $page.props.auth.user.profile_photo_path" class="w-full h-full object-cover" @error="$event.target.style.display = 'none'; $event.target.parentElement.innerHTML = '<div class=\'w-full h-full bg-slate-100 flex items-center justify-center text-slate-600 font-black text-xs border-2 border-slate-200 shrink-0\'>' + $page.props.auth.user.name.charAt(0) + '</div>'" />
                </div>
                <div v-else class="h-9 w-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-black border-2 border-slate-200 shrink-0 shadow-sm">
                    {{ $page.props.auth.user.name.charAt(0) }}
                </div>
                <div v-if="!collapsed" class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-csidark truncate uppercase tracking-wider">{{ $page.props.auth.user.name }}</p>
                    <p class="text-[10px] text-slate-500 truncate font-semibold uppercase tracking-widest">{{ $page.props.auth.user.job_title || 'Employee' }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
