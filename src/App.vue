<script setup>
import { ref, onMounted } from "vue";
import Header from "./components/Header.vue";
import Footer from "./components/Footer.vue";


const usuarioAutenticado = ref(null);

// Carga la sesión almacenada
onMounted(() => {
  const sesion = localStorage.getItem("sesion");
  if (sesion) {
    usuarioAutenticado.value = JSON.parse(sesion);
  }
});

// Manejar inicio de sesión
function manejarSesionIniciada(datos) {
  usuarioAutenticado.value = datos;
  localStorage.setItem("sesion", JSON.stringify(datos));
}

// Manejar cierre de sesión
function manejarSesionCerrada() {
  usuarioAutenticado.value = null;
  localStorage.removeItem("sesion");
}
</script>

<template>
  <Header
    title="DreamTrip"
    :usuarioAutenticado="usuarioAutenticado"
    @sesionIniciada="manejarSesionIniciada"
    @sesionCerrada="manejarSesionCerrada"
  />
  <router-view />
  <Footer></Footer>
</template>
