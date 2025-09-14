
 <!-- AuthSwitch.vue -->
<template>
  <div class="button-group">
    <!-- Login-Form -->
    <template v-if="mode === 'login'">
      <button type="submit" class="btn btn-primary" :disabled="disabled">Einloggen</button>
      <button type="button" class="btn btn-outline-primary" @click="goRegister">
        Registrieren
      </button>
    </template>

    <!-- Register-Form -->
    <template v-else>
      <button type="button" class="btn btn-outline-primary" @click="goLogin">
        Einloggen
      </button>
      <button type="submit" class="btn btn-primary" :disabled="disabled">Registrieren</button>
    </template>
  </div>
</template>

<script>
export default {
  name: "AuthSwitch",
  props: {
    mode: { 
      type: String, 
      required: true 
    },
    disabled: { 
      type: Boolean, 
      default: false 
    },
  },
  computed: {
    isLogin() {
      return this.mode === 'login'
    },
  },
  methods: {
    goLogin()    { 
      this.$router.push({ name: 'login' }); 
    },
    goRegister() { 
      this.$router.push({ name: 'register' }); 
    },
  }
}
</script>

