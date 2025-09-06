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
                    <button type="button" class="btn btn-outline-primary" @click="userLogout" :disabled="loading">
                        {{ loading ? 'Abmelden…' : 'Abmelden' }}</button>
                </li>
            </ul>
            </div>
        </div>
    </nav>

</template>

<script>
import axios from 'axios';

export default {
    data(){ 
        return { 
            loading: false, 
            error: '' 
        } 
    },

    methods:{
        async userLogout(){
            this.loading = true; this.error = '';

            const doLogout = () =>
                axios.post('/logout', null, { validateStatus: s => s < 500 }); // 4xx in then-Zweig

            try {
                let res = await doLogout();

                if (res.status === 419) {
                    await axios.get('/', { params: { _t: Date.now() }, validateStatus: s => s < 500 });
                    res = await doLogout();
                }

                if (res.status === 204 || res.status === 200 || res.status === 401 || res.status === 302) {
                    this.$router.replace({ path: '/', query: { loggedout: '1' } });
                    return;
                }

                if (res.status === 419) {
                    this.error = 'Sicherheits-Token erneuern…';
                    setTimeout(() => window.location.replace('/'), 400);
                    return;
                }

                this.error = 'Abmelden fehlgeschlagen. Bitte erneut versuchen.';
            } catch (e) {
                this.error = 'Netzwerkfehler beim Abmelden.';
                console.error('Logout error', e);
            } finally {
                this.loading = false;
            }
        }
  }
}

</script>