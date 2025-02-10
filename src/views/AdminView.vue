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
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Rol</th>
        </tr>
      </thead>
      <tbody>
        <tr  v-for="usuario in usuarios" :key="usuario.id" >
          <td>{{ usuario.id }}</td>
          <td>{{ usuario.nombre }}</td>
          <td>
            <select v-model="usuario.rol" @change="actualizarRol(usuario.id, usuario.rol)" class="form-select">
              <option v-for="rol in rolesDisponibles" :key="rol" :value="rol">{{ rol }}</option>
            </select>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
