<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, onMounted } from "vue";
import axios from "axios";

// Props passed to the component
const props = defineProps({
    user: Object,
});

// State variables
const currentUser = ref(props.user);
const currentDate = ref(new Date());
const selectedDay = ref(null);
const reservations = ref({});
const error = ref(null);

// Format a date to "YYYY-MM-DD"
const formatDate = (date) => {
    return date.toISOString().split("T")[0];
};

// Helper function to format the month and year for the header
const formatMonthYear = (date) => {
    return date.toLocaleString("default", { month: "long", year: "numeric" });
};

// Navigate to the previous month
const previousMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() - 1);
    currentDate.value = newDate;
};

// Navigate to the next month
const nextMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() + 1);
    currentDate.value = newDate;
};

// Navigate back to the current month
const goToToday = () => {
    currentDate.value = new Date();
};

// Get all days in the selected month including overflow days (previous and next month)
const getDaysInMonth = (date) => {
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDayOfMonth = new Date(year, month, 1);
    const lastDayOfMonth = new Date(year, month + 1, 0);
    const days = [];

    // Days before the first day of the current month
    const startDay = firstDayOfMonth.getDay() || 7; // Treat Sunday as the last day of the week
    for (let i = startDay - 1; i > 0; i--) {
        const prevDate = new Date(firstDayOfMonth);
        prevDate.setDate(prevDate.getDate() - i);
        days.push({ date: prevDate, isCurrentMonth: false });
    }

    // Days of the current month
    for (let i = 1; i <= lastDayOfMonth.getDate(); i++) {
        days.push({ date: new Date(year, month, i), isCurrentMonth: true });
    }

    // Days after the last day of the current month
    const endDay = lastDayOfMonth.getDay() || 7;
    for (let i = 1; i <= 7 - endDay; i++) {
        const nextDate = new Date(lastDayOfMonth);
        nextDate.setDate(nextDate.getDate() + i);
        days.push({ date: nextDate, isCurrentMonth: false });
    }

    return days;
};

// Reactive computed property for all days in the calendar grid
const days = computed(() => getDaysInMonth(currentDate.value));

// Open the reservation modal for a selected day
const openReservationModal = (day) => {
    selectedDay.value = day;
    error.value = null;
};

// Add a reservation for the selected day
const addReservation = async () => {
    const dayKey = formatDate(selectedDay.value);
    const todayKey = formatDate(new Date());

    // Prevent adding reservations for past days
    if (dayKey < todayKey) {
        error.value = "Reservations for past dates are not allowed.";
        return;
    }

    try {
        // Make a POST request to create a reservation
        const response = await axios.post("/reservations", {
            reservation_date: dayKey,
        });

        const newReservation = response.data; // API returns the newly created reservation
        if (!reservations.value[dayKey]) {
            reservations.value[dayKey] = [];
        }
        reservations.value[dayKey].push({
            id: newReservation.id,
            userId: currentUser.value.id,
            name: currentUser.value.name,
        });

        // Close the modal
        selectedDay.value = null;
    } catch (error) {
        error.value = "Failed to save the reservation.";
    }
};

// Fetch all reservations for the user
const fetchReservations = async () => {
    try {
        reservations.value = {}; // Reset the reservations object

        const response = await axios.get("/reservations");
        const fetchedReservations = response.data;

        // Organize reservations by date
        fetchedReservations.forEach((reservation) => {
            const dateKey = reservation.reservation_date;
            if (!reservations.value[dateKey]) {
                reservations.value[dateKey] = [];
            }
            reservations.value[dateKey].push({
                id: reservation.id,
                userId: reservation.user_id,
                name: reservation.user_name,
            });
        });
    } catch (error) {
        console.error("Error fetching reservations:", error);
    }
};

// Cancel a reservation
const cancelReservation = async (reservationId) => {
    if (!reservationId) {
        alert("Missing reservation ID. Cannot cancel.");
        return;
    }

    const confirmCancel = confirm("Are you sure you want to cancel this reservation?");
    if (!confirmCancel) {
        return;
    }

    try {
        // Make a DELETE request to cancel the reservation
        await axios.delete(`/reservations/${reservationId}`);

        // Remove the reservation from the local state
        for (const [date, reservationsForDate] of Object.entries(reservations.value)) {
            reservations.value[date] = reservationsForDate.filter(
                res => res.id !== reservationId
            );
        }

        // Close the modal
        selectedDay.value = null;
    } catch (error) {
        alert(error.response?.data?.error || "Failed to cancel the reservation.");
    }
};

// Fetch reservations when the component is mounted
onMounted(() => {
    fetchReservations();
});
</script>

<template>
    <AppLayout title="Dynamiczny Kalendarz z Rezerwacjami">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg">
                    <!-- Header -->
                    <header class="flex flex-col sm:flex-row items-center justify-between border-b border-gray-300 px-6 py-4 bg-gray-100">
                        <h1 class="text-base sm:text-lg font-bold text-gray-700">
                            <time :datetime="currentDate.toISOString()">{{ formatMonthYear(currentDate) }}</time>
                        </h1>
                        <div class="flex flex-wrap items-center space-x-2 sm:space-x-4 mt-2 sm:mt-0">
                            <button
                                @click="previousMonth"
                                class="px-3 py-2 bg-gray-700 text-white text-sm rounded hover:bg-gray-600"
                            >
                                Poprzedni miesiąc
                            </button>
                            <button
                                @click="goToToday"
                                class="px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-500"
                            >
                                Dziś
                            </button>
                            <button
                                @click="nextMonth"
                                class="px-3 py-2 bg-gray-700 text-white text-sm rounded hover:bg-gray-600"
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
                                'flex flex-col justify-between p-2 sm:p-4 border rounded-lg text-xs sm:text-sm h-20 sm:h-32',
                                day.isCurrentMonth ? 'bg-white text-gray-900' : 'bg-gray-200 text-gray-500',
                                day.date.toDateString() === new Date().toDateString() && 'bg-blue-500 text-white',
                                day.date.getDay() === 0 || day.date.getDay() === 6 ? 'bg-gray-100' : ''
                            ]"
                            @click="openReservationModal(day.date)"
                        >
                            <span class="font-bold">{{ day.date.getDate() }}</span>

                            <!-- Reservation list -->
                            <ul v-if="reservations[formatDate(day.date)]" class="mt-1 sm:mt-2 text-xs">
                                <li
                                    v-for="(reservation, index) in reservations[formatDate(day.date)]"
                                    :key="index"
                                    class="text-gray-700 flex justify-between items-center"
                                >
                                    <span>{{ reservation.name }}</span>
                                    <button
                                        v-if="reservation.userId === currentUser.id"
                                        @click.prevent="cancelReservation(reservation.id)"
                                        class="ml-2 text-red-500 hover:underline"
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

        <!-- Modal -->
        <div
            v-if="selectedDay"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white p-8 rounded-lg shadow-xl max-w-sm w-full">
                <h2 class="text-xl font-bold mb-4">Zarezerwuj wizytę</h2>
                <p class="mb-4">Data: {{ selectedDay.toDateString() }}</p>
                <p v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</p>
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
