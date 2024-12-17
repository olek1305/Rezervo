<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>

<template>
    <div class="text-white flex flex-col items-center">
        <!-- Header Section -->
        <section class="w-full pt-24 pb-16 bg-white dark:bg-gray-900 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white animate__animated animate__fadeIn">
                {{ t('system_title') }}
            </h1>
            <p class="mt-6 text-xl sm:text-2xl text-gray-600 dark:text-gray-300 opacity-90 animate__animated animate__fadeIn animate__delay-1s">
                {{ t('system_welcome') }}
            </p>
            <button class="mt-8 inline-block bg-teal-600 hover:bg-teal-500 py-4 px-8 rounded-xl text-lg font-semibold text-white transition-all transform hover:scale-105">
                <Link :href="route('doctor.index')">{{ t('start_now') }}</Link>
            </button>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-gray-50 dark:bg-gray-800 w-full">
            <div class="max-w-7xl mx-auto px-6 sm:px-8">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-center text-gray-900 dark:text-white">
                    {{ t('what_we_offer') }}
                </h2>
                <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12">
                    <!-- Feature 1 -->
                    <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-xl p-8 transition-all transform hover:scale-105">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-teal-600 dark:text-teal-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m0-2l-4 2v6m0-6h4m-4-6H8" />
                        </svg>
                        <h3 class="text-xl font-semibold text-center text-gray-800 dark:text-white">{{ t('easy_reservations') }}</h3>
                        <p class="mt-4 text-center text-gray-500 dark:text-gray-400">
                            {{ t('easy_reservations_desc') }}
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-xl p-8 transition-all transform hover:scale-105">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-teal-600 dark:text-teal-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.5-4.5m-4.5 4.5V3m0 9l4.5 4.5" />
                        </svg>
                        <h3 class="text-xl font-semibold text-center text-gray-800 dark:text-white">{{ t('notifications') }}</h3>
                        <p class="mt-4 text-center text-gray-500 dark:text-gray-400">
                            {{ t('notifications_desc') }}
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-xl p-8 transition-all transform hover:scale-105">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-teal-600 dark:text-teal-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6m6 6h-6m6-12h-6" />
                        </svg>
                        <h3 class="text-xl font-semibold text-center text-gray-800 dark:text-white">{{ t('calendar_integration') }}</h3>
                        <p class="mt-4 text-center text-gray-500 dark:text-gray-400">
                            {{ t('calendar_integration_desc') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
  /* Animation classes for fade-in */
  .animate__animated {
    animation-duration: 1s;
    animation-fill-mode: both;
  }

  .animate__fadeIn {
    animation-name: fadeIn;
  }

  /* Hover and scaling effect */
  .transition-all {
    transition: all 0.3s ease;
  }

  .hover\:scale-105:hover {
    transform: scale(1.05);
  }
</style>
