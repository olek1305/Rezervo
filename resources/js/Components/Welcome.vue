<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link} from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-gradient-to-r from-teal-100 to-teal-300 dark:from-teal-800 dark:to-teal-900 text-white flex flex-col items-center">
    <!-- Sticky Navbar -->
    <nav class="w-full fixed top-0 left-0 bg-gradient-to-r from-teal-500 via-teal-600 to-teal-700 dark:from-teal-700 dark:via-teal-800 dark:to-teal-900 text-white shadow-lg z-10">

    </nav>

    <!-- Hero Section -->
    <section class="w-full pt-24 pb-16 bg-white dark:bg-gray-900 text-center">
      <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white animate__animated animate__fadeIn">
        System rezerwacji
      </h1>
      <p class="mt-6 text-xl sm:text-2xl text-gray-600 dark:text-gray-300 opacity-90 animate__animated animate__fadeIn animate__delay-1s">
        Witaj w systemie rezerwacji!
      </p>
      <a href="#features" class="mt-8 inline-block bg-teal-600 hover:bg-teal-500 py-4 px-8 rounded-xl text-lg font-semibold text-white transition-all transform hover:scale-105">
       
        <Link :href="route('doctor.index')">Rozpocznij teraz</Link>
      </a>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50 dark:bg-gray-800 w-full">
      <div class="max-w-7xl mx-auto px-6 sm:px-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-center text-gray-900 dark:text-white">
          Co oferujemy?
        </h2>
        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12">
          <!-- Feature 1 -->
          <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-xl p-8 transition-all transform hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-teal-600 dark:text-teal-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m0-2l-4 2v6m0-6h4m-4-6H8" />
            </svg>
            <h3 class="text-xl font-semibold text-center text-gray-800 dark:text-white">Łatwe rezerwacje</h3>
            <p class="mt-4 text-center text-gray-500 dark:text-gray-400">
              Prosty i intuicyjny system do zarządzania rezerwacjami online.
            </p>
          </div>

          <!-- Feature 2 -->
          <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-xl p-8 transition-all transform hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-teal-600 dark:text-teal-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.5-4.5m-4.5 4.5V3m0 9l4.5 4.5" />
            </svg>
            <h3 class="text-xl font-semibold text-center text-gray-800 dark:text-white">Powiadomienia</h3>
            <p class="mt-4 text-center text-gray-500 dark:text-gray-400">
              Automatyczne powiadomienia o nadchodzących rezerwacjach.
            </p>
          </div>

          <!-- Feature 3 -->
          <div class="bg-white dark:bg-gray-700 rounded-2xl shadow-xl p-8 transition-all transform hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-teal-600 dark:text-teal-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6m6 6h-6m6-12h-6" />
            </svg>
            <h3 class="text-xl font-semibold text-center text-gray-800 dark:text-white">Integracja z kalendarzem</h3>
            <p class="mt-4 text-center text-gray-500 dark:text-gray-400">
              Synchronizacja z kalendarzami, aby uniknąć podwójnych rezerwacji.
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
  /* Smooth scroll behavior */
  html {
    scroll-behavior: smooth;
  }

  /* Sticky navbar */
  nav {
    position: sticky;
    top: 0;
    z-index: 100;
  }

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

  /* Smooth background colors */
  .bg-teal-600 {
    background-color: #2d3748;
  }
  .bg-teal-700 {
    background-color: #1a202c;
  }
  .bg-teal-800 {
    background-color: #2d3748;
  }

  /* Adjustments for dark mode */
  @media (prefers-color-scheme: dark) {
    .bg-gradient-to-r {
      background: linear-gradient(to right, #1a202c, #2d3748);
    }
  }
</style>
