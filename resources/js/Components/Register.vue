<template>

    <div class="register-form">

        <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name">
        </div>

        <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" class="form-control" id="email">
        </div>

        <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password">
        </div>

        <div class="mb-3">
        <label for="password-confirmation" class="form-label">Password wiederholen</label>
        <input type="password" class="form-control" id="password-confirmation">
        </div>

    </div>
   

</template>

<script>
export default {
    name: "Register"
}

</script>