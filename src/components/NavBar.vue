<script setup>
import {ref} from "vue"
import router from "../router"

function irHome() {
  router.push("./")
}
function irLogin() {
  router.push("./login")
}
const props = defineProps({
    datosUsuario: Object
})

</script>
<template>
      
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Navbar</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li v-if="datosUsuario?.rol=='profe'" class="nav-item active">
                    <a class="nav-link" href="#">Preguntas <span class="sr-only">(current)</span></a>
                </li>
                <li v-if="datosUsuario?.rol=='profe'" class="nav-item">
                    <a class="nav-link" href="#">Categorías</a>
                </li>
                <li v-if="datosUsuario?.rol=='profe'" class="nav-item">
                    <a class="nav-link disabled" href="#">Exámenes</a>
                </li>
                <li v-if="datosUsuario?.rol=='profe'" class="nav-item">
                    <a class="nav-link disabled" href="#">Resultados</a>
                </li>
                <li class="nav-item" @click="irHome">
                    <a class="nav-link disabled" @click="irHome" href="#">Home</a>
                </li>
                <li class="nav-item" @click="irLogin" >
                    <a class="nav-link disabled" href="#">Login</a>
                </li>
            </ul>
        </div>
    </nav>
  
</template>
  

  <style scoped>
  

  </style>
  