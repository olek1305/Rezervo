<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import LanguageSwitcher from "@/Components/LanguageSwitcher.vue";
import { useI18n } from 'vue-i18n';
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import {ref} from "vue";

defineProps({
    title: String,
});
const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const { t } = useI18n();
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    {{ t('dashboard') }}
                                </NavLink>
                                <NavLink :href="route('doctor.index')" :active="route().current('doctor.index')">
                                    {{ t('specialists') }}
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'doctor'"
                                    :href="route('doctor.availability', { id: $page.props.auth.user.id })"
                                    :active="route().current('doctor.availability')"
                                >
                                    {{ t('doctor_calendar') }}
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'admin'"
                                    :href="route('admin.doctors.index')"
                                    :active="route().current('admin.doctors.index')"
                                >
                                    {{ t('admin') }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Notifications Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button class="relative">
                                            <span class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border rounded-lg dark:bg-gray-800 dark:text-gray-400">
                                                {{ t('notifications') }}
                                            </span>
                                            <span
                                                v-if="$page.props.notifications && $page.props.notifications.length"
                                                class="absolute top-0 right-0 block h-2 w-2 bg-red-500 rounded-full ring-2 ring-white dark:ring-gray-800"
                                            ></span>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div v-if="$page.props.notifications && $page.props.notifications.length" class="divide-y divide-gray-200 dark:divide-gray-700">
                                            <div
                                                v-for="notification in $page.props.notifications.slice(0, 5)"
                                                :key="notification.id"
                                                class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300"
                                            >
                                                <p>{{ notification.data.message }}</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                                    {{ new Date(notification.created_at).toLocaleString() }}
                                                </p>
                                            </div>
                                        </div>
                                        <div v-else class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('no_notifications') }}
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <div class="ms-3 relative">
                                <LanguageSwitcher />
                            </div>

                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            {{ t('manage_account') }}
                                        </div>
                                        <DropdownLink :href="route('profile.show')">
                                            {{ t('profile') }}
                                        </DropdownLink>
                                        <div class="border-t border-gray-200 dark:border-gray-600" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">{{ t('log_out') }}</DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                        <!-- Responsive Navigation Toggle Button -->
                        <div class="sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out"
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                            >
                                <svg
                                    class="size-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            {{ t('dashboard') }}
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="size-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" />
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                {{ t('profile') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('doctor.index')" :active="route().current('doctor.index')">
                                {{ t('specialists') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'doctor'"
                                :href="route('doctor.availability', { id: $page.props.auth.user.id })"
                                :active="route().current('doctor.availability')"
                            >
                                {{ t('doctor_calendar') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('admin.doctors.index')"
                                :active="route().current('admin.doctors.index')"
                            >
                                {{ t('admin') }}
                            </ResponsiveNavLink>
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    {{ t('log_out') }}
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
