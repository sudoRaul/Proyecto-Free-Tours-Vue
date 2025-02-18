<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";

const usuarios = ref([]);
const rolesDisponibles = ["admin", "guia", "cliente"];

// Obtenenemos los usuarios
async function obtenerUsuarios() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/usuarios");
    if (!response.ok) throw new Error("Error al obtener los usuarios");
    usuarios.value = await response.json();
  } catch (err) {
    error.value = err.message;
  }
}

// Actualizamos el rol del usuario
async function actualizarRol(usuarioId, nuevoRol) {
  try {
    const response = await fetch(`http://localhost/APIFreetours/api.php/usuarios?id=${usuarioId}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ rol: nuevoRol }),
    });

    if (!response.ok) throw new Error("Error al actualizar el rol");

    // Mostramos un mensaje de éxito
    Swal.fire({
      icon: "success",
      title: "Rol actualizado",
      text: `El usuario ahora es ${nuevoRol}`,
      timer: 2000,
      showConfirmButton: false,
    });

    // Recargamos usuarios para mostrar los cambios
    obtenerUsuarios();
  } catch (err) {
    error.value = err.message;
    Swal.fire({
      icon: "error",
      title: "Error",
      text: err.message,
    });
  }
}

// Eliminamos un usuario 
async function eliminarUsuario(usuarioId) {
  //Preguntamos si está seguro de eliminar el usuario
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

      // Mostramos un mensaje de éxito
      Swal.fire({
        icon: "success",
        title: "Usuario eliminado",
        text: "El usuario ha sido eliminado correctamente.",
        timer: 2000,
        showConfirmButton: false,
      });

      // Recargamos para actualizar los usuarios
      obtenerUsuarios();
    } catch (err) {
      error.value = err.message;
      Swal.fire({
        icon: "error",
        title: "Error",
        text: err.message,
      });
    }
  }
}

// Cargamos los usuarios al montar el componente
obtenerUsuarios()
//onMounted(obtenerUsuarios);
</script>

<template>
  <div>
    <h2 class="text-center mt-5 mb-4">Gestión de Usuarios</h2>
    <table class="table table-striped mb-5 mt-3">
      <thead>
        <tr class="text-center">
          <th scope:col id="id" scope="col">ID</th>
          <th scope:col id="name" scope="col">Nombre</th>
          <th scope:col id="rol" scope="col">Rol</th>
          <th scope:col id="eliminar" scope="col">Eliminar Usuario</th>
        </tr>
      </thead>
      <tbody>
        <tr class="text-center" v-for="usuario in usuarios" :key="usuario.id">
          <td headers="id">{{ usuario.id }}</td>
          <td headers="name">{{ usuario.nombre }}</td>
          <td headers="rol">
            <select v-model="usuario.rol" @change="actualizarRol(usuario.id, $event.target.value)" class="form-select">
              <option v-for="rol in rolesDisponibles" :key="rol" :value="rol">{{ rol }}</option>
            </select>
          </td>
          <td headers="eliminar">
            <button class="btn-delete col-7 mt-1 p-2" aria-label="Eliminar usuario" @click="eliminarUsuario(usuario.id)">❌ Eliminar usuario</button>
          </td>
        </tr>
      </tbody>
    </table>
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
