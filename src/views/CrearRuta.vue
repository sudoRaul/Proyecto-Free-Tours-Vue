<script setup>
import { ref } from "vue";
import Swal from "sweetalert2";

// Inicializamos los valores del formulario de forma reactiva
const formData = ref({
  titulo: "",
  localidad: "",
  descripcion: "",
  foto: "",
  fecha: "",
  hora: "",
  latitud: "",
  longitud: "",
  guia: ""
});

// Obtener la fecha de hoy en formato YYYY-MM-DD
const fechaHoy = new Date().toISOString().split("T")[0];
// Inicializamos las horas disponibles para mostrarlas en las opciones al crear ruta
const horasDisponibles = ref([
  "10:00",
  "11:00",
  "12:00",
  "13:00",
  "14:00",
  "15:00",
  "16:00",
  "17:00",
  "18:00",
  "19:00",
  "20:00",
]);

async function enviarFormulario() {
  // Obtenemos la información del formulario
  const nuevaRuta = {
    titulo: formData.value.titulo,
    localidad: formData.value.localidad,
    descripcion: formData.value.descripcion,
    foto: formData.value.foto,
    fecha: formData.value.fecha,
    hora: formData.value.hora,
    latitud: formData.value.latitud,
    longitud: formData.value.longitud,
    guia_id: formData.value.guia
  };

  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/rutas", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(nuevaRuta)
    });

    if (!response.ok) {
      throw new Error("Error al enviar el formulario");
    }

    // Si se envía correctamente el formulario lo limpiamos
    formData.value = {
      titulo: "",
      localidad: "",
      descripcion: "",
      foto: "",
      fecha: "",
      hora: "",
      latitud: "",
      longitud: "",
      guia: ""
    };

    // Mostramos un mensaje de éxito
    Swal.fire("¡Éxito!", "Ruta creada correctamente.", "success");

  } catch (err) {
    // Mostramos un mensaje de error
    Swal.fire("Error", "No se pudo enviar el formulario.", "error");
  }
}
</script>

<template>
  <div class="container mt-4">
    <div class="card p-4 shadow mb-5">
      <h2 class="mb-3 text-center">Formulario de Tour</h2>
      <form @submit.prevent="enviarFormulario">
        <div class="mb-3">
          <label class="form-label">Título</label>
          <input type="text" class="form-control" placeholder="Título de la ruta" v-model="formData.titulo" required />
        </div>

        <div class="mb-3">
          <label class="form-label">Localidad</label>
          <input type="text" class="form-control" placeholder="Municipio de la ruta" v-model="formData.localidad" required />
        </div>

        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea class="form-control" placeholder="Descripción de la ruta" rows="3" v-model="formData.descripcion"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Foto</label>
          <input type="text" class="form-control" placeholder="URL de la imagen" v-model="formData.foto" required />
        </div>

        <div class="mb-3">
          <label class="form-label">Fecha</label>
          <input type="date" :min="fechaHoy" class="form-control" v-model="formData.fecha" required />
        </div>

        <div class="mb-3">
          <label class="form-label">Hora</label>
          <select class="form-select" v-model="formData.hora" required>
            <option value="" disabled>Seleccione una hora</option>
            <option v-for="hora in horasDisponibles" :key="hora" :value="hora">{{ hora }}</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Latitud</label>
          <input type="text" class="form-control" placeholder="Latitud del punto de encuentro" v-model="formData.latitud" required />
        </div>

        <div class="mb-3">
          <label class="form-label">Longitud</label>
          <input type="text" class="form-control" placeholder="Longitud del punto de encuentro" v-model="formData.longitud" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Id del guía</label>
          <input type="number" class="form-control" placeholder="Id del guía asignado" v-model="formData.guia" required />
        </div>
        

        <button type="submit" class="btn btn-primary w-100">Enviar</button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.card {
  max-width: 650px;
  margin: auto;
  border-radius: 10px;
}

.btn-primary {
  background: #28a745;
  border: none;
}

.btn-primary:hover {
  background: #218838;
}
</style>
