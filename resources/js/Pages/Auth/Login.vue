<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { EnvelopeIcon, KeyIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Sign In" />

    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6 relative overflow-hidden font-sans">
        <!-- Subtle Background Decorations -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-csired to-transparent opacity-20"></div>
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-red-50 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-50 rounded-full blur-3xl opacity-50"></div>

        <div class="max-w-md w-full">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-white overflow-hidden p-10 space-y-8">
                
                <!-- Logo/Branding -->
                <div class="text-center space-y-2">
                    <img v-if="$page.props.branding.logo_url" :src="$page.props.branding.logo_url" :alt="$page.props.branding.name" class="h-16 mx-auto mb-6" />
                    <div v-else class="mx-auto w-16 h-16 bg-csired rounded-2xl flex items-center justify-center shadow-lg shadow-red-100 mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $page.props.branding.name }}</h1>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest">Support Portal Login</p>
                </div>

                <div v-if="status" class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 text-xs font-bold text-emerald-700 text-center">
                    {{ status }}
                </div>

                <!-- Microsoft Login Button -->
                <div v-if="$page.props.azureAuthEnabled" class="space-y-4">
                    <a
                        :href="route('login.microsoft')"
                        class="inline-flex items-center justify-center w-full px-6 py-4 text-white font-semibold rounded-2xl transition-all duration-300 transform hover:-translate-y-0.5 active:scale-[0.98] bg-csired hover:bg-[#D31920] shadow-xl shadow-red-100 group"
                    >
                        <svg class="w-5 h-5 mr-3 group-hover:rotate-12 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-3.5 18l1.5-6h-3l6-7.5-1.5 6h3l-6 7.5z"/>
                        </svg>
                        Sign in with Office 365
                    </a>
                    
                    <div class="relative flex items-center justify-center py-2">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-100"></div></div>
                        <span class="relative px-3 bg-white text-[10px] font-black text-slate-300 uppercase tracking-widest backdrop-blur-xl">Or use credentials</span>
                    </div>
                </div>

                <!-- Local Login Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label for="email" class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-csired transition-colors">
                                    <EnvelopeIcon class="h-5 w-5" />
                                </div>
                                <input 
                                    id="email"
                                    type="email" 
                                    v-model="form.email"
                                    required
                                    class="block w-full pl-11 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-red-500/10 placeholder-slate-300 transition-all outline-none" 
                                    placeholder="Enter your email" 
                                />
                            </div>
                            <p v-if="form.errors.email" class="text-[10px] font-bold text-red-500 ml-1">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between px-1">
                                <label for="password" class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest">Password</label>
                                <Link :href="route('password.request')" class="text-[10px] font-black text-csired uppercase tracking-widest hover:text-red-700 transition-colors">Forgot?</Link>
                            </div>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-csired transition-colors">
                                    <KeyIcon class="h-5 w-5" />
                                </div>
                                <input 
                                    id="password"
                                    type="password" 
                                    v-model="form.password"
                                    required
                                    class="block w-full pl-11 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-red-500/10 placeholder-slate-300 transition-all outline-none" 
                                    placeholder="Enter your password" 
                                />
                            </div>
                            <p v-if="form.errors.password" class="text-[10px] font-bold text-red-500 ml-1">{{ form.errors.password }}</p>
                        </div>
                    </div>

                    <div class="flex items-center ml-1">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded-lg border-slate-200 text-csired focus:ring-red-500/20" />
                            <span class="ml-2 text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition-colors">Keep me signed in</span>
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full py-5 bg-slate-900 hover:bg-black text-white rounded-2xl font-black text-[0.7rem] tracking-widest uppercase transition-all shadow-xl shadow-slate-200 flex items-center justify-center gap-2 group disabled:opacity-50"
                    >
                        {{ form.processing ? 'Signing in...' : 'Sign In' }}
                        <ArrowRightIcon v-if="!form.processing" class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </button>
                </form>

                <div class="pt-6 border-t border-slate-50 flex flex-col items-center gap-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Authorized Access Only</p>
                </div>
            </div>

            <!-- Footer Meta -->
            <div class="mt-8 text-center text-slate-400 text-[0.65rem] font-bold uppercase tracking-[0.2em] animate-in fade-in duration-1000 delay-500">
                &copy; {{ new Date().getFullYear() }} {{ $page.props.branding.name }}.
            </div>
        </div>
    </div>
</template>
