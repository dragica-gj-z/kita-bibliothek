<template>
  <div id="app">
    <Navbar v-if="!showNavbar" />
    
    <router-view /> 
    
  </div>
</template>

<script>
import Navbar from './Navbar.vue'
export default {
  name: 'App',
  components: { Navbar },
  computed: {
    showNavbar() {
      return this.$route.name === 'login' || this.$route.name === 'register'    }
  }
}
</script>