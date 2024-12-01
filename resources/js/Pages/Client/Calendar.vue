<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import Modal from "@/Components/Modal.vue";

const page = usePage();
const doctorId = computed(() => page.props.doctorId);
const doctorName = computed(() => page.props.doctorName);
const timeSlots = computed(() => page.props.timeSlots || {});

const currentDate = ref(new Date());
const selectedDay = ref(null);
const selectedTime = ref(null);
const error = ref(null);
const reservationSuccessModal = ref(false);
const reservedDetails = ref({
    date: null,
    time: null,
});

// Format date to YYYY-MM-DD
const formatDate = (date) => date.toISOString().split("T")[0];

// Function to get days in a month
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

const days = computed(() => getDaysInMonth(currentDate.value));

// Navigation between months
const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
};
const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
};
const goToToday = () => {
    currentDate.value = new Date();
};

const openReservationModal = (day) => {
    const today = new Date();
    if (day.date < today.setHours(0, 0, 0, 0)) {
        alert("Nie możesz wybrać daty w przeszłości.");
        return;
    }

    selectedDay.value = formatDate(day.date);
    error.value = null;
};

// Booking an appointment
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

        reservedDetails.value = {
            date: selectedDay.value,
            time: selectedTime.value,
        };

        reservationSuccessModal.value = true;

        selectedDay.value = null;
        selectedTime.value = null;
    } catch (err) {
        error.value = err.response?.data?.error || "Nie udało się zarezerwować terminu.";
    }
};

// Checking reserved slots
const calculateSlotAvailability = (date) => {
    const dateKey = formatDate(date);

    const reservedSlots = (page.props.reservations?.[dateKey] || []).map((time) => time.slice(0, 5));
    const availableSlots = timeSlots.value?.[dateKey] || [];

    return {
        totalSlots: availableSlots.length,
        reservedSlots: reservedSlots.length,
        availableSlots: availableSlots.filter((time) => !reservedSlots.includes(time)),
    };
};
</script>

<template>
    <AppLayout :title="`Kalendarz lekarza - ${doctorName}`">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-900 dark:text-gray-100 overflow-hidden shadow-lg rounded-lg">
                    <!-- Nagłówek -->
                    <header class="flex justify-between items-center border-b px-6 py-4 bg-gray-100 dark:bg-gray-800">
                        <h1 class="text-lg font-bold">{{ doctorName }}</h1>
                        <div class="flex space-x-4">
                            <button @click="previousMonth" class="btn cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-900 ring-1 ring-black ring-opacity-5">Poprzedni miesiąc</button>
                            <button @click="goToToday" class="btn cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-900">Dziś</button>
                            <button @click="nextMonth" class="btn cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-900">Następny miesiąc</button>
                        </div>
                    </header>

                    <!-- Calendar grid -->
                    <div class="grid grid-cols-7 gap-2 p-6 text-black">
                        <!-- Weekday headers -->
                        <div
                            v-for="day in ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'Sb', 'Nd']"
                            :key="day"
                            class="text-center font-semibold dark:text-white"
                        >
                            {{ day }}
                        </div>

                        <!-- Days of the month -->
                        <div
                            v-for="day in days"
                            :key="day.date"
                            class="p-4 border rounded-lg cursor-pointer h-28"
                            :class="{
                                'bg-green-100 hover:bg-green-200': day.isCurrentMonth && calculateSlotAvailability(day.date).availableSlots.length > 0,
                                'bg-red-100 hover:bg-red-200': day.isCurrentMonth && calculateSlotAvailability(day.date).availableSlots.length === 0,
                                'bg-gray-100 text-gray-400': !day.isCurrentMonth,
                                'cursor-not-allowed': day.date < new Date().setHours(0, 0, 0, 0),
                                'border-blue-500 border-4': selectedDay === formatDate(day.date)
                            }"
                            @click="openReservationModal(day)"
                        >
                            <span class="block">{{ day.date.getDate() }}</span>

                            <!-- Display the number of available and reserved slots -->
                            <div v-if="calculateSlotAvailability(day.date).totalSlots > 0" class="text-sm mt-2">
                                <p class="text-green-800">
                                    Dostępne: {{ calculateSlotAvailability(day.date).availableSlots.length }}
                                </p>
                                <p class="text-red-800">Zajęte: {{ calculateSlotAvailability(day.date).reservedSlots }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <Modal
            :show="reservationSuccessModal"
            maxWidth="sm"
            closeable
            @close="reservationSuccessModal = false"
        >
            <template v-slot>
                <div class="p-6 dark:text-white">
                    <h2 class="text-xl font-bold mb-4">Rezerwacja zakończona sukcesem!</h2>
                    <p>Data: {{ reservedDetails.date }}</p>
                    <p>Godzina: {{ reservedDetails.time }}</p>
                    <div class="flex justify-end mt-4">
                        <button
                            class="btn bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600"
                            @click="reservationSuccessModal = false"
                        >
                            OK
                        </button>
                    </div>
                </div>
            </template>
        </Modal>


        <!-- Reservation modal -->
        <Modal
            :show="selectedDay !== null"
            maxWidth="lg"
            closeable
            @close="selectedDay = null"
        >
            <template v-slot>
                <div class="p-6 dark:text-white">
                    <h2 class="text-xl font-bold mb-4">Zarezerwuj wizytę</h2>
                    <p>Data: {{ selectedDay }}</p>

                    <!-- Show available times -->
                    <div v-if="timeSlots[selectedDay]?.length" class="grid grid-cols-3 gap-2 mt-4">
                        <button
                            v-for="time in timeSlots[selectedDay]"
                            :key="time"
                            :class="{
                        'bg-green-200 text-green-800 rounded p-2 cursor-pointer hover:bg-green-300': !page.props.reservations[selectedDay]?.includes(time),
                        'bg-red-200 text-red-800 rounded p-2 cursor-not-allowed': page.props.reservations[selectedDay]?.map(t => t.slice(0, 5)).includes(time),
                        'border-blue-500 border-2': selectedTime === time
                    }"
                            :disabled="page.props.reservations[selectedDay]?.includes(time)"
                            @click="!page.props.reservations[selectedDay]?.includes(time) && (selectedTime = time)"
                        >
                            {{ time }}
                        </button>
                    </div>
                    <div v-else>
                        <p>Brak dostępnych godzin dla wybranego dnia.</p>
                    </div>

                    <div v-if="error" class="text-red-500 mt-2">{{ error }}</div>
                    <div class="flex justify-end mt-4">
                        <button
                            class="btn bg-gray-300 px-4 py-2 mr-2 rounded shadow hover:bg-gray-400 dark:bg-gray-500"
                            @click="selectedDay = null"
                        >
                            Anuluj
                        </button>
                        <button
                            class="btn bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600"
                            @click="bookAppointment"
                            :disabled="!selectedTime"
                        >
                            Zarezerwuj
                        </button>
                    </div>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>
