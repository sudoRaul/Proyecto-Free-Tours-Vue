<script setup>
import { ref, onMounted } from "vue";

const usuarios = ref([]);
const error = ref("");
const rolesDisponibles = ["admin", "guia", "cliente"];

// Función para obtener los usuarios de la API
async function obtenerUsuarios() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/usuarios");
    if (!response.ok) throw new Error("Error al obtener los usuarios");
    usuarios.value = await response.json();
  } catch (err) {
    error.value = err.message;
  }
}

// Función para actualizar el rol del usuario
async function actualizarRol(usuarioId, nuevoRol) {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/usuarios?id="+ usuarioId, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ rol: nuevoRol }),
    });
    if (!response.ok) throw new Error("Error al actualizar el rol");
    obtenerUsuarios(); // Recargar usuarios para reflejar cambios
  } catch (err) {
    error.value = err.message;
  }
}

async function eliminarUsuario(usuarioId) {
  try{
    const response = await fetch("http://localhost/APIFreetours/api.php/usuarios?id="+ usuarioId,{
      method: "DELETE",
    });

    if (!response.ok) throw new Error("Error al eliminar el usuario");
    obtenerUsuarios()
  }catch (err) {
        error.value = err.message;
    }
}
obtenerUsuarios(); // Recargar usuarios para reflejar los cambios
// Cargar usuarios al montar el componente
//onMounted(obtenerUsuarios);
</script>

<template>
  <div>
    <h2 class="text-center mt-5 mb-4">Gestión de Usuarios</h2>
    <p v-if="error" class="text-danger">{{ error }}</p>
    <table class="table table-striped mb-5 mt-3">
      <thead>
        <tr class="text-center">
          <th>ID</th>
          <th>Nombre</th>
          <th>Rol</th>
          <th>Eliminar Usuario</th>
        </tr>
      </thead>
      <tbody>
        <tr class="text-center"  v-for="usuario in usuarios" :key="usuario.id" >
          <td>{{ usuario.id }}</td>
          <td>{{ usuario.nombre }}</td>
          <td>
            <select v-model="usuario.rol" @change="actualizarRol(usuario.id, usuario.rol)" class="form-select">
              <option v-for="rol in rolesDisponibles" :key="rol" :value="rol">{{ rol }}</option>
            </select>
          </td>
          <td><button class="btn-delete col-7 mt-1 p-2" @click="eliminarUsuario(usuario.id)">❌ Eliminar</button></td>
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
