<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const availabilities = ref(usePage().props.availabilities || []);
const newSlot = ref({
    available_date: '',
    start_time: '',
    end_time: '',
});
const errors = ref({});

const addAvailability = async () => {
    try {
        await router.post('/doctor/availability', newSlot.value);
        alert('Availability added!');
        newSlot.value = { available_date: '', start_time: '', end_time: '' };
    } catch (err) {
        errors.value = err.response.data.errors || {};
    }
};

const deleteAvailability = async (id) => {
    if (!confirm('Are you sure you want to delete this slot?')) return;
    await router.delete(`/doctor/availability/${id}`);
    alert('Availability deleted!');
};
</script>

<template>
    <div>
        <h1 class="text-2xl font-bold">Manage Availability</h1>

        <form @submit.prevent="addAvailability" class="mt-4 space-y-4">
            <div>
                <label for="date" class="block font-medium">Date:</label>
                <input
                    v-model="newSlot.available_date"
                    type="date"
                    id="date"
                    class="border rounded w-full"
                />
                <div v-if="errors.available_date" class="text-red-500">{{ errors.available_date }}</div>
            </div>

            <div>
                <label for="start_time" class="block font-medium">Start Time:</label>
                <input
                    v-model="newSlot.start_time"
                    type="time"
                    id="start_time"
                    class="border rounded w-full"
                />
                <div v-if="errors.start_time" class="text-red-500">{{ errors.start_time }}</div>
            </div>

            <div>
                <label for="end_time" class="block font-medium">End Time:</label>
                <input
                    v-model="newSlot.end_time"
                    type="time"
                    id="end_time"
                    class="border rounded w-full"
                />
                <div v-if="errors.end_time" class="text-red-500">{{ errors.end_time }}</div>
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                Add Availability
            </button>
        </form>

        <div class="mt-8">
            <h2 class="text-xl font-semibold">Existing Slots</h2>
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
                        Remove
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>
