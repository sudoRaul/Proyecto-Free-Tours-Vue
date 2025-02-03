<script setup>
import {ref} from "vue"
import router from "../router"
const emit = defineEmits(["sesionCerrada"])

 const props=defineProps({
  title: String,
  usuarioAutenticado: Object,
  datosUsuario: Object
});



function irHome() {
  router.push("./")
}
function irLogin() {
  router.push("./login")
}



function cerrarSesion() {
  emit("sesionCerrada", null)
  localStorage.removeItem("sesion")

//TODO: HABRÍA QUE NOTIFICAR A APP.VUE CON UN EMIT PARA QUE SEPA QUE LA SESIÓN ESTÁ CERRADA

}
</script>


<template>
    <header class="bg-ligth text-black align-items-center row ">
        <div class="row">
          
          <h1 class="col-3"> <img src="@/images/logo.svg" alt="Logotipo" width="100px" height="100px"> {{ title }}</h1>
          
        <div class="navbar navbar-expand-lg navbar-light col-6 pb-4">
          
          <div class="collapse navbar-collapse align-items-center justify-content-center " id="navbarSupportedContent">
              <ul class="navbar-nav mr-auto">
                <li class="nav-item" @click="irHome">
                      <a class="nav-link" @click="irHome" href="#">Home</a>
                  </li>
                  <li class="nav-item ">
                      <a class="nav-link" href="#">Cosa 1</a>
                  </li>
                  <li class="nav-item ">
                      <a class="nav-link" href="#">Cosa</a>
                  </li>
                  <li class="nav-item ">
                      <a class="nav-link" href="#">Cosa</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="#">Cosa</a>
                  </li>
                  
                  <li v-if="!datosUsuario" class="nav-item" @click="irLogin">
                      <a class="nav-link " href="#">Login</a>
                  </li>
              </ul>
          </div>
        </div>
        <div v-if="usuarioAutenticado" class="col-3 mt-4">
          <span>Bienvenid@, {{ usuarioAutenticado?.usuario }} ({{ usuarioAutenticado?.rol }}) <button @click="cerrarSesion" class="btn btn-danger">Cerrar Sesión</button></span>
          
        </div>
        </div>  
  </header>
</template>
  
 
<style scoped>

</style>
  