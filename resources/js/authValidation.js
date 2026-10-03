import { computed, reactive, watch } from 'vue';

export function validateAuthField(field, values, registering = false) {
    const value = values[field] ?? '';

    if (field === 'name' && !value.trim()) return 'Enter your name.';
    if (field === 'email') {
        if (!value.trim()) return 'Enter your email address.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Use an email like name@example.com.';
    }
    if (field === 'password') {
        if (!value) return 'Enter your password.';
        if (registering && [...value].length < 8) return 'Use at least 8 characters.';
    }
    if (field === 'current_password' && !value) return 'Enter your current password.';
    if (field === 'password_confirmation') {
        if (!value) return 'Confirm your password.';
        if (value !== values.password) return 'Passwords must match.';
    }

    return '';
}

export function useAuthValidation(form, registering = false, selectedFields = null) {
    const fields = selectedFields ?? (registering
        ? ['name', 'email', 'password', 'password_confirmation']
        : ['email', 'password']);
    const touched = reactive({});
    const touch = (field) => { touched[field] = true; };

    fields.forEach((field) => {
        watch(() => form[field], () => {
            form.clearErrors(field);
            if (!registering && field === 'password' && fields.includes('email')) form.clearErrors('email');
            touch(field);
        }, { flush: 'sync' });
    });

    const errors = computed(() => Object.fromEntries(fields.map((field) => [
        field,
        form.errors[field] || (touched[field] ? validateAuthField(field, form, registering) : ''),
    ])));

    const validate = () => {
        fields.forEach(touch);
        return !fields.some((field) => validateAuthField(field, form, registering));
    };

    const resetFeedback = () => fields.forEach((field) => { delete touched[field]; });

    return { errors, touch, validate, resetFeedback };
}
