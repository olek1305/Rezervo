<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useI18n } from "vue-i18n";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const { t } = useI18n();
</script>

<template>
    <Head :title="t('log_in')" />

    <div class="h-screen bg-gray-800 flex items-center justify-center">
        <div class="flex w-4/5 p-8 rounded-lg">
            <div class="w-1/2 text-white px-6 flex flex-col justify-center">
                <div>
                    <h2 class="text-4xl font-bold mb-6">Witaj w naszym systemie rezerwacji!</h2>
                </div>
            </div>

            <div class="w-1/2 flex justify-center">
                <AuthenticationCard class="w-full max-w-md p-8 bg-white shadow-xl">
                    <template #logo>
                        <AuthenticationCardLogo />
                    </template>

                    <h1 class="text-4xl font-bold text-center text-indigo-700 dark:text-indigo-300 mb-6">
                        {{ t('login') }}
                    </h1>

                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password" value="Password" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                                autocomplete="current-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="block mt-4">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="form.remember" name="remember" />
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ t('remember_me') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ t('forgot_password') }}
                            </Link>

                            <Link :href="route('register')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ t('register') }}
                            </Link>

                            <PrimaryButton class="ms-4 px-6 py-2 bg-indigo-600 text-white rounded-full shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ t('log_in') }}
                            </PrimaryButton>
                        </div>
                    </form>
                </AuthenticationCard>
            </div>
        </div>
    </div>
</template>

<style>
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    overflow: hidden;
}
</style>
