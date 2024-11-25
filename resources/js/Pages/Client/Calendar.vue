<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

// Dane przekazane przez Inertia
const page = usePage();
const doctorId = computed(() => page.props.doctorId);
const doctorName = computed(() => page.props.doctorName);
const timeSlots = computed(() => page.props.timeSlots || {}); // Domyślna wartość to pusty obiekt

// Zmienne stanu
const currentDate = ref(new Date());
const selectedDay = ref(null);
const selectedTime = ref(null);
const error = ref(null);

// Formatowanie daty do "YYYY-MM-DD"
const formatDate = (date) => date.toISOString().split("T")[0];

// Funkcja do pobrania dni w miesiącu
const getDaysInMonth = (date) => {
    const days = [];
    const year = date.getFullYear();
    const month = date.getMonth();

    const firstDayOfMonth = new Date(year, month, 1);
    const startDate = new Date(firstDayOfMonth);
    startDate.setDate(firstDayOfMonth.getDate() - (firstDayOfMonth.getDay() || 7) + 1);

    for (let i = 0; i < 42; i++) {
        const dayDate = new Date(startDate);
        dayDate.setDate(startDate.getDate() + i);
        const isCurrentMonth = dayDate.getMonth() === month;

        days.push({
            date: dayDate,
            isCurrentMonth,
        });
    }

    return days;
};

// Reaktywne dni w miesiącu
const days = computed(() => getDaysInMonth(currentDate.value));

// Nawigacja między miesiącami
const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
};

const goToToday = () => {
    currentDate.value = new Date();
};

// Otwórz modal rezerwacji
const openReservationModal = (day) => {
    const today = new Date();
    if (day.date < today.setHours(0, 0, 0, 0)) {
        alert("Nie możesz wybrać daty w przeszłości.");
        return;
    }

    selectedDay.value = formatDate(day.date);
    error.value = null;
};

// Rezerwacja wizyty
const bookAppointment = async () => {
    if (!selectedDay.value || !selectedTime.value) {
        error.value = "Proszę wybrać datę i godzinę.";
        return;
    }

    try {
        await axios.post("/reservations", {
            doctor_id: doctorId.value,
            reservation_date: selectedDay.value,
            reservation_time: selectedTime.value,
        });

        alert("Rezerwacja zakończona sukcesem!");
        selectedDay.value = null;
        selectedTime.value = null;
    } catch (err) {
        error.value = err.response?.data?.error || "Nie udało się zarezerwować terminu.";
    }
};

// Sprawdzanie zajętych slotów
const calculateSlotAvailability = (date) => {
    const dateKey = formatDate(date);

    if (timeSlots.value[dateKey]) {
        const reservedSlots = page.props.reservations?.[dateKey] || [];
        const availableSlots = timeSlots.value[dateKey].filter(
            (time) => !reservedSlots.includes(time)
        );

        return {
            totalSlots: timeSlots.value[dateKey].length,
            reservedSlots: reservedSlots.length,
            availableSlots
        };
    }

    return { totalSlots: 0, reservedSlots: 0, availableSlots: [] };
};


</script>

<template>
    <AppLayout :title="`Kalendarz lekarza - ${doctorName}`">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg">
                    <!-- Nagłówek -->
                    <header class="flex justify-between items-center border-b px-6 py-4 bg-gray-100">
                        <h1 class="text-lg font-bold">{{ doctorName }}</h1>
                        <div class="flex space-x-4">
                            <button @click="previousMonth" class="btn">Poprzedni miesiąc</button>
                            <button @click="goToToday" class="btn">Dziś</button>
                            <button @click="nextMonth" class="btn">Następny miesiąc</button>
                        </div>
                    </header>

                    <!-- Siatka kalendarza -->
                    <div class="grid grid-cols-7 gap-2 p-6">
                        <!-- Nagłówki dni tygodnia -->
                        <div
                            v-for="day in ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'Sb', 'Nd']"
                            :key="day"
                            class="text-center font-semibold"
                        >
                            {{ day }}
                        </div>

                        <!-- Dni miesiąca -->
                        <div
                            v-for="day in days"
                            :key="day.date"
                            class="p-4 border rounded-lg cursor-pointer"
                            :class="{
                                'bg-blue-100': day.isCurrentMonth && calculateSlotAvailability(day.date).availableSlots.length > 0,
                                'bg-gray-100 text-gray-400': !day.isCurrentMonth,
                                'cursor-not-allowed': day.date < new Date().setHours(0, 0, 0, 0),
                            }"
                            @click="openReservationModal(day)"
                        >
                            <span class="block">{{ day.date.getDate() }}</span>

                            <!-- Wyświetlanie liczby wolnych i zajętych terminów -->
                            <div v-if="timeSlots[formatDate(day.date)]" class="text-sm mt-2">
                                <p>Dostępne: {{ calculateSlotAvailability(day.date).availableSlots.length - calculateSlotAvailability(day.date).reservedSlots}}</p>
                                <p>Zajęte: {{ calculateSlotAvailability(day.date).reservedSlots }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal rezerwacji -->
        <div v-if="selectedDay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg">
                <h2 class="text-xl font-bold mb-4">Zarezerwuj wizytę</h2>
                <p>Data: {{ selectedDay }}</p>

                <!-- W modalu pokaż tylko dostępne godziny -->
                <div v-if="calculateSlotAvailability(new Date(selectedDay)).availableSlots.length > 0">
                    <label for="time" class="block font-semibold">Wybierz godzinę:</label>
                    <select v-model="selectedTime" id="time" class="border rounded w-full">
                        <option
                            v-for="time in calculateSlotAvailability(new Date(selectedDay)).availableSlots"
                            :key="time"
                            :value="time"
                        >
                            {{ time }}
                        </option>
                    </select>
                </div>
                <div v-else>
                    <p>Brak dostępnych godzin dla wybranego dnia.</p>
                </div>

                <div v-if="error" class="text-red-500 mt-2">{{ error }}</div>
                <div class="flex justify-end mt-4">
                    <button class="btn bg-gray-300" @click="selectedDay = null">Anuluj</button>
                    <button class="btn bg-blue-500 text-white" @click="bookAppointment">Zarezerwuj</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
