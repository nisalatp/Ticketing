<script setup>
import { ref, reactive, onMounted, onUnmounted, provide } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import ToastList from '@/Components/ToastList.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { 
    BellIcon, 
    MagnifyingGlassIcon,
    Bars3Icon,
    ChevronLeftIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    initialSidebarCollapsed: {
        type: Boolean,
        default: false
    }
});

const sidebarOpen = ref(false); // Mobile sidebar
const isSidebarCollapsed = ref(props.initialSidebarCollapsed); // Desktop compact sidebar

const notifications = reactive({
    unread: [],
    read: []
});

let fetchInterval = null;

const fetchNotifications = async () => {
    try {
        const response = await axios.get(route('notifications.index'));
        notifications.unread = response.data.unread;
        notifications.read = response.data.read;
    } catch (error) {
        console.error("Failed to fetch notifications:", error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post(route('notifications.mark-all-as-read'));
        notifications.read = [...notifications.unread, ...notifications.read].slice(0, 10);
        notifications.unread = [];
    } catch (error) {
        console.error("Failed to mark all as read:", error);
    }
};

const handleNotificationClick = async (notification) => {
    // If unread, mark it read on the server
    if (!notification.read_at) {
        try {
            await axios.post(route('notifications.mark-as-read', notification.id));
            notifications.unread = notifications.unread.filter(n => n.id !== notification.id);
            notification.read_at = new Date().toISOString();
            notifications.read.unshift(notification);
            if(notifications.read.length > 10) notifications.read.pop();
        } catch (error) {
            console.error("Failed to mark as read:", error);
        }
    }

    // Navigate to the ticket if it's related to one
    if (notification.data.ticket_id) {
        router.visit(route('tickets.show', notification.data.ticket_id));
    }
};

onMounted(() => {
    fetchNotifications();
    fetchInterval = setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
    if (fetchInterval) clearInterval(fetchInterval);
});
</script>

<template>
    <div class="flex h-screen bg-slate-50 overflow-hidden font-sans">
        <!-- Toast System -->
        <ToastList />

        <!-- Sidebar -->
        <Sidebar 
            class="hidden md:flex transition-all duration-300 ease-in-out" 
            :class="[isSidebarCollapsed ? 'w-20' : 'w-64']"
            :auth="$page.props.auth" 
            :collapsed="isSidebarCollapsed"
        />

        <!-- Mobile Sidebar (Slide-over) -->
        <Transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="sidebarOpen" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm md:hidden" @click="sidebarOpen = false"></div>
        </Transition>
        <Transition
            enter-active-class="transition ease-in-out duration-300 transform"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition ease-in-out duration-300 transform"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <Sidebar 
                v-if="sidebarOpen" 
                class="fixed inset-y-0 left-0 z-50 md:hidden w-72" 
                :auth="$page.props.auth" 
            />
        </Transition>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 shrink-0 z-40">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="md:hidden p-2 text-slate-500 hover:bg-slate-50 rounded-lg">
                        <Bars3Icon class="h-6 w-6" />
                    </button>
                    
                    <!-- Desktop Collapse Toggle -->
                    <button 
                        @click="isSidebarCollapsed = !isSidebarCollapsed" 
                        class="hidden md:flex p-2 text-slate-400 hover:text-csired hover:bg-red-50 rounded-full transition-all border border-transparent hover:border-red-100 shadow-sm bg-white"
                        :title="isSidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                    >
                        <ChevronLeftIcon v-if="!isSidebarCollapsed" class="h-4 w-4" />
                        <ChevronRightIcon v-else class="h-4 w-4" />
                    </button>

                    <div class="relative hidden sm:block">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                        <input 
                            type="text" 
                            placeholder="Search tickets..." 
                            class="pl-10 pr-4 py-1.5 bg-slate-50 border-none rounded-full text-sm w-64 focus:ring-2 focus:ring-csired/20 transition-all"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Dropdown align="right" width="96">
                        <template #trigger>
                            <button class="p-2 text-slate-500 hover:bg-slate-50 rounded-full relative transition-colors focus:ring-2 focus:ring-csired/20">
                                <BellIcon class="h-5 w-5" />
                                <span v-if="notifications.unread.length > 0" class="absolute top-1 right-1 flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 border-2 border-white"></span>
                                </span>
                            </button>
                        </template>

                        <template #content>
                            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-slate-900">Notifications</h3>
                                <button v-if="notifications.unread.length > 0" @click="markAllAsRead" class="text-[10px] font-bold text-csired hover:text-[#D31920] uppercase tracking-widest transition-colors">Mark all as read</button>
                            </div>
                            
                            <div class="max-h-96 overflow-y-auto">
                                <div v-if="notifications.unread.length === 0 && notifications.read.length === 0" class="p-6 text-center">
                                    <BellIcon class="mx-auto h-8 w-8 text-slate-300 mb-2" />
                                    <p class="text-sm text-slate-500 font-medium">You're all caught up!</p>
                                </div>

                                <div v-for="notification in notifications.unread" :key="notification.id" class="p-4 border-b border-slate-50 hover:bg-red-50/50 transition-colors relative group cursor-pointer" @click="handleNotificationClick(notification)">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                            <BellIcon class="w-4 h-4 text-csired" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-900 leading-tight mb-1">{{ notification.data.message }}</p>
                                            <p class="text-[10px] text-slate-500 font-medium uppercase tracking-widest">{{ new Date(notification.created_at).toLocaleString([], {hour: '2-digit', minute:'2-digit', month:'short', day:'numeric'}) }}</p>
                                        </div>
                                        <div class="w-2 h-2 bg-csired rounded-full mt-2 shrink-0"></div>
                                    </div>
                                </div>

                                <div v-for="notification in notifications.read" :key="notification.id" class="p-4 border-b border-slate-50 hover:bg-slate-50 transition-colors opacity-75 cursor-pointer" @click="handleNotificationClick(notification)">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                                            <BellIcon class="w-4 h-4 text-slate-500" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-700 leading-tight mb-1">{{ notification.data.message }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">{{ new Date(notification.created_at).toLocaleString([], {hour: '2-digit', minute:'2-digit', month:'short', day:'numeric'}) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Dropdown>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center gap-2 p-1 hover:bg-slate-50 rounded-lg transition-colors">
                                <div class="h-8 w-8 rounded-full bg-red-50 flex items-center justify-center text-csired font-bold border border-red-100 text-xs">
                                    {{ $page.props.auth.user.name.charAt(0) }}
                                </div>
                                <span class="text-sm font-medium text-slate-700 hidden sm:inline">{{ $page.props.auth.user.name }}</span>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Account Settings</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Sign Out</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto relative no-scrollbar">
                <div class="p-4 md:p-6 lg:px-6 w-full h-full">
                    <!-- Page Header (Slot) -->
                    <div v-if="$slots.header" class="mb-8">
                        <slot name="header" />
                    </div>

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
