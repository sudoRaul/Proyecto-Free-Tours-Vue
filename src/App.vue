<script setup>
import {ref} from "vue"
import Content from './components/Content.vue';
import Card from './components/Card.vue';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import NavBar from './components/NavBar.vue';
import router from "./router"

function irLogin() {
  router.push("./about")
}

//Abría que controlar cuando se inicia sesión y se cierra con un emit desde el hijo y tener un dato
// de sesión reactivo para pasarlo a la barra de navegación como props
const sesion = ref(JSON.parse(localStorage.getItem("sesion")))

function actualizaDatosSesion(usuario){
  sesion.value = usuario;
  localStorage.setItem("sesion", JSON.stringify(usuario))
}
</script>

<template>
  <div class="layout">

    <Header :datosUsuario="sesion" :usuarioAutenticado="sesion" @sesionCerrada="actualizaDatosSesion"  title="DREAM TRIP"/>
    <!--HACER E INCLUIR COMPONENTE DE BARRA DE NAVEGACIÓN CON OPCIONES VISIBLES SEGÚN EL ROL DEL USUARIO-->
    
    
   
    <RouterView @sesionIniciada="actualizaDatosSesion"></RouterView>

    <!-- <Content>
      <template v-slot:sidebar>
        <h2>Menú</h2>
        <ul>
          <li>Inicio</li>
          <li>Acerca de</li>
          <li>Contacto</li>
        </ul>
      </template>

      <template #main>
        <h1>Bienvenido</h1>
        <Card title="Tarjeta 1">
          <p>Este es el contenido de la tarjeta 1.</p>
        </Card>

        <Card title="Tarjeta 2">
          <p>Otro contenido diferente en la tarjeta 2.</p>
        </Card>
      </template>
    </Content>-->
    <Footer/>
  </div>
</template>


<style scoped>
.layout {
    display: flex;
    flex-direction: column;
    height: 100vh;
  }
  
</style>