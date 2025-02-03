<script setup>
import {ref} from "vue"
const emit = defineEmits(["sesionIniciada"])
const form = ref({ usuario: '', password: '' });
const error = ref('');


async function iniciarSesion() {
  try {
    const response = await fetch('/usuarios.json');
    
    const usuarios = await response.json();

    const usuarioEncontrado = usuarios.find(
      (u) => u.usuario === form.value.usuario && u.password === form.value.password
    );
    
    if (usuarioEncontrado) {
       

      emit("sesionIniciada", {
        usuario:usuarioEncontrado.usuario,
        rol: usuarioEncontrado.rol
      })
      //TODO: HABRÍA QUE NOTIFICAR A APP.VUE CON UN EMIT PARA QUE SEPA QUE LA SESIÓN ESTÁ INICIADA
      error.value = '';
      
      
    } else {
      error.value = 'Usuario o contraseña incorrectos';
    }
  } catch (err) {
    error.value = 'Error al cargar los datos';
  }
}
</script>
<template>
    <form>
        <label for="name"></label>
        <input v-model="form.usuario" type="text" placeholder="Nombre">
        <label for="pass"></label>
        <input v-model="form.password" type="password" placeholder="Contraseña">
        <p v-if="error" class="text-danger mt-2">{{ error }}</p>
        <button @click.prevent="iniciarSesion">Iniciar sesión</button>
    </form>
</template>