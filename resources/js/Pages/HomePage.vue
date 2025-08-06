<template>
  
    <div class="home-page">
        <div class="overlay">
           
            <Register />
            <Login />
            

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
    }
}


</script>

<style lang="scss" src="../../css/home-page.scss"></style>



    