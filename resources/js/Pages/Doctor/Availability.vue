<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

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

const addAvailability = async () => {
    if (warning.value) {
        return;
    }

    try {
        await router.post('/doctor/availability', newSlot.value);
        alert('Dostępność została dodana!');
        newSlot.value = { available_date: '', start_time: '', end_time: '' };
    } catch (err) {
        errors.value = err.response.data.errors || {};
    }
};


const deleteAvailability = async (id) => {
    if (!confirm('Czy na pewno chcesz usunąć ten termin?')) return;
    await router.delete(`/doctor/availability/${id}`);
    alert('Dostępność została usunięta!');
};
</script>


<template>
    <div>
        <h1 class="text-2xl font-bold">Zarządzaj Dostępnością</h1>

        <!-- Warning about no specialization -->
        <div v-if="warning" class="mt-4 p-4 bg-red-100 text-red-600 rounded">
            {{ warning }}
        </div>

        <!-- Add availability form -->
        <form v-if="!warning" @submit.prevent="addAvailability" class="mt-4 space-y-4">
            <div>
                <label for="date" class="block font-medium">Data:</label>
                <input
                    v-model="newSlot.available_date"
                    type="date"
                    id="date"
                    class="border rounded w-full"
                />
                <div v-if="errors.available_date" class="text-red-500">{{ errors.available_date }}</div>
            </div>

            <div>
                <label for="start_time" class="block font-medium">Godzina rozpoczęcia:</label>
                <input
                    v-model="newSlot.start_time"
                    type="time"
                    id="start_time"
                    class="border rounded w-full"
                />
                <div v-if="errors.start_time" class="text-red-500">{{ errors.start_time }}</div>
            </div>

            <div>
                <label for="end_time" class="block font-medium">Godzina zakończenia:</label>
                <input
                    v-model="newSlot.end_time"
                    type="time"
                    id="end_time"
                    class="border rounded w-full"
                />
                <div v-if="errors.end_time" class="text-red-500">{{ errors.end_time }}</div>
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                Dodaj Dostępność
            </button>
        </form>

        <!-- Existing terms -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold">Istniejące Terminy</h2>
            <ul class="mt-4 space-y-2">
                <li
                    v-for="slot in availabilities"
                    :key="slot.id"
                    class="flex justify-between items-center border p-4 rounded"
                >
                    <div>
                        {{ slot.available_date }}: {{ slot.start_time }} - {{ slot.end_time }}
                    </div>
                    <button
                        @click="deleteAvailability(slot.id)"
                        class="text-red-500 hover:underline"
                    >
                        Usuń
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

