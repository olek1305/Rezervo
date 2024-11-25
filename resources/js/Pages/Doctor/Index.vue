<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import {computed} from 'vue';
import {usePage} from '@inertiajs/vue3';

const page = usePage();
const doctors = computed(() => page.props.doctors);

const goToDoctorCalendar = (doctorId) => {
    window.location.href = `/doctor/${doctorId}/calendar`;
};
</script>

<template>
    <AppLayout title="Wybierz lekarza">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg">
                    <h1>Wybierz lekarza</h1>
                    <ul>
                        <li v-for="doctor in doctors" :key="doctor.id">
                            <button @click="goToDoctorCalendar(doctor.id)">
                                {{ doctor.name }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
