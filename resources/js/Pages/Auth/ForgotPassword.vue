<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { EnvelopeIcon, ArrowLeftIcon, PaperAirplaneIcon } from '@heroicons/vue/24/outline';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6 relative overflow-hidden font-sans">
        <!-- Subtle Background Decorations -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-csired to-transparent opacity-20"></div>
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-red-50 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-50 rounded-full blur-3xl opacity-50"></div>

        <div class="max-w-md w-full">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-white overflow-hidden p-10 space-y-8">
                
                <!-- Header -->
                <div class="text-center space-y-2">
                    <div class="mx-auto w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-6">
                        <PaperAirplaneIcon class="w-8 h-8 text-slate-400 -rotate-45" />
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Recover Account</h1>
                    <p class="text-sm text-slate-500">Enter your email address and we'll send you a link to reset your password.</p>
                </div>

                <div v-if="status" class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 text-xs font-bold text-emerald-700 text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
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
                                autofocus
                                class="block w-full pl-11 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-red-500/10 placeholder-slate-300 transition-all outline-none" 
                                placeholder="Enter your registered email" 
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-[10px] font-bold text-red-500 ml-1">{{ form.errors.email }}</p>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full py-5 bg-slate-900 hover:bg-black text-white rounded-2xl font-black text-[0.7rem] tracking-widest uppercase transition-all shadow-xl shadow-slate-200 flex items-center justify-center gap-2 group disabled:opacity-50"
                    >
                        {{ form.processing ? 'Sending...' : 'Send Reset Link' }}
                        <PaperAirplaneIcon v-if="!form.processing" class="w-4 h-4 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" />
                    </button>
                </form>

                <div class="pt-6 border-t border-slate-50 flex items-center justify-center">
                    <Link :href="route('login')" class="inline-flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition-colors">
                        <ArrowLeftIcon class="w-3 h-3" />
                        Back to Sign In
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
