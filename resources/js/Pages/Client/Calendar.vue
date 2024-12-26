<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {ref, computed, onMounted} from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import Modal from "@/Components/Modal.vue";
import {useI18n} from "vue-i18n";

const page = usePage();
const doctorId = computed(() => page.props.doctorId);
const doctorName = computed(() => page.props.doctorName);

const reservations = ref([]);
const isLoading = ref(false);
const currentDate = ref(new Date());
const selectedDay = ref(null);
const selectedTime = ref(null);
const error = ref(null);
const reservationSuccessModal = ref(false);
const reservedDetails = ref({
    date: null,
    time: null,
});
const timeSlots = computed(() => page.props.timeSlots || {});

// Format date to YYYY-MM-DD
const formatDate = (date) => {
    const localOffset = new Date(date).getTimezoneOffset() * 60000;
    const adjustedDate = new Date(date.getTime() - localOffset);
    return adjustedDate.toISOString().split('T')[0];
}

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
        alert(t('invalid_date'));
        return;
    }

    selectedDay.value = formatDate(day.date);
    error.value = null;
};

// Booking an appointment
const bookAppointment = async () => {
    if (!selectedDay.value || !selectedTime.value) {
        error.value = t('choose_date_and_time');
        return;
    }

    isLoading.value = true;

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
        error.value = err.response?.data?.error || (t('reservation_failed'));
    } finally {
        isLoading.value = false;
        setTimeout(() => {
            window.location.reload();
        }, 2100);
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

// Cancel an appointment
const cancelReservation = async (reservationId) => {
    if (!confirm(t('confirm_action'))) return;

    isLoading.value = true;

    try {
        await axios.delete(`/reservations/${reservationId}`);
        // Remove the canceled reservation from the list
        reservations.value = reservations.value.filter((r) => r.id !== reservationId);
        alert(t('cancel_appointment'));

    } catch (error) {
        console.error(t('reservation_failed'), error);
        alert(t('reservation_failed'));
    } finally {
        isLoading.value = false;
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
};

const fetchReservations = async () => {
    try {
        const response = await axios.get('/reservations');
        reservations.value = response.data.reservations;
    } catch (error) {
        console.error(t('reservation_failed'), error);
    }
};

onMounted(fetchReservations);

const { t } = useI18n();
</script>

<template>
    <AppLayout :title="t('doctor_calendar_title', { doctorName })">
        <div class="py-4">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-900 dark:text-gray-100 overflow-hidden shadow-lg rounded-lg">
                    <!-- Header -->
                    <header class="flex flex-col sm:flex-row justify-between items-center border-b px-4 sm:px-6 py-4 bg-gray-100 dark:bg-gray-800 space-y-2 sm:space-y-0">
                        <h1 class="text-base sm:text-lg font-bold">{{ doctorName }}</h1>
                        <div class="flex space-x-4">
                            <button @click="previousMonth" class="btn cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-900 ring-1 ring-black ring-opacity-5 text-xs sm:text-sm">
                                {{ t('previous_month') }}
                            </button>
                            <button @click="goToToday" class="btn cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-900 text-xs sm:text-sm">
                                {{ t('today') }}
                            </button>
                            <button @click="nextMonth" class="btn cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-900 text-xs sm:text-sm">
                                {{ t('next_month') }}
                            </button>
                        </div>
                    </header>

                    <!-- Calendar grid -->
                    <div class="p-4 sm:p-6 overflow-x-auto">
                        <div class="grid grid-cols-7 gap-2 text-black min-w-[600px]">
                            <!-- Weekday headers -->
                            <div
                                v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']"
                                :key="day"
                                class="text-center font-semibold text-[10px] md:text-[9px] lg:text-sm dark:text-white">
                                {{ day }}
                            </div>

                            <!-- Days of the month -->
                            <div
                                v-for="day in days"
                                :key="day.date"
                                class="relative p-1 md:p-2 lg:p-4 border rounded-lg cursor-pointer h-16 sm:h-20 flex flex-col"
                                :class="{
                                    'bg-green-100 hover:bg-green-200': day.isCurrentMonth && calculateSlotAvailability(day.date).availableSlots.length > 0,
                                    'bg-red-100 hover:bg-red-200': day.isCurrentMonth && calculateSlotAvailability(day.date).availableSlots.length === 0,
                                    'bg-gray-100 text-gray-400': !day.isCurrentMonth,
                                    'cursor-not-allowed': day.date < new Date().setHours(0, 0, 0, 0),
                                    'border-blue-500 border-4': selectedDay === formatDate(day.date)
                                    }"
                                    @click="openReservationModal(day)"
                                    >
                                <!-- Date number -->
                                <span class="absolute top-1 left-1 text-[14px] font-semibold truncate z-10">
                                    {{ day.date.getDate() }}
                                </span>

                                <!-- Availability info -->
                                <div v-if="calculateSlotAvailability(day.date).totalSlots > 0" class="absolute bottom-1 right-1 text-[11px] md:text-[12px] text-right z-0">
                                    <p class="text-green-800 truncate">
                                        {{ t('available') }}: {{ calculateSlotAvailability(day.date).availableSlots.length }}
                                    </p>
                                    <p class="text-red-800 truncate">
                                        {{ t('reserved') }}: {{ calculateSlotAvailability(day.date).reservedSlots }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User's reservations -->
                <div v-if="reservations.length">
                    <ul>
                        <li
                            v-for="reservation in reservations"
                            :key="reservation.id"
                            class="flex justify-between items-center p-4 bg-white dark:bg-gray-700 dark:text-white rounded mb-2"
                        >
                            <div>
                                <p>
                                    <strong>{{ t('date') }}:</strong> {{ reservation.reservation_date }}
                                </p>
                                <p>
                                    <strong>{{ t('time') }}:</strong> {{ reservation.reservation_time }}
                                </p>
                                <p>
                                    <strong>{{ t('doctor') }}:</strong> {{ reservation.doctor.name }}
                                </p>
                            </div>
                            <button
                                class="btn bg-red-500 text-white px-4 py-2 rounded shadow hover:bg-red-600 max-w-32"
                                :disabled="isLoading"
                                @click="cancelReservation(reservation.id)"
                            >
                                <span v-if="isLoading">{{ t('loading') }}</span>
                                <span v-else>{{ t('cancel_appointment') }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div v-else>
                    <p class="dark:text-white text-center">{{ t('no_appointments') }}</p>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <Modal :show="reservationSuccessModal" maxWidth="sm" closeable @close="reservationSuccessModal = false">
            <template v-slot>
                <div class="p-6 dark:text-white">
                    <h2 class="text-xl font-bold mb-4">{{ t('reservation_successful') }}</h2>
                    <p>{{ t('date') }}: {{ reservedDetails.date }}</p>
                    <p>{{ t('time') }}: {{ reservedDetails.time }}</p>
                    <div class="flex justify-end mt-4">
                        <button class="btn bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600" @click="reservationSuccessModal = false">
                            {{ t('confirm') }}
                        </button>
                    </div>
                </div>
            </template>
        </Modal>

        <!-- Reservation modal -->
        <Modal :show="selectedDay !== null" maxWidth="lg" closeable @close="selectedDay = null">
            <template v-slot>
                <div class="p-6 dark:text-white">
                    <h2 class="text-xl font-bold mb-4">{{ t('book_appointment') }}</h2>
                    <p>{{ t('date') }}: {{ selectedDay }}</p>

                    <!-- Show available times -->
                    <div v-if="timeSlots[selectedDay]?.length" class="grid grid-cols-3 gap-2 mt-4">
                        <button
                            v-for="time in timeSlots[selectedDay]"
                            :key="time"
                            :class="{
                                'bg-green-200 text-green-800 rounded p-2 cursor-pointer hover:bg-green-300': !page.props.reservations[selectedDay]?.includes(time),
                                'bg-red-200 text-red-800 rounded p-2 cursor-not-allowed': page.props.reservations[selectedDay]?.map(time => time.slice(0, 5)).includes(time),
                                'border-blue-500 border-2': selectedTime === time
                            }"
                            :disabled="page.props.reservations[selectedDay]?.includes(time)"
                            @click="!page.props.reservations[selectedDay]?.includes(time) && (selectedTime = time)"
                        >
                            {{ time }}
                        </button>
                    </div>
                    <div v-else>
                        <p>{{ t('no_available_times') }}</p>
                    </div>

                    <div v-if="error" class="text-red-500 mt-2">{{ error }}</div>
                    <div class="flex justify-end mt-4">
                        <button class="btn bg-gray-300 px-4 py-2 mr-2 rounded shadow hover:bg-gray-400 dark:bg-gray-500" @click="selectedDay = null">
                            {{ t('cancel') }}
                        </button>
                        <button
                            class="btn bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600"
                            @click="bookAppointment"
                            :disabled="!selectedTime || isLoading"
                        >
                            <span v-if="isLoading">{{ t('loading') }}</span>
                            <span v-else>{{ t('reserve') }}</span>
                        </button>
                    </div>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>

