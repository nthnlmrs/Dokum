<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    aiConfigurations: {
        type: Array,
        required: true
    },
    hasGithubToken: {
        type: Boolean,
        required: true
    }
});

// Github Token Form
const githubForm = useForm({
    github_token: '',
});

const saveGithubToken = () => {
    githubForm.post(route('settings.github-token'), {
        preserveScroll: true,
        onSuccess: () => {
            githubForm.reset();
            alert('GitHub Token saved successfully!');
        }
    });
};

// AI Config Form
const aiForm = useForm({
    provider: 'openai',
    model_name: '',
    api_key: '',
});

const isEditing = ref(false);
const fetchedModels = ref([]);
const isFetchingModels = ref(false);

const loadModels = async () => {
    if (!aiForm.api_key) {
        alert("Please enter an API Key first.");
        return;
    }

    isFetchingModels.value = true;
    fetchedModels.value = [];

    try {
        const response = await axios.post(route('settings.ai.fetch-models'), {
            provider: aiForm.provider,
            api_key: aiForm.api_key
        });

        fetchedModels.value = response.data.models;

        // Auto-select first model if none selected or if current is invalid
        if (fetchedModels.value.length > 0 && !fetchedModels.value.includes(aiForm.model_name)) {
            aiForm.model_name = fetchedModels.value[0];
        }
    } catch (error) {
        console.error(error);
        alert(error.response?.data?.error || "Failed to fetch models. Check your API key.");
    } finally {
        isFetchingModels.value = false;
    }
};

// Reset models when provider changes
watch(() => aiForm.provider, () => {
    fetchedModels.value = [];
});
const editingId = ref(null);

const saveAiConfig = () => {
    if (isEditing.value) {
        aiForm.put(route('settings.ai.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => {
                cancelEdit();
            }
        });
    } else {
        aiForm.post(route('settings.ai.store'), {
            preserveScroll: true,
            onSuccess: () => {
                aiForm.reset();
            }
        });
    }
};

const editConfig = (config) => {
    isEditing.value = true;
    editingId.value = config.id;
    aiForm.provider = config.provider;
    aiForm.model_name = config.model_name;
    aiForm.api_key = ''; // Leave blank for security, only update if typed
};

const cancelEdit = () => {
    isEditing.value = false;
    editingId.value = null;
    aiForm.reset();
};

const deleteConfig = (id) => {
    if (confirm('Are you sure you want to delete this configuration?')) {
        router.delete(route('settings.ai.destroy', id), {
            preserveScroll: true
        });
    }
};

const activateConfig = (id) => {
    router.post(route('settings.ai.activate', id), {}, {
        preserveScroll: true
    });
};

</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settings</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- GitHub Token Section -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">GitHub Access Token</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Provide your Personal Access Token so the AI can read your private repositories.
                            <span v-if="hasGithubToken" class="text-green-600 font-bold ml-2">(Token is currently set)</span>
                            <span v-else class="text-red-600 font-bold ml-2">(Token not set)</span>
                        </p>
                    </header>

                    <form @submit.prevent="saveGithubToken" class="mt-6 space-y-6 max-w-xl">
                        <div>
                            <label for="github_token" class="block text-sm font-medium text-gray-700">Token</label>
                            <input
                                id="github_token"
                                v-model="githubForm.github_token"
                                type="password"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                placeholder="ghp_xxxxxxxxxxxx"
                                required
                            />
                        </div>

                        <div class="flex items-center gap-4">
                            <button
                                type="submit"
                                :disabled="githubForm.processing"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Save Token
                            </button>
                        </div>
                    </form>
                </div>

                <!-- AI Configurations Section -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <header class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900">AI Configurations</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage your AI providers, models, and API keys. The "Active" configuration will be used by the documentation generator.
                        </p>
                    </header>

                    <!-- Add / Edit Form -->
                    <div class="bg-gray-50 p-4 rounded-md mb-8 border">
                        <h3 class="text-md font-medium text-gray-900 mb-4">{{ isEditing ? 'Edit Configuration' : 'Add New Configuration' }}</h3>
                        <form @submit.prevent="saveAiConfig" class="space-y-4 max-w-2xl">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Provider</label>
                                <select v-model="aiForm.provider" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="openai">OpenAI</option>
                                    <option value="gemini">Google Gemini</option>
                                    <option value="anthropic">Anthropic Claude</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">API Key</label>
                                <div class="flex mt-1">
                                    <input v-model="aiForm.api_key" type="password" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-l-md shadow-sm" :placeholder="isEditing ? 'Leave blank to keep existing key' : 'sk-...'" :required="!isEditing" />
                                    <button type="button" @click="loadModels" :disabled="isFetchingModels" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-r-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-25 transition ease-in-out duration-150">
                                        {{ isFetchingModels ? 'Loading...' : 'Load Models' }}
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Enter your API key and click "Load Models" to see available models.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Model Name</label>
                                <select v-if="fetchedModels.length > 0" v-model="aiForm.model_name" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option v-for="model in fetchedModels" :key="model" :value="model">{{ model }}</option>
                                </select>
                                <input v-else v-model="aiForm.model_name" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="e.g. gpt-4o, gemini-1.5-pro" required />
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    {{ isEditing ? 'Update Configuration' : 'Add Configuration' }}
                                </button>
                                <button v-if="isEditing" @click="cancelEdit" type="button" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- List of Configurations -->
                    <div v-if="aiConfigurations.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="config in aiConfigurations" :key="config.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="config.is_active" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                        <button v-else @click="activateConfig(config.id)" class="text-xs text-indigo-600 hover:text-indigo-900 border border-indigo-600 px-2 py-1 rounded">
                                            Set Active
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{{ config.provider }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ config.model_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="editConfig(config)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
                                        <button @click="deleteConfig(config.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-gray-500 text-sm py-4">
                        No AI configurations found. Please add one above.
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
