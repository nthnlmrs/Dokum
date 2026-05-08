<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Editor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import { onMounted, onBeforeUnmount, ref } from 'vue';

const props = defineProps({ documentation: Object });
const editor = ref(null);
const form = useForm({ content: props.documentation.content || '' });

onMounted(() => {
    editor.value = new Editor({
        extensions: [StarterKit],
        content: form.content,
        onUpdate: () => { form.content = editor.value.getHTML(); },
        editorProps: { attributes: { class: 'prose mx-auto focus:outline-none min-h-[400px] border p-4' } },
    });
});
onBeforeUnmount(() => { editor.value?.destroy(); });

const saveDoc = () => { form.put(route('docs.update', props.documentation.id), { preserveScroll: true }); };
const exportGoogle = () => { form.post(route('docs.export.google', props.documentation.id), { preserveScroll: true }); };
</script>
<template>
    <Head title="Edit Documentation" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editing: {{ documentation.title }}</h2>
                <div>
                    <button @click="saveDoc" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 mr-2" :disabled="form.processing">Save</button>
                    <a :href="route('docs.export.pdf', documentation.id)" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 mr-2">PDF</a>
                    <button @click="exportGoogle" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" :disabled="form.processing">Google Docs</button>
                </div>
            </div>
        </template>
        <div class="py-12"><div class="max-w-5xl mx-auto sm:px-6 lg:px-8"><div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"><div class="p-6 text-gray-900">
            <editor-content :editor="editor" />
        </div></div></div></div>
    </AuthenticatedLayout>
</template>
