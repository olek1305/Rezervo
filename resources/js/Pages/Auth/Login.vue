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

    <!-- Strona z gradientowym tłem -->
    <div class="min-h-screen bg-gray-800 flex items-center ">
        <!-- Sekcja po lewej stronie -->
        <div class="hidden lg:block w-1/3 text-white px-8 space-y-6">
            <h2 class="text-4xl font-bold">Witaj w naszym systemie rezerwacji!</h2>
            <p class="text-lg">
                Zarządzaj rezerwacjami szybko i wygodnie w jednym miejscu. Bezpieczny i nowoczesny system dostosowany do Twoich potrzeb.
            </p>
            <ul class="list-disc list-inside space-y-2 text-lg">
                <li>Intuicyjny interfejs</li>
                <li>Szybki dostęp do rezerwacji</li>
                <li>Zabezpieczone dane użytkowników</li>
            </ul>
        </div>

        <!-- Panel logowania -->
        <div class="flex justify-center lg:w-2/3">
            <AuthenticationCard class="max-w-sm w-full p-6 bg-white rounded-lg shadow-xl">
                <template #logo>
                    <AuthenticationCardLogo />
                </template>

                <!-- Tytuł strony -->
                <h1 class="text-4xl font-bold text-center text-indigo-700 dark:text-indigo-300 mb-6">
                    System rezerwacji
                </h1>

                <!-- Status -->
                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                    {{ status }}
                </div>

                <!-- Formularz logowania -->
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
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ t('Zapamiętaj mnie') }}</span>
                        </label>
                    </div>

                    <!-- Link do resetowania hasła oraz przycisk logowania -->
                    <div class="flex items-center justify-between mt-4">
                        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ t('forgot_password') }}
                        </Link>

                        <PrimaryButton class="ms-4 px-6 py-2 bg-indigo-600 text-white rounded-full shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ t('log_in') }}
                        </PrimaryButton>
                    </div>
                </form>
            </AuthenticationCard>
        </div>
    </div>
</template>

<style scoped>
/* Tło gradientowe */
body {
    font-family: 'Inter', sans-serif;
}

.min-h-screen {
    min-height: 100vh;
}

/* Tło zmienione na kolor zgodny z tytułem */
.bg-indigo-700 {
    background-color: #4C51BF; /* Zbliżony kolor */
}

/* Tytuł strony */
h1 {
    font-family: 'Poppins', sans-serif;
    font-size: 2.25rem;
    font-weight: 700;
    color: #4C51BF;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
