<template>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Navbar</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <router-link to="/">Startseite</router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/adults">Erzieher:innen-Modus</router-link>
                </li>
                <li class="nav-item">
                    <router-link to="/kids">Kinder-Modus</router-link>
                </li>
                <li class="nav-item">
                    <button type="button" class="btn btn-outline-primary" @click="userLogout">Abmelden</button>
                </li>
            </ul>
            </div>
        </div>
    </nav>

</template>

<script>
import axios from 'axios';

export default {


    methods: {
        async userLogout (){
            axios.defaults.headers.common['X-CSRF-TOKEN'] =
            document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            axios.defaults.withCredentials = true;
            try {
                await axios.post('/logout');
                this.$router.push('/');
        
            }catch(error) {
                console.error('Logout fehlgeschlagen', {
    status: error.response?.status,
    data: error.response?.data,   // << wichtig
  });
            }
            
        }
    }
}

</script>