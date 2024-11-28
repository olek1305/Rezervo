<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
    search: String,
});

const localSearch = ref(props.search || '');

function searchUsers() {
    router.get(route('admin.doctors.index'), { search: localSearch.value });
}

function assignDoctor(userId) {
    if (confirm('Czy na pewno chcesz nadać rolę Doctor temu użytkownikowi?')) {
        router.post(route('admin.doctors.assign', { user: userId }), {}, {
            onSuccess: () => alert('Rola Doctor została nadana.'),
        });
    }
}

function removeDoctor(userId) {
    if (confirm('Czy na pewno chcesz usunąć rolę Doctor temu użytkownikowi?')) {
        router.post(route('admin.doctors.remove', { user: userId }), {}, {
            onSuccess: () => alert('Rola Doctor została usunięta.'),
        });
    }
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
                Szukaj
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
                            @click="assignDoctor(user.id)"
                            class="px-4 py-2 bg-green-500 text-white rounded shadow hover:bg-green-600"
                        >
                            Nadaj rolę Doctor
                        </button>
                        <button
                            @click="removeDoctor(user.id)"
                            class="px-4 py-2 bg-red-500 text-white rounded shadow hover:bg-red-600"
                        >
                            Usuń rolę Doctor
                        </button>
                    </div>
                </li>
            </ul>
        </div>
        <div v-else class="text-center text-gray-500">
            Brak wyników do wyświetlenia.
        </div>
    </div>
</template>
