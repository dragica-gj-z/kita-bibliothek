<!-- Register.vue -->
<template>
    <form class="register-form" @submit.prevent="registerNewUser" novalidate>

        <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input 
            type="text" 
            class="form-control" 
            name="name"
            required
            v-model.trim="form.name"
            :class="{ 'is-invalid': fieldErrors.name }"
            >
            <div v-if="fieldErrors.name" class="invalid-feedback">
                {{ fieldErrors.name }}
            </div>

        </div>

        <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input 
            type="email" 
            class="form-control" 
            name="email"
            required
            v-model.trim="form.email"
            :class="{ 'is-invalid': fieldErrors.email }"
            >
        </div>

        <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" 
            class="form-control" 
            name="password"
            required
            v-model="form.password"
            :class="{ 'is-invalid': fieldErrors.password }"
            >
            <div v-if="fieldErrors.password" class="invalid-feedback">
                {{ fieldErrors.password }}
            </div>
        </div>

        <div class="mb-3">
        <label for="password-confirmation" class="form-label">Password wiederholen</label>
        <input 
            type="password" 
            class="form-control" 
            name="password_confirmation"
            required
            v-model="form.passwordConfirmation"
            :class="{ 'is-invalid': fieldErrors.password_confirmation }"
            >
            <div v-if="fieldErrors.password_confirmation" class="invalid-feedback">
                {{ fieldErrors.password_confirmation }}
            </div>
        </div>
        <AuthSwitch mode="register" :disabled="loading" />
        <p v-if="errorMessage" class="text-danger mt-2">{{ errorMessage }}</p>

    </form>
   

</template>

<script>
import axios from 'axios';
import AuthSwitch from "./AuthSwitch.vue";


export default {
    name: "Register",
    components: { AuthSwitch },

    data () {
        return {
            form: {
                name: '',
                email: '',
                password: '',
                passwordConfirmation: '', 
            },
            loading: false,
            errorMessage: '',
            fieldErrors: {}
        }
    },

    methods: {
        async registerNewUser () {
      
            this.errorMessage = ''
            this.fieldErrors = {}

            if (!this.form.email || !/.+@.+\..+/.test(this.form.email)) {
                this.fieldErrors.email = 'Bitte eine gültige E-Mail eingeben.'
            }
            if (!this.form.password) {
                this.fieldErrors.password = 'Bitte Passwort eingeben.'
            } else if (this.form.password.length < 8) {
                this.fieldErrors.password = 'Passwort muss mindestens 8 Zeichen enthalten.'
            }

            if (!this.form.passwordConfirmation) {
                this.fieldErrors.password_confirmation = 'Bitte Passwort wiederholen.'
            } else if (this.form.password !== this.form.passwordConfirmation) {
                this.fieldErrors.password_confirmation = 'Passwörter stimmen nicht überein.'
            }
            if (Object.keys(this.fieldErrors).length) return

            this.loading = true
            try {
                const res = await axios.post('/register', {
                    name: this.form.name,
                    email: this.form.email,
                    password: this.form.password,
                    password_confirmation: this.form.passwordConfirmation
                })

                if (res.status === 201 || res.status === 204 || (res.data && (res.data.user || res.data.success))) {
                this.$router.push({ name: 'login', query: { registered: '1' } });
                return;
                }
                this.errorMessage = 'Registrierung fehlgeschlagen. Bitte erneut versuchen.'
            } catch (err) {
                const r = err?.response

                if (r?.status === 422 && r.data?.errors) {
                this.fieldErrors = Object.fromEntries(
                    Object.entries(r.data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : String(v)])
                )
                this.errorMessage = r.data?.message || 'Bitte Eingaben prüfen.'
                } else if (r?.status === 409) {
                this.fieldErrors.email = 'Diese E-Mail ist bereits vergeben.'
                } else {
                this.errorMessage = 'Unerwarteter Fehler. Bitte später erneut versuchen.'
                }
            } finally {
                this.loading = false
            }
        }
  
    }

}

</script>

<style lang="scss" src="../../css/home-page.scss"></style>
