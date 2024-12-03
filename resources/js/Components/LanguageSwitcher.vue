<template>
    <div class="language-switcher">
        <select
            id="language"
            class="block w-28 pl-3 pr-10 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
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

