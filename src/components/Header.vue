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

// Referencias a los modales
const modalLogin = ref(null);
const modalRegistro = ref(null);
let modalInstanceLogin = null;
let modalInstanceRegistro = null;

// Datos de los formularios de inicio de sesión y registro
const form = ref({ usuario: "", password: "" });
const formRegistro = ref({ usuario: "", email: "", password: "" });
const error = ref("");

// Función para mostrar el modal de login
function abrirModalLogin() {
  modalInstanceLogin = new Modal(modalLogin.value);
  modalInstanceLogin.show();
}

// Función para cerrar el modal de login
function cerrarModalLogin() {
  modalInstanceLogin.hide();
}

// Función para mostrar el modal de registro
function abrirModalRegistro() {
  cerrarModalLogin();
  modalInstanceRegistro = new Modal(modalRegistro.value);
  modalInstanceRegistro.show();
}

// Función para cerrar el modal de registro
function cerrarModalRegistro() {
  modalInstanceRegistro.hide();
}

// Función para iniciar sesión
async function iniciarSesion() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/usuarios");
    const usuarios = await response.json();

    const usuarioEncontrado = usuarios.find(
      (u) => u.email == form.value.usuario && u.contraseña == form.value.password
    );

    if (usuarioEncontrado) {
      emit("sesionIniciada", {
        usuario: usuarioEncontrado.nombre,
        rol: usuarioEncontrado.rol
      });
      
      error.value = "";
      cerrarModalLogin();
    } else {
      error.value = "Usuario o contraseña incorrectos";
    }
  } catch (err) {
    error.value = "Error al cargar los datos";
  }
}

// Función para registrar usuario
function registrarUsuario() {
  if (!formRegistro.value.usuario || !formRegistro.value.email || !formRegistro.value.password) {
    error.value = "Todos los campos son obligatorios";
    return;
  }

  const nuevoUsuario = {
    usuario: formRegistro.value.usuario,
    email: formRegistro.value.email,
    password: formRegistro.value.password,
    rol: "usuario"
  };

  // Guardar en localStorage el nuevo usuario
  localStorage.setItem("nuevoUsuario", JSON.stringify(nuevoUsuario));

  // Limpiar formulario y errores
  formRegistro.value = { usuario: "", email: "", password: "" };
  error.value = "";

  //Se cierra el modal del registro y automáticamente se abre el modal del login
  cerrarModalRegistro();
  abrirModalLogin(); 
}

// Función para cerrar sesión
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
            <li v-if="!usuarioAutenticado" class="nav-item">
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
              <label class="form-label">Usuario</label>
              <input type="text" v-model="form.usuario" class="form-control" placeholder="Ingrese su usuario" />
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" v-model="form.password" class="form-control" placeholder="Ingrese su contraseña" />
            </div>
            <p v-if="error" class="text-danger mt-2">{{ error }}</p>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </div>
            <p class="text-center mt-2">
              ¿No tienes cuenta?
              <a href="#" @click.prevent="abrirModalRegistro">Regístrate</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Registro -->
  <div ref="modalRegistro" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Registro</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="registrarUsuario">
            <div class="mb-3">
              <label class="form-label">Usuario</label>
              <input type="text" v-model="formRegistro.usuario" class="form-control" placeholder="Ingrese un usuario" />
            </div>
            <div class="mb-3">
              <label class="form-label">Correo Electrónico</label>
              <input type="email" v-model="formRegistro.email" class="form-control" placeholder="Ingrese su email" />
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" v-model="formRegistro.password" class="form-control" placeholder="Ingrese una contraseña" />
            </div>
            <p v-if="error" class="text-danger mt-2">{{ error }}</p>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-success">Registrarse</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
