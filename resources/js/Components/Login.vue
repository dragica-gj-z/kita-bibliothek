<template>

<form class="login-form">
                <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                </div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" class="form-control" id="exampleInputPassword1">
                </div>
                <a href="#">Weiter ohne Amelden</a>
                
                <!-- <button type="submit" class="btn btn-primary">Submit</button> -->
            </form>

</template>

<script>
export default {
    name: "Login"
}

</script>