<template>
  <div id="app">
    <Navbar v-if="!showNavbar" />
    
    <!-- <Smiley /> -->
    <router-view /> <!-- Hier wird HomePage.vue geladen -->
    
  </div>
</template>

<script>
import Navbar from './Navbar.vue'
export default {
  name: 'App',
  components: { Navbar },
  computed: {
    showNavbar() {
      // verstecke Navbar wenn irgendein gematchter Route-Record hideNavbar trägt
      return this.$route.name === 'login' || this.$route.name === 'register'    }
  }
}
</script>