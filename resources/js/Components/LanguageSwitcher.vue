<template>
    <div class="language-switcher">
        <select
            id="language"
            class="inline-flex items-center px-4 py-2 w-28 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition ease-in-out duration-150"
            @change="changeLanguage($event)"
        >
            <option value="en" :selected="locale === 'en'">English</option>
            <option value="pl" :selected="locale === 'pl'">Polski</option>
        </select>
    </div>
</template>


<script setup>
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const changeLanguage = (event) => {
    const selectedLanguage = event.target.value;
    locale.value = selectedLanguage;
    localStorage.setItem('lang', selectedLanguage);
};
</script>

