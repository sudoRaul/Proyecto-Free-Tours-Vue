<script setup>
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import router from "@/router";


const usuarios = ref([]);
const rolesDisponibles = ["admin", "guia", "cliente"];

const sesion = localStorage.getItem("sesion");
const rol = sesion ? JSON.parse(sesion).rol : null;

// Redirección si no es guía
if (rol !== "admin") {
  router.push("/");
}

// Paginación
const usuariosPorPagina = 5;
const paginaActual = ref(1);

// Obtener usuarios
async function obtenerUsuarios() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/usuarios");
    if (!response.ok) throw new Error("Error al obtener los usuarios");
    usuarios.value = await response.json();
  } catch (err) {
    console.error(err.message);
  }
}

// Actualizar rol
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

// Eliminar usuario
async function eliminarUsuario(usuarioId) {
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

const usuariosPaginados = computed(() => {
  const inicio = (paginaActual.value - 1) * usuariosPorPagina;
  return usuarios.value.slice(inicio, inicio + usuariosPorPagina);
});

// Cambiar de página
function cambiarPagina(nuevaPagina) {
  if (nuevaPagina > 0 && nuevaPagina <= totalPaginas.value) {
    paginaActual.value = nuevaPagina;
  }
}

// Cargar usuarios al montar el componente
onMounted(obtenerUsuarios);
</script>

<template>
  <div>
    <h2 class="text-center mt-5 mb-4 fs-1">Gestión de Usuarios</h2>

    <table class="table table-striped mb-5 mt-3">
      <thead>
        <tr class="text-center fs-4">
          <th>ID</th>
          <th>Nombre</th>
          <th>Rol</th>
          <th>Eliminar</th>
        </tr>
      </thead>
      <tbody>
        <tr class="text-center fs-5" v-for="usuario in usuariosPaginados" :key="usuario.id">
          <td>{{ usuario.id }}</td>
          <td>{{ usuario.nombre }}</td>
          <td>
            <select v-model="usuario.rol" @change="actualizarRol(usuario.id, $event.target.value)" class="form-select">
              <option v-for="rol in rolesDisponibles" :key="rol" :value="rol">{{ rol }}</option>
            </select>
          </td>
          <td>
            <button class="btn-delete col-7 mt-1 p-2" @click="eliminarUsuario(usuario.id)">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>

    
    <nav aria-label="Paginación de usuarios" class="mb-3 pb-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: paginaActual === 1 }">
          <button class="page-link" @click="cambiarPagina(paginaActual - 1)">Anterior</button>
        </li>

        <li class="page-item" v-for="pagina in totalPaginas" :key="pagina" :class="{ active: pagina === paginaActual }">
          <button class="page-link" @click="cambiarPagina(pagina)">{{ pagina }}</button>
        </li>

        <li class="page-item" :class="{ disabled: paginaActual === totalPaginas }">
          <button class="page-link" @click="cambiarPagina(paginaActual + 1)">Siguiente</button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<style scoped>
.btn-delete {
  background-color: #ff4d4d;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
  transition: background 0.3s ease;
}

.btn-delete:hover {
  background-color: #cc0000;
}

</style>
