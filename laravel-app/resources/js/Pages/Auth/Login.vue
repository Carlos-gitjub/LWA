<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
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
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600"
                        >Remember me</span
                    >
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>

        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-gray-300"></div>
            <span class="mx-4 text-gray-500">or</span>
            <div class="flex-grow border-t border-gray-300"></div>
        </div>

        <button
            type="button"
            @click="router.visit('/auth/google')"
            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M21.35 11.1H12v2.8h5.4c-.2 1.5-1.6 4.3-5.4 4.3-3.2 0-5.9-2.7-5.9-6s2.7-6 5.9-6c1.8 0 3 .7 3.7 1.3l2.5-2.4C17.5 3.9 15 3 12 3 6.5 3 2 7.5 2 13s4.5 10 10 10c5.7 0 9.5-4 9.5-9.5 0-.7-.1-1.3-.2-1.9z"/>
                <path fill="#34A853" d="M12 22c2.7 0 5-1 6.7-2.7l-3.1-2.5c-.8.5-1.8.8-3.6.8-2.8 0-5.2-1.9-6-4.5H2.1v2.8C3.8 20.1 7.6 22 12 22z"/>
                <path fill="#FBBC05" d="M5.9 13.6c-.2-.5-.3-1-.3-1.6s.1-1.1.3-1.6V7.6H2.1C1.4 9 1 10.5 1 12s.4 3 1.1 4.4l3.8-2.8z"/>
                <path fill="#EA4335" d="M12 6.1c1.5 0 2.8.5 3.7 1.3l2.8-2.7C17.5 3.9 15 3 12 3 7.6 3 3.8 4.9 2.1 7.6l3.8 2.8c.8-2.6 3.2-4.3 6.1-4.3z"/>
            </svg>
            Log in with Google
        </button>


    </GuestLayout>
</template>
