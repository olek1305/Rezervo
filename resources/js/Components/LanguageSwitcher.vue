<template>
    <div class="language-switcher">
        <select
            id="language"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-400 w-24"
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

