<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from "@/Components/Modal.vue";


const props = defineProps({
    users: Array,
    search: String,
});

const localSearch = ref(props.search || '');
const showAssignModal = ref(false);
const showRemoveModal = ref(false);
const selectedUserId = ref(null);

function searchUsers() {
    router.get(route('admin.doctors.index'), { search: localSearch.value });
}

function openAssignModal(userId) {
    selectedUserId.value = userId;
    showAssignModal.value = true;
}

function openRemoveModal(userId) {
    selectedUserId.value = userId;
    showRemoveModal.value = true;
}

function assignDoctor() {
    router.post(route('admin.doctors.assign', { user: selectedUserId.value }), {}, {
        onSuccess: () => {
            alert('Rola Doctor została nadana.');
            showAssignModal.value = false;
        },
    });
}

function removeDoctor() {
    router.post(route('admin.doctors.remove', { user: selectedUserId.value }), {}, {
        onSuccess: () => {
            alert('Rola Doctor została usunięta.');
            showRemoveModal.value = false;
        },
    });
}
</script>

<template>
    <div class="p-8 bg-gray-100 min-h-screen">
        <h1 class="text-2xl font-bold mb-6">Zarządzanie lekarzami</h1>

        <!-- Search Engine -->
        <form @submit.prevent="searchUsers" class="mb-6 flex gap-4">
            <input
                v-model="localSearch"
                type="text"
                placeholder="Wpisz imię lub email"
                class="px-4 py-2 border border-gray-300 rounded shadow-sm w-full focus:ring focus:ring-blue-200"
            />
            <button
                type="submit"
                class="px-6 py-2 bg-blue-500 text-white rounded shadow hover:bg-blue-600"
            >
                {{ t('search') }}
            </button>
        </form>

        <!-- List Users -->
        <div v-if="users.length" class="bg-white rounded shadow p-4">
            <ul>
                <li
                    v-for="user in users"
                    :key="user.id"
                    class="flex justify-between items-center py-2 border-b last:border-b-0"
                >
                    <div>
                        <p class="text-lg font-medium">{{ user.name }}</p>
                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="openAssignModal(user.id)"
                            class="px-4 py-2 bg-green-500 text-white rounded shadow hover:bg-green-600"
                        >
                            {{ t('assign_role') }}
                        </button>
                        <button
                            @click="openRemoveModal(user.id)"
                            class="px-4 py-2 bg-red-500 text-white rounded shadow hover:bg-red-600"
                        >
                            {{ t('remove_role') }}
                        </button>
                    </div>
                </li>
            </ul>
        </div>
        <div v-else class="text-center text-gray-500">
            {{ t('no_display') }}
        </div>

        <!-- Assign Role Modal -->
        <Modal :show="showAssignModal" @close="showAssignModal = false">
            <template v-slot>
                <div class="p-6">
                    <h2 class="text-lg font-bold mb-4">Potwierdź akcję</h2>
                    <p>Czy na pewno chcesz nadać rolę Doctor temu użytkownikowi?</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            @click="showAssignModal = false"
                            class="px-4 py-2 bg-gray-300 rounded shadow hover:bg-gray-400"
                        >
                            {{ t('cancel') }}
                        </button>
                        <button
                            @click="assignDoctor"
                            class="px-4 py-2 bg-green-500 text-white rounded shadow hover:bg-green-600"
                        >
                            {{ t('confirm') }}
                        </button>
                    </div>
                </div>
            </template>
        </Modal>

        <!-- Remove Role Modal -->
        <Modal :show="showRemoveModal" @close="showRemoveModal = false">
            <template v-slot>
                <div class="p-6 dark:text-white">
                    <h2 class="text-lg font-bold mb-4">{{ t('confirm_action') }}</h2>
                    <p>{{ t('ask_role_to_confirm') }}</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <button
                            @click="showRemoveModal = false"
                            class="px-4 py-2 bg-gray-300 rounded shadow hover:bg-gray-400"
                        >
                            {{ t('cancel') }}
                        </button>
                        <button
                            @click="removeDoctor"
                            class="px-4 py-2 bg-red-500 text-white rounded shadow hover:bg-red-600"
                        >
                            {{ t('confirm') }}
                        </button>
                    </div>
                </div>
            </template>
        </Modal>
    </div>
</template>
