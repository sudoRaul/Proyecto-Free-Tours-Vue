<script setup>
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import router from "@/router";

// Inicializamos el array de usuarios y los roles disponibles
const usuarios = ref([]);
const rolesDisponibles = ["admin", "guia", "cliente"];

// Cogemos el rol para evitar que se pueda entrar en otras vistas
const sesion = localStorage.getItem("sesion");
const rol = sesion ? JSON.parse(sesion).rol : null;

// Redireccianamos al home si no es guía
if (rol !== "admin") {
  router.push("/");
}

// Constantes para la paginación
const usuariosPorPagina = 5;
const paginaActual = ref(1);

// Obtenemos los usuarios de la BBDD
async function obtenerUsuarios() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/usuarios");
    if (!response.ok) throw new Error("Error al obtener los usuarios");
    usuarios.value = await response.json();
  } catch (err) {
    console.error(err.message);
  }
}

// Actualizamos el rol
async function actualizarRol(usuarioId, nuevoRol) {
  try {
    const response = await fetch(`http://localhost/APIFreetours/api.php/usuarios?id=${usuarioId}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ rol: nuevoRol }),
    });

    if (!response.ok) throw new Error("Error al actualizar el rol");

    Swal.fire({
      icon: "success",
      title: "Rol actualizado",
      text: `El usuario ahora es ${nuevoRol}`,
      timer: 2000,
      showConfirmButton: false,
    });

    obtenerUsuarios();
  } catch (err) {
    console.error(err.message);
  }
}

// Eliminamos al usuario
async function eliminarUsuario(usuarioId) {
  //Antes de eliminar, mostramos un mensaje de confirmación
  const confirmacion = await Swal.fire({
    title: "¿Estás seguro?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  });

  if (confirmacion.isConfirmed) {
    try {
      const response = await fetch(`http://localhost/APIFreetours/api.php/usuarios?id=${usuarioId}`, {
        method: "DELETE",
      });

      if (!response.ok) throw new Error("Error al eliminar el usuario");

      Swal.fire({
        icon: "success",
        title: "Usuario eliminado",
        text: "El usuario ha sido eliminado correctamente.",
        timer: 2000,
        showConfirmButton: false,
      });

      obtenerUsuarios();
    } catch (err) {
      console.error(err.message);
    }
  }
}

//Cogemos el número total de páginas dividiendo el total de usuarios entre los usuarios por pagina
const totalPaginas = computed(() => Math.ceil(usuarios.value.length / usuariosPorPagina));

//Usamos una propiedad computada cuyo valor depende de la página actual y de los usuarios
const usuariosPaginados = computed(() => {
  const inicio = (paginaActual.value - 1) * usuariosPorPagina;
  return usuarios.value.slice(inicio, inicio + usuariosPorPagina);
});

// Cambiamos de página
function cambiarPagina(nuevaPagina) {
  if (nuevaPagina > 0 && nuevaPagina <= totalPaginas.value) {
    paginaActual.value = nuevaPagina;
  }
}

// Cargamos los usuarios 
onMounted(obtenerUsuarios);
</script>

<template>
  <div>
    <h2 class="text-center mt-5 mb-4 fs-1">Gestión de Usuarios</h2>

    <table class="table table-striped table-hover mb-5 mt-3">
      <thead>
        <tr class="text-center fs-4">
          <th scope="col" id="id">ID</th>
          <th scope="col" id="nombre">Nombre</th>
          <th scope="col" id="rol">Rol</th>
          <th scope="col" id="eliminar">Eliminar</th>
        </tr>
      </thead>
      <tbody>
        <tr class="text-center fs-5" v-for="(usuario, index) in usuariosPaginados" :key="usuario.id">
          <td headers="id">{{ usuario.id }}</td>
          <td headers="nombre">{{ usuario.nombre }}</td>
          <td headers="rol">
            <select v-model="usuario.rol" @change="actualizarRol(usuario.id, $event.target.value)" class="form-select" :disabled="usuario.id == 1">
              <option v-for="rol in rolesDisponibles" :key="rol" :value="rol">{{ rol }}</option>
            </select>
          </td>
          <td headers="eliminar">
            <button class="btn btn-danger col-7 mt-1 p-2" @click="eliminarUsuario(usuario.id)" :disabled="usuario.id == 1">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>

    
    <nav aria-label="Paginación de usuarios" class="mb-3 pb-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: paginaActual === 1 }">
          <button class="page-link fs-5" @click="cambiarPagina(paginaActual - 1)">Anterior</button>
        </li>

        <li class="page-item" v-for="pagina in totalPaginas" :key="pagina" :class="{ active: pagina === paginaActual }">
          <button class="page-link fs-5" @click="cambiarPagina(pagina)">{{ pagina }}</button>
        </li>

        <li class="page-item" :class="{ disabled: paginaActual === totalPaginas }">
          <button class="page-link fs-5" @click="cambiarPagina(paginaActual + 1)">Siguiente</button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<style scoped>
.btn-danger {
  border-radius: 5px;
  transition: background 0.3s ease;
}

.btn-danger:hover {
  scale: 1.01;
  background-color: #ff0000;
}


</style>
