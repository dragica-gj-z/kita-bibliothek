<!-- Login.vue -->
<template>

    <form class="login-form" @submit.prevent="userLogin" novalidate>
        <div class="mb-3">
            <label for="name" class="form-label">E-Mail</label>
            <input 
                type="email" 
                class="form-control" 
                name="email" 
                required
                v-model.trim="form.email"
                :class="{'is-invalid': fieldErrors.email}"
                >
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input 
                type="password" 
                class="form-control" 
                name="password" 
                required
                minlength="8"
                v-model="form.password"
                :class="{'is-invalid': fieldErrors.password}"
                >
        </div>

        <AuthSwitch mode="login" :disabled="loading" />
        <p v-if="errorMessage" class="error-msg mt-2">{{ errorMessage }}</p>

         <div v-if="successMessage" class="success-msg mt-3" >
            {{ successMessage }}
        </div>
    </form>
</template>

<script>
import axios from 'axios'
import AuthSwitch from "./AuthSwitch.vue";

export default {
    name: "Login",
    components: { AuthSwitch },

    data () {
        return {
            form: {
                email: '',
                password: '',
            },
            errorMessage: '',
            successMessage: '',
            loading: false,
            fieldErrors: {},
        }
    },

    created() {
        this.consumeFlash();
    },
    watch: {
        '$route.query.registered': function () {
        this.consumeFlash();
        }
    },

    methods:{
        async userLogin () {
            this.errorMessage = '';
            try {
                const res = await axios.post('/login', {
                    email: this.form.email,
                    password: this.form.password,
                });

                if (res.status === 204 || (res.data && (res.data.user || res.data.success))) {
                    this.$router.push('/adults');
                return;
                }
                this.errorMessage = 'Einloggen fehlgeschlagen. \nBitte erneut versuchen.';
            } catch (err) {
                const r = err?.response;
                if (r && (r.status === 401 || r.status === 403)) {
                    this.errorMessage = 'E-Mail oder Passwort ist falsch.';
                } else if (r && r.status === 422) {
                    this.errorMessage = r.data?.message || 'Bitte Eingaben prüfen.';
                } else {
                    this.errorMessage = 'Unerwarteter Fehler. \nBitte später erneut versuchen.';
                }
            }
        },

        consumeFlash(){
            const q = this.$route.query;
            if (q.registered) {
                this.successMessage = 'Ihr Konto wurde erfolgreich erstellt.\nBitte melden Sie sich jetzt an.';
            } else if (q.loggedout) {
                this.successMessage = 'Sie wurden abgemeldet.';
            } else {
                this.successMessage = ''; 
                return;
            }
            const { registered, loggedout, ...rest } = q;
                this.$router.replace({ query: rest });
        },
    }
}
</script>

<style lang="scss" src="../../css/app.scss"></style>
