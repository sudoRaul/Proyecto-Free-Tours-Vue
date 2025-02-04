<script setup>
import { ref } from "vue";
import router from "../router";
const emit = defineEmits(["sesionCerrada"]);

const props = defineProps({
  title: String,
  usuarioAutenticado: Object,
  datosUsuario: Object
});

// Método para cerrar sesión
function cerrarSesion() {
  emit("sesionCerrada", null);
  localStorage.removeItem("sesion");
}

// Referencia para el modal
const modalLogin = ref(null);

// Función para mostrar el modal
function abrirModalLogin() {
  const modal = new bootstrap.Modal(modalLogin.value);
  modal.show();
}
</script>

<template>
  <header class="bg-light text-black align-items-center row">
    <div class="row">
      <h1 class="col-4">
        <img src="@/images/logo.svg" alt="Logotipo" width="100px" height="100px" />
        {{ title }}
      </h1>

      <div class="navbar navbar-expand-lg navbar-light col-5 pb-4">
        <div class="collapse navbar-collapse align-items-center justify-content-center" id="navbarSupportedContent">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item">
              <a class="nav-link" @click.prevent="router.push('./')" href="#">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Cosa 1</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Cosa</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Cosa</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Cosa</a>
            </li>
            <li v-if="!datosUsuario" class="nav-item">
              <a class="nav-link" href="#" @click.prevent="abrirModalLogin">Login</a>
            </li>
          </ul>
        </div>
      </div>

      <div v-if="usuarioAutenticado" class="col-3 mt-4">
        <span>
          Bienvenid@, {{ usuarioAutenticado?.usuario }} ({{ usuarioAutenticado?.rol }})
          <button @click="cerrarSesion" class="btn btn-danger">Cerrar Sesión</button>
        </span>
      </div>
    </div>
  </header>

  <!-- Modal de Login -->
  <div ref="modalLogin" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Iniciar Sesión</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="email" class="form-label">Correo electrónico</label>
              <input type="email" class="form-control" id="email" placeholder="Ingrese su email" />
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="password" placeholder="Ingrese su contraseña" />
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary">Iniciar Sesión</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Puedes agregar estilos personalizados si es necesario */
</style>
