<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref, computed, onMounted} from "vue";
import axios from "axios";

const props = defineProps({
    user: Object,
});
const currentUser = ref(props.user);
const currentDate = ref(new Date());
const selectedDay = ref(null);
const reservations = ref({});
const error = ref(null);

// Formatuje datę w stylu "YYYY-MM-DD"
const formatDate = (date) => {
    return date.toISOString().split("T")[0];
};

// Pomocnicza funkcja do formatowania miesiąca i roku
const formatMonthYear = (date) => {
    return date.toLocaleString("default", { month: "long", year: "numeric" });
};

// Funkcje nawigacji po miesiącach
const previousMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() - 1);
    currentDate.value = newDate;
};

const nextMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() + 1);
    currentDate.value = newDate;
};

const goToToday = () => {
    currentDate.value = new Date();
};

// Funkcja pomocnicza do uzyskania dni miesiąca
const getDaysInMonth = (date) => {
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDayOfMonth = new Date(year, month, 1);
    const lastDayOfMonth = new Date(year, month + 1, 0);
    const days = [];

    // Dni poprzedzające pierwszy dzień miesiąca
    const startDay = firstDayOfMonth.getDay() || 7; // Niedziela jako ostatni dzień tygodnia
    for (let i = startDay - 1; i > 0; i--) {
        const prevDate = new Date(firstDayOfMonth);
        prevDate.setDate(prevDate.getDate() - i);
        days.push({ date: prevDate, isCurrentMonth: false });
    }

    // Dni bieżącego miesiąca
    for (let i = 1; i <= lastDayOfMonth.getDate(); i++) {
        days.push({ date: new Date(year, month, i), isCurrentMonth: true });
    }

    // Dni po ostatnim dniu miesiąca, aby uzupełnić tygodnie
    const endDay = lastDayOfMonth.getDay() || 7;
    for (let i = 1; i <= 7 - endDay; i++) {
        const nextDate = new Date(lastDayOfMonth);
        nextDate.setDate(nextDate.getDate() + i);
        days.push({ date: nextDate, isCurrentMonth: false });
    }

    return days;
};

// Obliczanie dni w bieżącym miesiącu
const days = computed(() => getDaysInMonth(currentDate.value));

// Funkcja otwierająca modal rezerwacji dla wybranego dnia
const openReservationModal = (day) => {
    selectedDay.value = day;
    error.value = null;
};

// Dodanie rezerwacji do wybranego dnia
const addReservation = async () => {
    const dayKey = formatDate(selectedDay.value);

    try {
        await axios.post("/reservations", {
            reservation_date: dayKey,
        });

        // Zaktualizuj dane w kalendarzu
        if (!reservations.value[dayKey]) {
            reservations.value[dayKey] = [];
        }
        reservations.value[dayKey].push({
            userId: currentUser.value.id,
            name: currentUser.value.name,
        });

        selectedDay.value = null; // Zamknij modal
    } catch (error) {
        error.value = "Nie udało się zapisać rezerwacji.";
        console.error("Błąd podczas dodawania rezerwacji:", error);
    }
};

// Pobierz rezerwacje użytkownika
const fetchReservations = async () => {
    try {
        const response = await axios.get("/reservations");
        const fetchedReservations = response.data;

        fetchedReservations.forEach((reservation) => {
            const dateKey = reservation.reservation_date;
            if (!reservations.value[dateKey]) {
                reservations.value[dateKey] = [];
            }
            reservations.value[dateKey].push({
                userId: reservation.user_id,
                name: reservation.user_name,
            });
        });
    } catch (error) {
        console.error("Błąd podczas pobierania rezerwacji:", error);
    }
};

onMounted(() => {
    fetchReservations();
});
</script>

<template>
    <AppLayout title="Dynamiczny Kalendarz z Rezerwacjami">
        <div class="py-12">
            <div class="max-w-5xl mx-auto px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg">
                    <!-- Header -->
                    <header class="flex items-center justify-between border-b border-gray-300 px-6 py-4 bg-gray-100">
                        <h1 class="text-lg font-bold text-gray-700">
                            <time :datetime="currentDate.toISOString()">{{ formatMonthYear(currentDate) }}</time>
                        </h1>
                        <div class="flex items-center space-x-4">
                            <button
                                @click="previousMonth"
                                class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600"
                            >
                                Poprzedni miesiąc
                            </button>
                            <button
                                @click="goToToday"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500"
                            >
                                Dziś
                            </button>
                            <button
                                @click="nextMonth"
                                class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600"
                            >
                                Następny miesiąc
                            </button>
                        </div>
                    </header>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-2 border-t border-gray-300 p-6 bg-gray-50">
                        <!-- Week Days -->
                        <div
                            v-for="day in ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'Sb', 'Nd']"
                            :key="day"
                            class="text-center font-semibold text-gray-600"
                        >
                            {{ day }}
                        </div>

                        <!-- Days -->
                        <div
                            v-for="day in days"
                            :key="day.date"
                            :class="[
                                'flex flex-col justify-between p-4 border rounded-lg text-sm',
                                day.isCurrentMonth ? 'bg-white text-gray-900' : 'bg-gray-200 text-gray-500',
                                day.date.toDateString() === new Date().toDateString() && 'bg-blue-500 text-white',
                                day.date.getDay() === 0 || day.date.getDay() === 6 ? 'bg-gray-100' : ''
                            ]"
                            @click="openReservationModal(day.date)"
                        >
                            <span class="font-bold">{{ day.date.getDate() }}</span>
                            <ul v-if="reservations[formatDate(day.date)]" class="mt-2 text-xs">
                                <li
                                    v-for="(reservation, index) in reservations[formatDate(day.date)]"
                                    :key="index"
                                    class="text-gray-700"
                                >
                                    {{ reservation.name }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div
            v-if="selectedDay"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white p-8 rounded-lg shadow-xl max-w-sm w-full">
                <h2 class="text-xl font-bold mb-4">Zarezerwuj wizytę</h2>
                <p class="mb-4">Data: {{ selectedDay.toDateString() }}</p>
                <p v-if="error" class="text-red-500 mb-4">{{ error }}</p>
                <div class="flex justify-end space-x-4">
                    <button @click="selectedDay = null" class="px-4 py-2 bg-gray-300 rounded-md">
                        Anuluj
                    </button>
                    <button
                        @click="addReservation"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md"
                    >
                        Zapisz
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
