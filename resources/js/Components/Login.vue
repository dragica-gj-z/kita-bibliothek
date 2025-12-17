<!-- Login.vue -->
<template>

    <form class="login-form" ref="root" @submit.prevent="userLogin" novalidate>
        <div class="mb-3">
            <label for="name" class="form-label">E-Mail</label>
            <input 
                ref="emailInput"
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
                ref="passwordInput"
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
   
    <!-- Demo Login -->
        <div class="demo-login mb-0">
                <h5 class="mb-2">Demo-Login</h5>

                <div class="mb-1">
                  <strong>E-Mail:</strong>
                  <span
                    class="copyable"
                    role="button"
                    tabindex="0"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-html="true"
                    :data-bs-title="tooltipCopyHtml"
                    data-copy-key="email"
                    @click="copy(demoEmail, 'email')"
                    @keydown.enter.prevent="copy(demoEmail, 'email')"
                    @keydown.space.prevent="copy(demoEmail, 'email')"
                    >
                    <code>{{ demoEmail }}</code>
                  </span>
                </div>
                <div>
                  <strong>Passwort:</strong>
                  <span
                    class="copyable"
                    role="button"
                    tabindex="0"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-html="true"
                    :data-bs-title="tooltipCopyHtml"
                    data-copy-key="password"
                    @click="copy(demoPassword, 'password')"
                    @keydown.enter.prevent="copy(demoPassword, 'password')"
                    @keydown.space.prevent="copy(demoPassword, 'password')"
                  >
                    <code>{{ demoPassword }}</code>
                  </span>
                </div>
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

            demoEmail: "test@test.de",
            demoPassword: "test1234",
            tooltips: new Map(),
            _ttTimer: null,
            tooltipCopyHtml: "<i class='bi bi-copy'></i> Kopieren",
            tooltipCopiedHtml: "<i class='bi bi-check2'></i> Kopiert",
        }
    },

    created() {
        this.consumeFlash();
    },
    watch: {
        '$route.query.registered': function () {
            this.consumeFlash();
        }, 
    },

    mounted() {
         this.initTooltips();
    },

    beforeUnmount() {
        clearTimeout(this._ttTimer);
        this.tooltips.forEach(t => t.dispose());
        this.tooltips.clear();
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

        initTooltips() {
            if (!window.bootstrap || !this.$refs.root) return;

            this.tooltips.forEach(t => t.dispose());
            this.tooltips.clear();

            const els = this.$refs.root.querySelectorAll('[data-bs-toggle="tooltip"]');
            els.forEach(el => {
                const key = el.getAttribute("data-copy-key");
                const tt = new window.bootstrap.Tooltip(el, { trigger: "hover focus", html: true });
                if (key) this.tooltips.set(key, tt);
            });
        },

        async copy(text, key) {
            try {
                await navigator.clipboard.writeText(text);
            } catch (e) {
                return; // optional: hier könntest du "nicht möglich" tooltip zeigen
            }

            if (key === "email") {
                this.form.email = text;
                this.$nextTick(() => this.$refs.emailInput?.focus());
            } else if (key === "password") {
                this.form.password = text;
                this.$nextTick(() => this.$refs.passwordInput?.focus());
            }

            const el = this.$refs.root?.querySelector(`[data-copy-key="${key}"]`);
            const tt = this.tooltips.get(key);
            if (!el || !tt) return;

            el.setAttribute("data-bs-title", this.tooltipCopiedHtml);

            tt.dispose();
            const manualTt = new window.bootstrap.Tooltip(el, { trigger: "manual", html: true });
            this.tooltips.set(key, manualTt);

            manualTt.show();

            clearTimeout(this._ttTimer);
            this._ttTimer = setTimeout(() => {
                manualTt.hide();
                el.setAttribute("data-bs-title", this.tooltipCopyHtml);
                manualTt.dispose();
                this.tooltips.set(key, new window.bootstrap.Tooltip(el, { trigger: "hover focus", html: true }));
            }, 900);
        },
    }
}
</script>

<style lang="scss" src="../../css/intro-modal.scss"></style>
