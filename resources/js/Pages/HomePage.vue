<!-- HomePage.vue -->
<template>
  <div class="home-page">
    <div class="overlay">
      <router-view />  <!-- HIER erscheinen Login/Registrieren -->
    </div>
  </div>
</template>

<script>
import Login from '../Components/Login.vue';
import Register from '../Components/Register.vue';


export default{
    name: "HomePage",
    components: {
        Login,
        Register,
    },

    data () {
        return {
            showLogin: true
        }
    },

    methods: {
        toggle () {
            this.showLogin = !this.showLogin
        },
        switchToLogin() { 
            this.showLogin = true 
        },
    }
}

</script>

<style lang="scss" src="../../css/home-page.scss"></style>



    