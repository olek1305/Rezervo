<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';

defineProps({
    title: String,
});

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Panel
                                </NavLink>
                                <NavLink :href="route('doctor.index')" :active="route().current('doctor.index')">
                                    Lekarzy
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'doctor'"
                                    :href="route('doctor.availability', { id: $page.props.auth.user.id })"
                                    :active="route().current('doctor.availability')"
                                >
                                    Kalendarz lekarza
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'admin'"
                                    :href="route('admin.doctors.index')"
                                    :active="route().current('admin.doctors.index')"
                                >
                                    Admin
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
                                                Notifications
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
                                            No notifications
                                        </div>
                                    </template>
                                </Dropdown>
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
                                            Manage Account
                                        </div>
                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>
                                        <div class="border-t border-gray-200 dark:border-gray-600" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">Log Out</DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
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
