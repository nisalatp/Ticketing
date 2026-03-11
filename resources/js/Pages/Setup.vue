<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';

const form = useForm({
    database: 'thinugic_csi_sup',
    username: 'thinugic_csi_sup',
    password: 'KANdy!23',
    host: '127.0.0.1',
    app_url: '',
});

onMounted(() => {
    form.app_url = window.location.origin;
});

const status = ref('idle'); // idle, testing, configured, checking_db, data_detected, initializing, done
const message = ref('');
const error = ref('');
const progress = ref(0);
const dbState = ref({ table_count: 0, has_data: false });

const handleConfigure = async () => {
    status.value = 'testing';
    error.value = '';
    
    try {
        const response = await axios.post(route('setup.configure'), form.data());
        status.value = 'configured';
        message.value = response.data.message;
        progress.value = 25;
    } catch (e) {
        status.value = 'idle';
        error.value = e.response?.data?.error || 'Failed to update configuration.';
    }
};

const checkDatabaseState = async () => {
    status.value = 'checking_db';
    error.value = '';
    
    try {
        const response = await axios.post(route('setup.check-state'));
        dbState.value = response.data;
        
        if (dbState.value.has_data) {
            status.value = 'data_detected';
            progress.value = 50;
        } else {
            handleInitialize();
        }
    } catch (e) {
        status.value = 'configured';
        error.value = e.response?.data?.error || 'Failed to check database state.';
    }
};

const handleBackupAndInitialize = async () => {
    message.value = 'Creating backup...';
    try {
        const response = await axios.post(route('setup.backup'));
        if (response.data.download_url) {
            window.location.assign(response.data.download_url);
        }
        setTimeout(() => {
            handleInitialize();
        }, 3000);
    } catch (e) {
        error.value = 'Backup failed: ' + (e.response?.data?.error || 'Unknown error');
    }
};

const handleInitialize = async () => {
    status.value = 'initializing';
    error.value = '';
    progress.value = 75;
    
    try {
        const response = await axios.post(route('setup.initialize'));
        progress.value = 100;
        status.value = 'done';
        message.value = response.data.message;
        
        setTimeout(() => {
            window.location.href = '/dashboard';
        }, 3000);
    } catch (e) {
        status.value = 'configured';
        error.value = e.response?.data?.error || 'Failed to initialize database.';
    }
};
</script>

