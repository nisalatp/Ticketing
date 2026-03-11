<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpenIcon, ChevronRightIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

const articles = [
    {
        id: 1,
        title: 'How to Create a Service Ticket',
        description: 'A step-by-step guide to submitting a request or reporting an issue through the portal.',
        slug: 'how-to-create-a-ticket',
        category: 'Getting Started'
    }
];
</script>

<template>
    <Head title="Knowledge Base" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                    <BookOpenIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Knowledge Base</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-tight">Find guides, tutorials, and policy documentation.</p>
                </div>
            </div>
        </template>

        <div class="w-full py-8 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <div v-for="article in articles" :key="article.id" class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow group relative flex flex-col h-full">
                    <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center text-csired mb-4">
                        <DocumentTextIcon class="w-6 h-6" />
                    </div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">{{ article.category }}</div>
                    <h3 class="text-lg font-bold text-slate-900 leading-tight mb-2 group-hover:text-csired transition-colors">{{ article.title }}</h3>
                    <p class="text-sm text-slate-500 mb-6 flex-grow">{{ article.description }}</p>
                    
                    <Link :href="route('knowledge-base.show', article.slug)" class="inline-flex items-center text-sm font-bold text-csired hover:text-[#B3151B]">
                        Read Article <ChevronRightIcon class="w-4 h-4 ml-1" />
                    </Link>
                    <!-- Make the whole card clickable -->
                    <Link :href="route('knowledge-base.show', article.slug)" class="absolute inset-0 z-10"><span class="sr-only">View {{ article.title }}</span></Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
