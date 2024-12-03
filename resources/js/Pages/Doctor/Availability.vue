<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { useI18n } from "vue-i18n";
import AppLayout from "@/Layouts/AppLayout.vue";

const userSpecialization = usePage().props.auth.user.specialization || null;
const availabilities = ref(usePage().props.availabilities || []);
const newSlot = ref({
    available_date: '',
    start_time: '',
    end_time: '',
});
const errors = ref({});
const warning = ref(
    !userSpecialization || userSpecialization.trim() === ''
        ? 'Nie posiadasz ustawionej specjalizacji, więc nie możesz ustawić terminu.'
        : null
);

// Modal state
const deleteModalVisible = ref(false);
const addModalVisible = ref(false);
const slotToDelete = ref(null);

const openDeleteModal = (slot) => {
    slotToDelete.value = slot;
    deleteModalVisible.value = true;
};

const closeDeleteModal = () => {
    slotToDelete.value = null;
    deleteModalVisible.value = false;
};

const confirmDelete = async () => {
    if (!slotToDelete.value) return;

    try {
        await router.delete(`/doctor/availability/${slotToDelete.value.id}`);
        availabilities.value = availabilities.value.filter((slot) => slot.id !== slotToDelete.value.id);
        alert('Dostępność została usunięta!');
    } catch (err) {
        console.error('Nie udało się usunąć terminu:', err);
    } finally {
        closeDeleteModal();
    }
};

const openAddModal = () => {
    addModalVisible.value = true;
};

const closeAddModal = () => {
    // Reset formularza i zamknięcie modalu
    newSlot.value = {available_date: '', start_time: '', end_time: ''};
    errors.value = {};
    addModalVisible.value = false;
};

const addAvailability = async () => {
    // Sprawdź, czy wszystkie pola są wypełnione
    if (!newSlot.value.available_date || !newSlot.value.start_time || !newSlot.value.end_time) {
        errors.value = {
            available_date: !newSlot.value.available_date ? 'Data jest wymagana.' : null,
            start_time: !newSlot.value.start_time ? 'Godzina rozpoczęcia jest wymagana.' : null,
            end_time: !newSlot.value.end_time ? 'Godzina zakończenia jest wymagana.' : null,
        };
        return;
    }

    try {
        await router.post('/doctor/availability', newSlot.value);
        availabilities.value.push({...newSlot.value, id: Date.now()}); // Tymczasowe dodanie dla UI
        alert('Dostępność została dodana!');
        closeAddModal();
    } catch (err) {
        errors.value = err.response.data.errors || {};
    }
};

const { t } = useI18n();
</script>

<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-900 dark:text-gray-100 overflow-hidden shadow-lg rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-4">{{ t('manage_availability') }}</h1>

                    <!-- Warning about no specialization -->
                    <div v-if="warning" class="mb-6 p-4 bg-red-100 text-red-600 rounded">
                        {{ t('no_specialization_warning') }}
                    </div>

                    <!-- Button to add new availability -->
                    <div class="mb-6">
                        <button
                            @click="openAddModal"
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                        >
                            {{ t('add_availability') }}
                        </button>
                    </div>

                    <!-- Existing terms -->
                    <div>
                        <h2 class="text-xl font-semibold mb-4">{{ t('existing_terms_heading') }}</h2>
                        <ul class="space-y-2 dark:text-black">
                            <li
                                v-for="slot in availabilities"
                                :key="slot.id"
                                class="flex justify-between items-center border p-4 rounded bg-gray-50"
                            >
                                <div>
                                    {{ slot.available_date }}: {{ slot.start_time }} - {{ slot.end_time }}
                                </div>
                                <button
                                    @click="openDeleteModal(slot)"
                                    class="text-red-500 hover:underline"
                                >
                                    {{ t('delete') }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Availability Modal -->
        <Modal
            :show="addModalVisible"
            closeable
            maxWidth="sm"
            @close="closeAddModal"
        >
            <template #default>
                <div class="p-6">
                    <h2 class="text-lg font-semibold mb-4">{{ t('add_availability') }}</h2>
                    <form @submit.prevent="addAvailability" class="space-y-4">
                        <div>
                            <label for="date" class="block font-medium">{{ t('date_label') }}</label>
                            <input
                                v-model="newSlot.available_date"
                                type="date"
                                id="date"
                                class="border rounded w-full"
                            />
                            <div v-if="errors.available_date" class="text-red-500">{{ errors.available_date }}</div>
                        </div>
                        <div>
                            <label for="start_time" class="block font-medium">{{ t('start_time_label') }}</label>
                            <input
                                v-model="newSlot.start_time"
                                type="time"
                                id="start_time"
                                class="border rounded w-full"
                            />
                            <div v-if="errors.start_time" class="text-red-500">{{ errors.start_time }}</div>
                        </div>
                        <div>
                            <label for="end_time" class="block font-medium">{{ t('end_time_label') }}</label>
                            <input
                                v-model="newSlot.end_time"
                                type="time"
                                id="end_time"
                                class="border rounded w-full"
                            />
                            <div v-if="errors.end_time" class="text-red-500">{{ errors.end_time }}</div>
                        </div>
                        <div class="flex justify-end">
                            <button
                                class="bg-gray-300 text-black px-4 py-2 mr-2 rounded hover:bg-gray-400"
                                @click="closeAddModal"
                            >
                                {{ t('cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                            >
                                {{ t('save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal
            :show="deleteModalVisible"
            closeable
            maxWidth="sm"
            @close="closeDeleteModal"
        >
            <template #default>
                <div class="p-6">
                    <h2 class="text-lg font-semibold">{{ t('delete_confirmation') }}</h2>
                    <p class="mt-2">{{ t('confirm_delete_availability') }}</p>
                    <div class="mt-4 flex justify-end">
                        <button
                            class="bg-gray-300 text-black px-4 py-2 mr-2 rounded hover:bg-gray-400"
                            @click="closeDeleteModal"
                        >
                            {{ t('cancel') }}
                        </button>
                        <button
                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                            @click="confirmDelete"
                        >
                            {{ t('delete') }}
                        </button>
                    </div>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>