<template>
    <Head title="Database Setup" />
    
    <div class="min-h-screen bg-[#f8fafc] flex items-center justify-center p-6 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-red-50 via-white to-blue-50">
        <div class="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            
            <!-- Left Panel: Branding & Info -->
            <div class="space-y-8">
                <div class="w-16 h-16 bg-slate-900 rounded-[24px] flex items-center justify-center shadow-2xl shadow-slate-200">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 7v10c0 1.1.9 2 2 2h12a2 2 0 002-2V7M4 7a2 2 0 012-2h12a2 2 0 012 2M4 7l8 5 8-5" />
                    </svg>
                </div>
                
                <div class="space-y-4">
                    <h1 class="text-5xl font-black text-gray-900 tracking-tight leading-none">
                        Database <br/>
                        <span class="text-slate-900">Setup Utility</span>
                    </h1>
                    <p class="text-lg text-gray-500 font-medium leading-relaxed max-w-sm">
                        Configure database credentials and initialize the Ticketing system schema.
                    </p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-white/50 backdrop-blur-sm rounded-[24px] border border-white/50 shadow-sm">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Current Task</p>
                        <p class="text-sm font-black text-gray-900 capitalize">{{ status.replace('_', ' ') }}</p>
                    </div>
                    <div class="p-4 bg-white/50 backdrop-blur-sm rounded-[24px] border border-white/50 shadow-sm">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Mode</p>
                        <p class="text-sm font-black text-gray-900">Laravel Migrations</p>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel: Form/Progress -->
            <div class="bg-white/80 backdrop-blur-xl p-8 lg:p-10 rounded-[48px] border border-white shadow-2xl shadow-gray-200/50">
                
                <!-- Step 1: Configuration -->
                <div v-if="['idle', 'testing', 'configured'].includes(status)" class="space-y-8">
                    <div v-if="['idle', 'testing'].includes(status)" class="space-y-8">
                        <div class="grid grid-cols-1 gap-6">
                            <div class="group">
                                <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2 block px-1">Application URL</label>
                                <input v-model="form.app_url" type="text" class="w-full bg-gray-50 border-none rounded-[20px] px-6 py-4 text-sm font-bold text-gray-900 focus:ring-4 focus:ring-csired/10 transition-all" />
                            </div>
                            <div class="group">
                                <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2 block px-1">Database Host</label>
                                <input v-model="form.host" type="text" class="w-full bg-gray-50 border-none rounded-[20px] px-6 py-4 text-sm font-bold text-gray-900 focus:ring-4 focus:ring-csired/10 transition-all" />
                            </div>
                            <div class="group">
                                <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2 block px-1">Database Name</label>
                                <input v-model="form.database" type="text" class="w-full bg-gray-50 border-none rounded-[20px] px-6 py-4 text-sm font-bold text-gray-900 focus:ring-4 focus:ring-csired/10 transition-all" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="group">
                                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2 block px-1">Username</label>
                                    <input v-model="form.username" type="text" class="w-full bg-gray-50 border-none rounded-[20px] px-6 py-4 text-sm font-bold text-gray-900 focus:ring-4 focus:ring-csired/10 transition-all" />
                                </div>
                                <div class="group">
                                    <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2 block px-1">Password</label>
                                    <input v-model="form.password" type="password" class="w-full bg-gray-50 border-none rounded-[20px] px-6 py-4 text-sm font-bold text-gray-900 focus:ring-4 focus:ring-csired/10 transition-all" />
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="error" class="p-4 bg-red-50 rounded-[20px] border border-red-100">
                            <p class="text-xs font-black text-red-600 leading-tight">Error: {{ error }}</p>
                        </div>

                        <button @click="handleConfigure" :disabled="status === 'testing'" class="w-full py-5 bg-gray-900 hover:bg-black text-white rounded-[24px] font-black text-sm tracking-widest uppercase transition-all shadow-xl shadow-gray-200">
                            {{ status === 'testing' ? 'Testing Connection...' : 'Save & Test Connection' }}
                        </button>
                    </div>
                    
                    <div v-if="status === 'configured'" class="animate-in slide-in-from-bottom duration-500">
                        <div class="p-6 bg-green-50 rounded-[32px] border border-green-100 flex flex-col items-center text-center space-y-4">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg shadow-green-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-xs font-bold text-green-700">Credentials verified. Let's inspect the database structure before proceeding.</p>
                            <button @click="checkDatabaseState" class="w-full py-4 bg-green-600 hover:bg-green-700 text-white rounded-[20px] font-black text-xs tracking-widest uppercase">
                                Check Database Structure
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Data Detected Warning -->
                <div v-else-if="status === 'data_detected'" class="space-y-8 text-center py-4">
                    <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center text-amber-600 mx-auto shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-2xl font-black text-gray-900">Database has data!</h3>
                        <p class="text-sm text-gray-500 font-medium">
                            Found <b>{{ dbState.table_count }}</b> tables. Proceeding will <span class="text-red-600 font-bold uppercase tracking-tight">DROP ALL TABLES</span> and recreate them.
                        </p>
                    </div>

                    <div v-if="error" class="p-4 bg-red-50 rounded-[20px] text-xs font-black text-red-600">
                        {{ error }}
                    </div>

                    <div class="space-y-3 pt-4">
                        <button @click="handleBackupAndInitialize" class="w-full py-5 bg-blue-600 hover:bg-blue-700 text-white rounded-[24px] font-black text-sm tracking-widest uppercase transition-all shadow-xl shadow-blue-100">
                            Backup & Re-Initialize
                        </button>
                        <button @click="handleInitialize" class="w-full py-4 bg-gray-100 hover:bg-gray-200 text-gray-900 rounded-[24px] font-black text-xs tracking-widest uppercase transition-all">
                            Skip Backup & Just Re-Initialize
                        </button>
                    </div>
                </div>

                <!-- Step 3: Initializing/Done -->
                <div v-else class="h-[450px] flex flex-col items-center justify-center space-y-10">
                    <div class="relative w-40 h-40 flex items-center justify-center">
                        <svg class="absolute inset-0 w-full h-full transform -rotate-90">
                            <circle cx="80" cy="80" r="75" fill="none" class="stroke-gray-50" stroke-width="10" />
                            <circle cx="80" cy="80" r="75" fill="none" class="stroke-csired transition-all duration-1000 ease-out" stroke-width="10" stroke-dasharray="471" :stroke-dashoffset="471 - (471 * progress / 100)" stroke-linecap="round" />
                        </svg>
                        <span class="text-3xl font-black text-gray-900 tracking-tighter">{{ progress }}%</span>
                    </div>
                    
                    <div class="text-center space-y-3">
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ status === 'done' ? 'System Fully Live!' : 'Running Migrations...' }}</h3>
                        <p class="text-sm text-gray-500 font-medium px-4">{{ message || 'Executing Laravel migrations and creating tables...' }}</p>
                    </div>

                    <div v-if="status === 'done'" class="animate-bounce">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white shadow-xl shadow-green-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
input::placeholder {
    color: #cbd5e1;
    font-weight: 700;
}
.transition-all {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
