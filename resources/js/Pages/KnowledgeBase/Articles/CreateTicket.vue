<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, BookOpenIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

// We will populate this with screenshots collected from the browser subagent in the next step.
</script>

<template>
    <Head title="How to Create a Ticket" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('knowledge-base.index')" class="p-2 -ml-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <div class="w-10 h-10 bg-csired rounded-xl flex items-center justify-center shadow-lg shadow-red-100">
                    <BookOpenIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">How to Create a Service Ticket</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-tight">Getting Started • Last updated today</p>
                </div>
            </div>
        </template>

        <div class="w-full max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <article class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="p-8 sm:p-12 prose prose-slate prose-img:rounded-2xl prose-img:shadow-lg prose-img:border prose-img:border-slate-100 max-w-none">
                    
                    <p class="text-lg text-slate-600 leading-relaxed mb-8">
                        The Support portal makes it easy to submit requests, report incidents, or ask for help from our dedicated support teams. Follow this step-by-step guide to learn how to create and submit a ticket.
                    </p>

                    <div class="space-y-16">
                        
                        <!-- Step 1 -->
                        <section>
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-8 h-8 rounded-full bg-csired text-white flex items-center justify-center font-bold font-mono">1</div>
                                <h2 class="text-2xl font-bold text-slate-900 m-0">Navigate to the Ticket Creation Menu</h2>
                            </div>
                            <p class="mb-4">
                                From your main dashboard, locate the <strong class="text-csired flex items-center gap-1 inline-flex"><span class="bg-csired text-white px-2 py-0.5 rounded text-xs">NEW TICKET</span></strong> button on the top right, or select the <strong class="bg-slate-100 px-2 py-0.5 rounded text-sm text-slate-700">Submit a Ticket</strong> option from the sidebar navigation.
                            </p>
                            <!-- Placeholder for Step 1 Screenshot -->
                            <div class="w-full bg-slate-50 rounded-2xl border-2 border-slate-100 flex items-center justify-center overflow-hidden shadow-sm">
                                <img src="/images/kb/kb_step1_department_1771612878001.png" alt="Dashboard Navigation" class="w-full h-auto object-cover" />
                            </div>
                        </section>

                        <!-- Step 2 -->
                        <section>
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-8 h-8 rounded-full bg-csired text-white flex items-center justify-center font-bold font-mono">2</div>
                                <h2 class="text-2xl font-bold text-slate-900 m-0">Select the Target Department</h2>
                            </div>
                            <p class="mb-4">
                                You will be presented with a list of available Service Departments (e.g., IT Support, HR, Facilities). Click on the department that best fits your request.
                            </p>
                            <!-- Placeholder for Step 2 Screenshot -->
                            <div class="w-full bg-slate-50 rounded-2xl border-2 border-slate-100 flex items-center justify-center overflow-hidden shadow-sm mt-4">
                                <img src="/images/kb/kb_step1_department_1771612878001.png" alt="Department Selection grid" class="w-full h-auto object-cover" />
                            </div>
                        </section>

                        <!-- Step 3 -->
                        <section>
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-8 h-8 rounded-full bg-csired text-white flex items-center justify-center font-bold font-mono">3</div>
                                <h2 class="text-2xl font-bold text-slate-900 m-0">Categorize Your Request</h2>
                            </div>
                            <p class="mb-4">
                                Once a department is selected, choose the specific <strong>Topic</strong> or sub-category. This ensures your ticket is routed to the exact specialist group equipped to handle it efficiently.
                            </p>
                            <!-- Placeholder for Step 3 Screenshot -->
                            <div class="w-full bg-slate-50 rounded-2xl border-2 border-slate-100 flex items-center justify-center overflow-hidden shadow-sm mt-4">
                                <img src="/images/kb/kb_step2_topic_1771612904286.png" alt="Topic Selection" class="w-full h-auto object-cover" />
                            </div>
                        </section>

                        <!-- Step 4 -->
                        <section>
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-8 h-8 rounded-full bg-csired text-white flex items-center justify-center font-bold font-mono">4</div>
                                <h2 class="text-2xl font-bold text-slate-900 m-0">Provide Details and Submit</h2>
                            </div>
                            <p class="mb-4">
                                In the final step, fill out the form with a clear subject and a detailed description of your issue or request. If it's urgent, mark it as a <strong>Critical Incident</strong>.
                            </p>
                            <ul class="list-disc pl-5 mb-4 text-slate-600">
                                <li>Be as descriptive as possible.</li>
                                <li>You can drag and drop screenshots or log files into the attachment area.</li>
                            </ul>
                            <!-- Placeholder for Step 4 Screenshot -->
                            <div class="w-full bg-slate-50 rounded-2xl border-2 border-slate-100 flex items-center justify-center overflow-hidden shadow-sm mt-4">
                                <img src="/images/kb/kb_step3_details_1771612945553.png" alt="Final Edit Form" class="w-full h-auto object-cover" />
                            </div>
                            <div class="mt-6 bg-emerald-50 border border-emerald-100 rounded-xl p-4 flex gap-3">
                                <CheckCircleIcon class="w-6 h-6 text-emerald-500 flex-shrink-0" />
                                <p class="text-sm text-emerald-800 m-0">Click <strong>Submit Ticket</strong>. You will receive an email confirmation, and your ticket will immediately appear in your "My Tickets" queue.</p>
                            </div>
                        </section>

                    </div>
                </div>
            </article>
        </div>
    </AuthenticatedLayout>
</template>
