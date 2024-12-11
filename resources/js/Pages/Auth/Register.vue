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
import LanguageSwitcher from "@/Components/LanguageSwitcher.vue";

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const { t } = useI18n();
</script>

<template>
    <Head :title="t('register')" />

    <div class="h-screen bg-gray-800 flex items-center justify-center">
        <div class="flex w-4/5 p-8 rounded-lg">
            <div class="w-1/2 text-white px-6 flex flex-col justify-center">
                <div>
                    <h2 class="text-4xl font-bold mb-6">{{ t('join_system') }}</h2>
                    <p class="text-lg mb-4">
                        {{ t('join_system_description') }}
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-lg">
                        <li>{{ t('feature_registration') }}</li>
                        <li>{{ t('feature_history') }}</li>
                        <li>{{ t('feature_security') }}</li>
                    </ul>
                </div>
            </div>

            <div class="w-1/2 flex justify-center">
                <AuthenticationCard class="w-full max-w-md p-8 bg-white shadow-xl">
                    <template #logo>
                        <AuthenticationCardLogo />
                    </template>

                    <h1 class="text-4xl font-bold text-center text-indigo-700 dark:text-indigo-300 mb-6">
                        {{ t('register') }}
                    </h1>

                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel for="name" value="Imię i nazwisko" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password" value="Hasło" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password_confirmation" value="Potwierdź hasło" />
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>

                        <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                            <InputLabel for="terms">
                                <div class="flex items-center">
                                    <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />
                                    <div class="ms-2">
                                        {{ t('agree_terms') }}
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.terms" />
                            </InputLabel>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <Link :href="route('login')" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ t('already_registered') }}
                            </Link>

                            <PrimaryButton class="ms-4 px-6 py-2 bg-indigo-600 text-white rounded-full shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ t('register') }}
                            </PrimaryButton>
                        </div>
                    </form>
                    <div class="pt-3 flex justify-center h-full">
                        <LanguageSwitcher />
                    </div>
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
