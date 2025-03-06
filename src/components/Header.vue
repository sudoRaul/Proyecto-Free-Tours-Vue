<script setup>
import { ref } from "vue";
import router from "../router";
import { useRouter } from "vue-router";
import { Modal } from "bootstrap";

//Necesitamos el router para redirigir al home cuando se inicie o se cierre la sesión
const router2 = useRouter()
const emit = defineEmits(["sesionIniciada", "sesionCerrada"]);

const props = defineProps({
  title: String,
  usuarioAutenticado: Object,
  datosUsuario: Object
});

const modalLogin = ref(null);
const modalRegistro = ref(null);
let modalInstanceLogin = null;
let modalInstanceRegistro = null;

// Datos de los formularios de inicio de sesión y registro
const form = ref({ usuario: "", password: "" });
const formRegistro = ref({ usuario: "", email: "", password: "" });

// Variables reactivas para errores
const errorLogin = ref("");
const errorRegistro = ref("");

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
        rol: usuarioEncontrado.rol,
        id: usuarioEncontrado.id,
        email: usuarioEncontrado.email
      });

      errorLogin.value = "";
      cerrarModalLogin();
      router2.push("/");
    } else {
      errorLogin.value = "Usuario o contraseña incorrectos";
    }
  } catch (err) {
    errorLogin.value = "Error al cargar los datos";

  } finally {
    //Usamos un bloque finally para limpiar el formulario sea cual sea el resultado
    form.value.usuario = "";
    form.value.password = "";
  }
}

// Función para registrar usuario
async function registrarUsuario() {
  // Validamos que estén todos los campos en el formulario de registro
  if (!formRegistro.value.usuario || !formRegistro.value.email || !formRegistro.value.password) {
    errorRegistro.value = "Todos los campos son obligatorios";
    return;
  }

  const nuevoUsuario = {
    nombre: formRegistro.value.usuario,
    email: formRegistro.value.email,
    contraseña: formRegistro.value.password,
    rol: "usuario"
  };

  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/usuarios", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(nuevoUsuario)
    });

    if (!response.ok) {
      errorRegistro.value = "Error al registrar el usuario";
    } else {
      errorRegistro.value = "";
      cerrarModalRegistro();
      abrirModalLogin();
    }

  } catch (err) {
    errorRegistro.value = "Error al registrar el usuario";
  } finally {
    // Se limpia el formulario sin importar el resultado
    formRegistro.value = { usuario: "", email: "", password: "" };
  }
}

// Función para cerrar sesión
function cerrarSesion() {
  emit("sesionCerrada", null);
  localStorage.removeItem("sesion");
  router2.push("/")
}

function toggleMenu(){
  const menu = document.getElementById("navbarNav");
    if (menu.classList.contains("show")) {
      menu.classList.remove("show");
    } else {
      menu.classList.add("show");
    }
}
</script>

<template>
  <header class="bg-light text-black">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid d-flex justify-content-between align-items-center">


        <h1 class="navbar-brand mb-0" @click="router.push('/')">

          <img src="@/images/iconos/logo.svg" class="me-1" alt="Logotipo" width="100px" height="100px" />
          {{ title }}

        </h1>


        <button class="navbar-toggler" type="button" @click="toggleMenu">
          <span class="navbar-toggler-icon"></span>
        </button>


        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
          <ul class="navbar-nav text-center">
            <li class="nav-item">
              <router-link to="/" class="nav-link btn btn-outline-secondary fs-5"
                active-class="active">Home</router-link>
            </li>
            <li v-if="usuarioAutenticado && usuarioAutenticado.rol === 'admin'" class="nav-item">
              <router-link to="/admin" class="nav-link btn btn-outline-secondary fs-5" active-class="active">Lista de
                usuarios</router-link>
            </li>
            <li v-if="usuarioAutenticado && usuarioAutenticado.rol === 'admin'" class="nav-item">
              <router-link to="/crear-ruta" class="nav-link btn btn-outline-secondary fs-5" active-class="active">Crear
                ruta</router-link>
            </li>
            <li v-if="usuarioAutenticado && usuarioAutenticado.rol === 'admin'" class="nav-item">
              <router-link to="/ver-rutas" class="nav-link btn btn-outline-secondary fs-5" active-class="active">Ver
                todas las rutas</router-link>
            </li>

            <li v-if="usuarioAutenticado && usuarioAutenticado.rol === 'guia'" class="nav-item">
              <router-link to="/visitas-pendientes" class="nav-link btn btn-outline-secondary fs-5"
                active-class="active">Visitas pendientes</router-link>
            </li>

            <li v-if="usuarioAutenticado && usuarioAutenticado.rol === 'cliente'" class="nav-item">
              <router-link to="/mis-reservas" class="nav-link btn btn-outline-secondary fs-5" active-class="active">Mis
                reservas</router-link>
            </li>
            <li v-if="usuarioAutenticado && usuarioAutenticado.rol === 'cliente'" class="nav-item">
              <router-link to="/rutas-pasadas" class="nav-link btn btn-outline-secondary fs-5" active-class="active">Rutas pasadas</router-link>
            </li>

            <li v-if="!usuarioAutenticado" class="nav-item">
              <a class="nav-link btn btn-outline-secondary fs-5" href="#topDestinos">Mejores destinos</a>
            </li>
            <li v-if="!usuarioAutenticado" class="nav-item">
              <a class="nav-link btn btn-outline-secondary fs-5" href="#quienesSomos">Sobre nosotros</a>
            </li>
            <li v-if="!usuarioAutenticado" class="nav-item">
              <a class="nav-link btn btn-outline-secondary fs-5" href="#" @click.prevent="abrirModalLogin">Login</a>
            </li>
            <li v-if="usuarioAutenticado" class="nav-item">
              <a class="nav-link btn btn-outline-secondary fs-5" href="#" @click.prevent="cerrarSesion">Logout</a>
            </li>
          </ul>
        </div>


        <div v-if="usuarioAutenticado" class="d-none d-lg-block me-5">
          <h6 class="d-block text-center fs-5">
            Bienvenid@, {{ usuarioAutenticado?.usuario }}
          </h6>
        </div>
        <div v-else class="d-none d-lg-block">
          <h6 class="d-block text-center fs-5 ms-5 ps-5">

          </h6>
        </div>

      </div>
    </nav>
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
            <p v-if="errorLogin" class="text-danger mt-2">{{ errorLogin }}</p>
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
              <input type="password" v-model="formRegistro.password" class="form-control"
                placeholder="Ingrese una contraseña" />
            </div>
            <p v-if="errorRegistro" class="text-danger mt-2">{{ errorRegistro }}</p>
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
<style scoped>
h1 {
  cursor: pointer;
}

.active {
  background-color: transparent;
  color: rgb(0, 0, 0) !important;
  font-weight: bold;
  border: 1px solid grey;
}
</style>
