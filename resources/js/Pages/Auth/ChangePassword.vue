<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { LockClosedIcon, ShieldCheckIcon, KeyIcon } from '@heroicons/vue/24/outline';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.change'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Change Password" />

    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6 relative overflow-hidden font-sans">
        <!-- Subtle Background Decorations -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-csired to-transparent opacity-20"></div>
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-red-50 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-50 rounded-full blur-3xl opacity-50"></div>

        <div class="max-w-md w-full">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-white overflow-hidden p-10 space-y-8">
                
                <!-- Header -->
                <div class="text-center space-y-2">
                    <div class="mx-auto w-16 h-16 bg-csired rounded-2xl flex items-center justify-center shadow-lg shadow-red-100 mb-6">
                        <LockClosedIcon class="w-8 h-8 text-white" />
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Security Update Required</h1>
                    <p class="text-sm text-slate-500">For your protection, please update your temporary password before proceeding to the dashboard.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Current Password -->
                    <div class="space-y-1.5 text-left">
                        <label for="current_password" class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest ml-1">Temporary Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-csired transition-colors">
                                <KeyIcon class="h-5 w-5" />
                            </div>
                            <input 
                                id="current_password"
                                type="password" 
                                v-model="form.current_password"
                                required
                                class="block w-full pl-11 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-red-500/10 placeholder-slate-300 transition-all outline-none" 
                                placeholder="Enter temporary password" 
                            />
                        </div>
                        <p v-if="form.errors.current_password" class="text-[10px] font-bold text-red-500 ml-1">{{ form.errors.current_password }}</p>
                    </div>

                    <!-- New Password -->
                    <div class="space-y-1.5 text-left">
                        <label for="password" class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest ml-1">New Secure Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-csired transition-colors">
                                <ShieldCheckIcon class="h-5 w-5" />
                            </div>
                            <input 
                                id="password"
                                type="password" 
                                v-model="form.password"
                                required
                                class="block w-full pl-11 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-red-500/10 placeholder-slate-300 transition-all outline-none" 
                                placeholder="At least 8 characters" 
                            />
                        </div>
                        <p v-if="form.errors.password" class="text-[10px] font-bold text-red-500 ml-1">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-1.5 text-left">
                        <label for="password_confirmation" class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest ml-1">Confirm New Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-csired transition-colors">
                                <ShieldCheckIcon class="h-5 w-5" />
                            </div>
                            <input 
                                id="password_confirmation"
                                type="password" 
                                v-model="form.password_confirmation"
                                required
                                class="block w-full pl-11 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-red-500/10 placeholder-slate-300 transition-all outline-none" 
                                placeholder="Repeat new password" 
                            />
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full py-5 bg-slate-900 hover:bg-black text-white rounded-2xl font-black text-[0.7rem] tracking-widest uppercase transition-all shadow-xl shadow-slate-200 flex items-center justify-center gap-2 group disabled:opacity-50"
                    >
                        {{ form.processing ? 'Updating...' : 'Update Password & Continue' }}
                        <ShieldCheckIcon v-if="!form.processing" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                    </button>
                </form>

                <div class="pt-6 border-t border-slate-50 flex items-center justify-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">End-to-End Encrypted</p>
                </div>
            </div>

            <!-- Security Info -->
            <div class="mt-8 flex items-center justify-center gap-6 px-10">
                <div class="flex flex-col items-center">
                    <p class="text-[0.65rem] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Encrypted</p>
                    <div class="h-1 w-8 bg-slate-200 rounded-full"></div>
                </div>
                <div class="flex flex-col items-center">
                    <p class="text-[0.65rem] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Private</p>
                    <div class="h-1 w-8 bg-slate-200 rounded-full"></div>
                </div>
                <div class="flex flex-col items-center">
                    <p class="text-[0.65rem] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Secure</p>
                    <div class="h-1 w-8 bg-slate-200 rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
</template>
