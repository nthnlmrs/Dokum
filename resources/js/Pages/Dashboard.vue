<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
defineProps({ documentations: Array });
</script>
<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header><h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2></template>
        <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8"><div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900">
            <h3 class="text-lg font-medium mb-4">Generated Documentations</h3>
            <div v-if="documentations.length === 0" class="text-gray-500">No documentations generated yet.</div>
            <div v-else class="space-y-4">
                <div v-for="doc in documentations" :key="doc.id" class="border p-4 rounded flex justify-between items-center">
                    <div><div class="font-bold">{{ doc.title }}</div><div class="text-sm text-gray-600">Status: {{ doc.status }}</div></div>
                    <Link :href="route('docs.edit', doc.id)" class="px-4 py-2 bg-blue-500 text-white rounded">Edit Document</Link>
                </div>
            </div>
        </div></div></div></div>
    </AuthenticatedLayout>
</template>
