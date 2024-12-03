<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const page = usePage();
const doctors = computed(() => page.props.doctors);

const goToDoctorCalendar = (doctorId) => {
    window.location.href = `/doctor/${doctorId}/calendar`;
};

const {t} = useI18n();
</script>


<template>
    <AppLayout :title="t('choose_doctor')">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-center text-gray-800 dark:text-gray-100">
                        {{ t('choose_doctor') }}
                    </h1>
                    <ul class="space-y-4">
                        <li
                            v-for="doctor in doctors"
                            :key="doctor.id"
                            class="flex justify-between items-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition-shadow"
                        >
                            <div class="flex flex-col">
                                <span class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                    {{ doctor.name }}
                                </span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ t('doctor_specialization') }}: {{ doctor.specialization }}
                                </span>
                            </div>
                            <button
                                @click="goToDoctorCalendar(doctor.id)"
                                class="px-6 py-2 bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 transition-colors"
                            >
                                {{ t('select') }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
