<template>

    <form class="register-form" @submit.prevent="registerNewUser">

        <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input 
            type="text" 
            class="form-control" 
            name="name"
            required
            v-model="form.name"
            >
        </div>

        <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input 
            type="email" 
            class="form-control" 
            name="email"
            required
            v-model="form.email"
            >
        </div>

        <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" 
            class="form-control" 
            name="password"
            required
            v-model="form.password"
            >
        </div>

        <div class="mb-3">
        <label for="password-confirmation" class="form-label">Password wiederholen</label>
        <input 
            type="password" 
            class="form-control" 
            name="password_confirmation"
            required
            v-model="form.passwordConfirmation"
            >
        </div>

        <button type="submit" class="btn btn-primary">Registrieren</button>

        <p v-if="errorMessage" class="text-danger">{{ errorMessage }}</p>

    </form>
   

</template>

<script>
import axios from 'axios';

export default {
    name: "Register",

    data () {
        return {
            form: {
                name: '',
                email: '',
                password: '',
                passwordConfirmation: '',   
            },
            errorMessage: '',
        }
    },

    methods: {
        async registerNewUser () {
            axios.defaults.headers.common['X-CSRF-TOKEN'] =
            document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                await axios.post ('/register', {
                    name: this.form.name,
                    email: this.form.email,
                    password: this.form.password,
                    password_confirmation: this.form.passwordConfirmation
                });
                this.$router.push('/adults');
            } catch (error){
                console.error(error);
                this.$router.push('/');
                this.errorMessage = 'Registrierung fehlgeschlagen. Bitte erneut versuchen.';
            }
        }
    }

}

</script>