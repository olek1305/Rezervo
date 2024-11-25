<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

// Access props from Inertia
const page = usePage();
const doctorId = computed(() => page.props.doctorId);
const doctorName = computed(() => page.props.doctorName);
const calendarData = computed(() => page.props.calendar);

// State variables
const currentDate = ref(new Date());
const selectedDay = ref(null);
const reservations = ref({});
const error = ref(null);

// Format a date to "YYYY-MM-DD"
const formatDate = (date) => date.toISOString().split("T")[0];

// Helper function to format the month and year for the header
const formatMonthYear = (date) => date.toLocaleString("default", { month: "long", year: "numeric" });

// Function to get all days in the selected month
const getDaysInMonth = (date) => {
    const days = [];
    const year = date.getFullYear();
    const month = date.getMonth();

    // First day of the month
    const firstDayOfMonth = new Date(year, month, 1);
    // Last day of the month
    const lastDayOfMonth = new Date(year, month + 1, 0);

    // Calculate the day of the week the month starts on (0 = Sunday, 6 = Saturday)
    const startDay = firstDayOfMonth.getDay();
    const daysFromPrevMonth = startDay === 0 ? 6 : startDay - 1;

    // Total cells in the calendar grid (usually 6 weeks to cover all days)
    const totalCells = 42;

    // Start date for the calendar grid (might include days from the previous month)
    const startDate = new Date(firstDayOfMonth);
    startDate.setDate(firstDayOfMonth.getDate() - daysFromPrevMonth);

    for (let i = 0; i < totalCells; i++) {
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

// Reactive days in the month
const days = computed(() => getDaysInMonth(currentDate.value));

// Navigation functions
const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
};

const goToToday = () => {
    currentDate.value = new Date();
};

// Open reservation modal
const openReservationModal = (day) => {
    // Prevent selecting dates in the past
    const today = new Date();
    if (day.date < today.setHours(0, 0, 0, 0)) {
        alert("Cannot book appointments for past dates.");
        return;
    }

    selectedDay.value = day.date;
    error.value = null;
};

// Fetch reservations and availability
const fetchReservations = async () => {
    try {
        const response = await axios.get(`/doctor/${doctorId.value}/calendar`);

        const { availability } = response.data;

        reservations.value = availability.reduce((acc, slot) => {
            const dateKey = slot.available_date;

            if (!acc[dateKey]) {
                acc[dateKey] = [];
            }

            acc[dateKey].push({
                start: slot.start_time,
                end: slot.end_time,
            });

            return acc;
        }, {});
    } catch (err) {
        console.error("Error fetching reservations and availability:", err);
    }
};


// Add a reservation
const addReservation = async () => {
    const dayKey = formatDate(selectedDay.value);

    try {
        const response = await axios.post("/reservations", {
            doctor_id: doctorId.value,
            reservation_date: dayKey,
        });

        if (!reservations.value[dayKey]) reservations.value[dayKey] = [];
        reservations.value[dayKey].push(response.data);

        alert("Reservation added successfully!");
        selectedDay.value = null;
    } catch (err) {
        error.value = err.response?.data?.error || "Failed to save the reservation.";
    }
};

onMounted(() => {
    fetchReservations();
});
</script>

<template>
    <AppLayout :title="`Kalendarz lekarza - ${doctorName}`">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg">
                    <!-- Header -->
                    <header class="flex justify-between items-center border-b px-6 py-4 bg-gray-100">
                        <h1 class="text-lg font-bold">{{ doctorName }}</h1>
                        <div class="flex space-x-4">
                            <button @click="previousMonth" class="btn">Poprzedni miesiąc</button>
                            <button @click="goToToday" class="btn">Dziś</button>
                            <button @click="nextMonth" class="btn">Następny miesiąc</button>
                        </div>
                    </header>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-2 p-6">
                        <!-- Weekdays -->
                        <div
                            v-for="day in ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'Sb', 'Nd']"
                            :key="day"
                            class="text-center font-semibold"
                        >
                            {{ day }}
                        </div>

                        <!-- Days in the month -->
                        <div
                            v-for="day in days"
                            :key="day.date"
                            class="p-4 border rounded-lg cursor-pointer"
                            :class="{
                                'bg-blue-100': day.isCurrentMonth && reservations[formatDate(day.date)],
                                'bg-gray-100 text-gray-400': !day.isCurrentMonth,
                                'cursor-not-allowed': day.date < new Date().setHours(0, 0, 0, 0),
                            }"
                            @click="openReservationModal(day)"
                        >
                            <span class="block">{{ day.date.getDate() }}</span>

                            <!-- Display reservations -->
                            <ul v-if="reservations[formatDate(day.date)]" class="mt-2">
                                <li v-for="res in reservations[formatDate(day.date)]" :key="res.id">
                                    {{ res.time || "Zarezerwowane" }}
                                    <button
                                        v-if="res.user_id === page.props.auth.user.id"
                                        @click.stop="cancelReservation(res.id)"
                                        class="text-red-500 ml-2"
                                    >
                                        Anuluj
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservation Modal -->
        <div
            v-if="selectedDay"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
        >
            <div class="bg-white p-8 rounded-lg">
                <h2 class="text-xl font-bold mb-4">Zarezerwuj wizytę</h2>
                <p>Data: {{ selectedDay.toDateString() }}</p>
                <div v-if="error" class="text-red-500">{{ error }}</div>
                <div class="flex space-x-4">
                    <button @click="selectedDay = null" class="btn">Anuluj</button>
                    <button @click="addReservation" class="btn btn-primary">Potwierdź</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
