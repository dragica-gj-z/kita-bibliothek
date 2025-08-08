<template>

    <form class="login-form">
        <div class="mb-3">
            <label for="name" class="form-label">E-Mail</label>
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
            <input 
                type="password" 
                class="form-control" 
                name="password" 
                required
                v-model="form.password"
                >
        </div>
        
        <button type="button" class="btn btn-primary" @click="userLogin">Einloggen</button>

        <a href="#">Weiter ohne Amelden</a>

    </form>

</template>

<script>
export default {
    name: "Login",

    data () {
        return {
            form: {
                email: '',
                password: '',
            }
        }
    },

    methods: {
        async userLogin () {
            axios.defaults.headers.common['X-CSRF-TOKEN'] =
            document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                await axios.post ('/login', {
                    email: this.form.email,
                    password: this.form.password,
                });
                this.$router.push('/adults');
            } catch (error){
                console.error(error);
                this.$router.push('/');
                this.errorMessage = 'Einloggen fehlgeschlagen. Bitte erneut versuchen.';
            }
        },
    }
}

</script>