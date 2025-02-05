<script setup>
import { ref } from "vue";
import router from "../router";
import { Modal } from "bootstrap";

const emit = defineEmits(["sesionIniciada", "sesionCerrada"]);

const props = defineProps({
  title: String,
  usuarioAutenticado: Object,
  datosUsuario: Object
});

// Referencia para el modal
const modalLogin = ref(null);
let modalInstance = null;

// Datos del formulario
const form = ref({ usuario: "", password: "" });
const error = ref("");

// Función para mostrar el modal
function abrirModalLogin() {
  modalInstance = new Modal(modalLogin.value);
  modalInstance.show();
}

// Función para iniciar sesión
async function iniciarSesion() {
  try {
    const response = await fetch("/usuarios.json");
    const usuarios = await response.json();

    const usuarioEncontrado = usuarios.find(
      (u) => u.usuario === form.value.usuario && u.password === form.value.password
    );

    if (usuarioEncontrado) {
      emit("sesionIniciada", {
        usuario: usuarioEncontrado.usuario,
        rol: usuarioEncontrado.rol
      });

      error.value = "";

      // Cerrar modal después del login exitoso
      modalInstance.hide();
    } else {
      error.value = "Usuario o contraseña incorrectos";
    }
  } catch (err) {
    error.value = "Error al cargar los datos";
  }
}

// Método para cerrar sesión
function cerrarSesion() {
  emit("sesionCerrada", null);
  localStorage.removeItem("sesion");
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
        <div class="collapse navbar-collapse align-items-center justify-content-center">
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
          <form @submit.prevent="iniciarSesion">
            <div class="mb-3">
              <label for="email" class="form-label">Usuario</label>
              <input type="text" v-model="form.usuario" class="form-control" placeholder="Ingrese su usuario" />
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <input type="password" v-model="form.password" class="form-control" placeholder="Ingrese su contraseña" />
            </div>
            <p v-if="error" class="text-danger mt-2">{{ error }}</p>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
