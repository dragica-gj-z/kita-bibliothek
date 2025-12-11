<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <p class="ms-5"></p>

      <ul class="navbar-nav ms-auto gap-2">
        <li class="nav-item dropdown">
          <button
            v-if="$route.path.startsWith('/adults')"
            key="adults-toggle"
            type="button"
            class="nav-link dropdown-toggle btn btn-link p-0 active-link"
            data-bs-auto-close="false"
            aria-expanded="false"
            ref="adultsToggle"
            @click="toggleAdultsDropdown"
          >
            Für Erzieher
          </button>

          <router-link
            v-else
            key="adults-link"
            class="nav-link"
            to="/adults"
            active-class="active-link"
          >
            Für Erzieher
          </router-link>

          <ul class="dropdown-menu">
            <li>
              <router-link class="dropdown-item" to="/addBooks">
                Bücher einlegen
              </router-link>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <router-link class="nav-link" to="/kids">Für Kinder</router-link>
        </li>

        <li class="nav-item">
          <button
            type="button"
            class="logout-btn me-2"
            @click="userLogout"
            :disabled="loading"
          >
            {{ loading ? 'Abmelden…' : 'Abmelden' }}
          </button>
        </li>

      </ul>
    </div>
  </nav>
</template>

<script>
import axios from 'axios';
import { Dropdown } from 'bootstrap';

export default {
    data(){ 
        return { 
            loading: false, 
            error: '' 
        } 
    },

    mounted() {
        this.initAdultsDropdown()
    },

    watch: {
        '$route.path'(val) {
            if (val.startsWith('/adults')) this.$nextTick(() => this.initAdultsDropdown())
            else this._adultsDd?.dispose?.()
        }
    },

    
    methods:{
        initAdultsDropdown() {
            const el = this.$refs.adultsToggle
            if (!el) return
            this._adultsDd?.dispose?.()
            this._adultsDd = Dropdown.getOrCreateInstance(el, { autoClose: false }) // bleibt offen
        },
        toggleAdultsDropdown(e) {
            e.preventDefault()
            this._adultsDd?.toggle()
        },

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
        },

        beforeUnmount() {
            this._adultsDd?.dispose?.()
        },
    }
}

</script>

