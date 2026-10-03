<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);
const form = useForm({ password: '' });

const confirmUserDeletion = async () => {
    confirmingUserDeletion.value = true;
    await nextTick();
    passwordInput.value?.focus();
};
const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
const deleteUser = () => {
    if (form.processing) return;
    if (!form.password) {
        form.setError('password', 'Enter your password to confirm deletion.');
        passwordInput.value?.focus();
        return;
    }
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: closeModal,
        onError: async () => {
            await nextTick();
            passwordInput.value?.focus();
        },
    });
};
</script>

<template>
    <section class="ea-account-section" aria-labelledby="delete-account-heading">
        <header>
            <h2 id="delete-account-heading">Delete account</h2>
            <p>This action is permanent. Take a moment to check what you need to keep.</p>
        </header>
        <div class="ea-account-delete">
            <p>Deleting your account also deletes every event you own and its planning data, including access for collaborators. Events owned by other people will remain.</p>
            <button type="button" class="ea-button ea-account-danger" @click="confirmUserDeletion">Delete my account</button>
        </div>
        <Modal :show="confirmingUserDeletion" :closeable="!form.processing" max-width="lg" aria-labelledby="delete-dialog-title" aria-describedby="delete-dialog-description" @close="closeModal">
            <form class="ea-account-dialog" novalidate @submit.prevent="deleteUser">
                <h2 id="delete-dialog-title">Delete your account?</h2>
                <p id="delete-dialog-description">Your account and every event you own will be permanently deleted. Your collaborators will lose access to those events. This cannot be undone.</p>
                <div class="ea-form-field">
                    <InputLabel for="delete_password" value="Confirm with your password" />
                    <TextInput id="delete_password" ref="passwordInput" v-model="form.password" type="password" required autocomplete="current-password" :aria-invalid="Boolean(form.errors.password)" aria-describedby="delete-password-error" @input="form.clearErrors('password')" />
                    <InputError reserve-space id="delete-password-error" :message="form.errors.password" />
                </div>
                <div class="ea-account-actions">
                    <button type="button" class="ea-button ea-button-outline" :disabled="form.processing" @click="closeModal">Keep my account</button>
                    <button class="ea-button ea-account-danger" :disabled="form.processing">{{ form.processing ? 'Deleting account…' : 'Delete permanently' }}</button>
                </div>
            </form>
        </Modal>
    </section>
</template>
