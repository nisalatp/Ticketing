<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    error: {
        type: String,
        default: null,
    }
});
</script>

<template>
    <Head :title="`${$page.props.branding.name} - Support Desk`" />
    
    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6 relative overflow-hidden font-sans">
        <!-- Subtle Background Decorations -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-red-50 rounded-full blur-[120px] opacity-40"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-slate-100 rounded-full blur-[120px] opacity-40"></div>
        </div>

        <div class="max-w-2xl w-full text-center space-y-12">
            <!-- Logo Section -->
            <div class="flex justify-center animate-in fade-in duration-700">
                <img v-if="$page.props.branding.logo_url" :src="$page.props.branding.logo_url" :alt="$page.props.branding.name" class="h-20 w-auto" />
                <div v-else class="w-16 h-16 bg-csired rounded-2xl flex items-center justify-center shadow-lg shadow-red-200">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
            </div>

            <!-- Branding Section -->
            <div class="space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-700 delay-100">
                <h2 class="text-csired font-bold tracking-[0.2em] uppercase text-xs">{{ $page.props.branding.tagline }}</h2>
                <h1 class="text-4xl md:text-5xl font-bold text-csidark tracking-tight">
                    {{ $page.props.branding.name }}
                </h1>
                <p class="text-base text-slate-500 max-w-md mx-auto leading-relaxed">
                    Driving operational excellence through streamlined internal support and automated service delivery.
                </p>

                <div v-if="error" class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm font-medium max-w-md mx-auto mt-4 animate-in fade-in zoom-in">
                    {{ error }}
                </div>
            </div>

            <!-- Action Section -->
            <div class="bg-white p-1 rounded-[2.5rem] shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] border border-slate-100 animate-in fade-in zoom-in duration-1000 delay-200 overflow-hidden">
                <div class="bg-white rounded-[2.2rem] p-10 space-y-8">
                    <div class="flex justify-center">
                        <div class="p-5 flex items-center justify-center">
                             <div class="w-16 h-16 bg-csired rounded-2xl flex items-center justify-center shadow-lg shadow-red-200">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                             </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <a
                            v-if="$page.props.azureAuthEnabled"
                            :href="route('login.microsoft')"
                            class="inline-flex items-center justify-center w-full px-8 py-5 text-white font-semibold rounded-2xl shadow-xl transition-all duration-300 transform hover:-translate-y-1 active:scale-[0.98] group bg-csired hover:bg-[#D31920] shadow-red-100"
                        >
                            <svg class="w-5 h-5 mr-3 group-hover:rotate-12 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-3.5 18l1.5-6h-3l6-7.5-1.5 6h3l-6 7.5z"/>
                            </svg>
                            Sign in with Office 365
                        </a>

                        <Link
                            :href="route('login')"
                            class="inline-flex items-center justify-center w-full px-8 py-5 text-slate-700 font-semibold rounded-2xl border-2 border-slate-100 hover:bg-slate-50 transition-all duration-300 transform hover:-translate-y-1 active:scale-[0.98]"
                        >
                            Sign in with Credentials
                        </Link>
                        
                        <p class="text-[0.65rem] text-slate-400 font-bold uppercase tracking-widest">
                            Authorized Personnel Only &bull; Secure Access
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="pt-8 text-slate-400 text-[0.7rem] font-bold uppercase tracking-widest animate-in fade-in duration-1000 delay-500">
                &copy; {{ new Date().getFullYear() }} {{ $page.props.branding.name }}.
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-in {
    animation-fill-mode: both;
}
@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slide-in-from-bottom-4 {
    from { transform: translateY(1rem); }
    to { transform: translateY(0); }
}
@keyframes zoom-in {
    from { transform: scale(0.95); }
    to { transform: scale(1); }
}
.fade-in { animation-name: fade-in; }
.slide-in-from-bottom-4 { animation-name: slide-in-from-bottom-4; }
.zoom-in { animation-name: zoom-in; }
</style>
