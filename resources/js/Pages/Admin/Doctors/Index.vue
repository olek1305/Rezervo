<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from "@/Components/Modal.vue";
import { useI18n } from "vue-i18n";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    users: Object,
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
            alert(t('role_assigned'));
            showAssignModal.value = false;
        },
    });
}

function removeDoctor() {
    router.post(route('admin.doctors.remove', { user: selectedUserId.value }), {}, {
        onSuccess: () => {
            alert(t('role_removed'));
            showRemoveModal.value = false;
        },
    });
}

function goToPage(page) {
    router.get(route('admin.doctors.index'), { search: localSearch.value, page });
}

const { t } = useI18n();
</script>

<template>
    <AppLayout>
        <div class="p-4 sm:p-6 lg:p-8 bg-gray-100 min-h-screen">
            <h1 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-center sm:text-left">
                {{ t('manage_doctors') }}
            </h1>

            <!-- Search Engine -->
            <form @submit.prevent="searchUsers" class="mb-4 sm:mb-6 flex flex-wrap gap-4">
                <input
                    v-model="localSearch"
                    type="text"
                    :placeholder="t('enter_name_or_email')"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded shadow-sm w-full sm:w-auto focus:ring focus:ring-blue-200"
                />
                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-500 text-white rounded shadow hover:bg-blue-600 w-full sm:w-auto"
                >
                    {{ t('search') }}
                </button>
            </form>

            <!-- List Users -->
            <div v-if="users.data.length" class="bg-white rounded shadow p-4 sm:p-6">
                <ul>
                    <li
                        v-for="user in users.data"
                        :key="user.id"
                        class="flex flex-wrap sm:flex-nowrap justify-between items-center py-2 border-b last:border-b-0"
                    >
                        <div class="w-full sm:w-auto mb-2 sm:mb-0">
                            <p class="text-lg font-medium text-center sm:text-left">{{ user.name }}</p>
                            <p class="text-sm text-gray-500 text-center sm:text-left">{{ user.email }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2 justify-center sm:justify-end">
                            <button
                                @click="openAssignModal(user.id)"
                                class="px-4 py-2 bg-green-500 text-white rounded shadow hover:bg-green-600 w-full sm:w-auto"
                            >
                                {{ t('assign_role') }}
                            </button>
                            <button
                                @click="openRemoveModal(user.id)"
                                class="px-4 py-2 bg-red-500 text-white rounded shadow hover:bg-red-600 w-full sm:w-auto"
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
            <div v-if="users.last_page > 1" class="flex justify-center gap-2 mt-4">
                <button
                    v-for="page in users.last_page"
                    :key="page"
                    @click="goToPage(page)"
                    :class="['px-4 py-2 rounded shadow', page === users.current_page ? 'bg-blue-500 text-white' : 'bg-gray-300']"
                >
                    {{ page }}
                </button>
            </div>

            <!-- Assign Role Modal -->
            <Modal :show="showAssignModal" @close="showAssignModal = false">
                <template v-slot>
                    <div class="p-6 max-w-md mx-auto dark:text-white">
                        <h2 class="text-lg font-bold mb-4 text-center">{{ t('confirm_action') }}</h2>
                        <p class="text-center">{{ t('ask_role_to_confirm') }}</p>
                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <button
                                @click="showAssignModal = false"
                                class="px-4 py-2 bg-gray-300 rounded shadow hover:bg-gray-400 w-full sm:w-auto"
                            >
                                {{ t('cancel') }}
                            </button>
                            <button
                                @click="assignDoctor"
                                class="px-4 py-2 bg-green-500 text-white rounded shadow hover:bg-green-600 w-full sm:w-auto"
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
                    <div class="p-6 max-w-md mx-auto dark:text-white">
                        <h2 class="text-lg font-bold mb-4 text-center">{{ t('confirm_action') }}</h2>
                        <p class="text-center">{{ t('ask_role_to_delete') }}</p>
                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <button
                                @click="showRemoveModal = false"
                                class="px-4 py-2 bg-gray-300 rounded shadow hover:bg-gray-400 w-full sm:w-auto"
                            >
                                {{ t('cancel') }}
                            </button>
                            <button
                                @click="removeDoctor"
                                class="px-4 py-2 bg-red-500 text-white rounded shadow hover:bg-red-600 w-full sm:w-auto"
                            >
                                {{ t('confirm') }}
                            </button>
                        </div>
                    </div>
                </template>
            </Modal>
        </div>
    </AppLayout>
</template>
