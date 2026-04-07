<script setup lang="ts">
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

function submit() {
    form.patch(edit().url);
}
</script>

<template>
    <Head title="Profile settings" />

    <SettingsLayout>
        <div class="space-y-6">
            <HeadingSmall title="Profile information" description="Update your name and email address" />

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" v-model="form.email" />
                    <InputError :message="form.errors.email" />
                </div>

                <div v-if="mustVerifyEmail && !user.email_verified_at">
                    <p class="text-sm text-muted-foreground">
                        Your email address is unverified.
                        <Link :href="send().url" method="post" as="button" class="underline text-primary">
                            Click here to re-send the verification email.
                        </Link>
                    </p>
                    <p v-if="status === 'verification-link-sent'" class="text-sm text-green-600 mt-2">
                        A new verification link has been sent to your email address.
                    </p>
                </div>

                <Button type="submit" :disabled="form.processing">Save</Button>
            </form>

            <DeleteUser />
        </div>
    </SettingsLayout>
</template>
