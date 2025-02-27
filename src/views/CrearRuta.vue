<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import router from "@/router";



//Inicializamos la lista de los guias
const listaGuias = ref([])
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
const address = ref('');
let map, marker;

onMounted(() => {
  map = L.map('map').setView([40.4168, -3.7038], 13); // Madrid por defecto
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
});
const searchLocation = async () => {
  if (!address.value) return;
  const response = await fetch(

    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address.
      value)}`
  );
  const data = await response.json();

  if (data.length > 0) {
    formData.value.latitud = data[0].lat;
    formData.value.longitud = data[0].lon;
    if (marker) marker.remove();
    marker = L.marker([formData.value.latitud, formData.value.longitud]).addTo(map)
      .bindPopup(address.value)
      .openPopup();
    map.setView([formData.value.latitud, formData.value.longitud], 13);
  } else {
    alert('Dirección no encontrada');
  }
};

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

async function filtrarGuias() {
  try{
    const response = await fetch(`http://localhost/APIFreetours/api.php/asignaciones?fecha=${formData.value.fecha}`)
    const data = await response.json();
    listaGuias.value = data;
    console.log(listaGuias.value)
  }catch(err){
    err.message
  }
}

function comprobarGuias() {
  if(!formData.value.fecha){
    Swal.fire({
      icon: "warning",
      title: "Atención",
      text: "Por favor, seleccione una fecha antes de buscar."
    });
    return;
  }
}
// Enviamos el fomrulario
async function enviarFormulario() {
  // Validamos que los campos no estén vacíos
  if (
    !formData.value.titulo ||
    !formData.value.localidad ||
    !formData.value.descripcion ||
    !formData.value.foto ||
    !formData.value.fecha ||
    !formData.value.hora ||
    !formData.value.latitud ||
    !formData.value.longitud ||
    !formData.value.guia
  ) {
    Swal.fire("Campos obligatorios", "Todos los campos son requeridos.", "warning");
    return;
  }

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

    Swal.fire("¡Éxito!", "Ruta creada correctamente.", "success");

  } catch (err) {
    Swal.fire("Error", "No se pudo enviar el formulario.", "error");

  } finally {
    // Se limpia el formulario siempre, independientemente del resultado.
    formData.value = {
      titulo: "",
      localidad: "",
      descripcion: "",
      foto: "",
      fecha: "",
      hora: "",
      latitud: "",
      longitud: "",
      guia_id: ""
    };
  }
}


</script>

<template>
  <div class="container mt-4">
    <div class="card p-4 shadow mb-5">
      <h2 class="mb-3 text-center">Formulario de Tour</h2>
      <form @submit.prevent="enviarFormulario">
        <div class="mb-3">
          <label for="titulo" class="form-label fs-4">Título *</label>
          <input aria-required="true" type="text" id="titulo" class="form-control" placeholder="Ej: Mar de olivos" v-model="formData.titulo"  />
        </div>

        <div class="mb-3">
          <label class="form-label fs-4" for="localidad">Localidad *</label>
          <input aria-required="true" type="text" id="localidad" class="form-control" placeholder="Ej: Arroyo del Ojanco" v-model="formData.localidad"/>
        </div>

        <div class="mb-3">
          <label class="form-label fs-4" for="description">Descripción *</label>
          <textarea class="form-control" aria-required="true" id="description" placeholder="Ej: Ruta a pie por la profundidad de los olivos" rows="3"
            v-model="formData.descripcion"></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label fs-4" for="foto">Foto *</label>
          <input type="text" aria-required="true" id="foto" class="form-control" placeholder="Ej: https://olivosJaen.png" v-model="formData.foto" />
        </div>

        <div class="mb-3">
          <label class="form-label fs-4" for="fecha">Fecha *</label>
          <input type="date" id="fecha" aria-required="true" @change="filtrarGuias" :min="fechaHoy" class="form-control" v-model="formData.fecha" />
        </div>

        <div class="mb-3">
          <label class="form-label fs-4" for="hora">Hora *</label>
          <select class="form-select" id="hora" v-model="formData.hora">
            <option value="" disabled>Seleccione una hora</option>
            <option v-for="hora in horasDisponibles" :key="hora" :value="hora">{{ hora }}</option>
          </select>
        </div>

        <!--<div class="mb-3">
          <label class="form-label fs-4">Latitud</label>
          <input type="text" class="form-control" placeholder="Latitud del punto de encuentro" v-model="formData.latitud" required />
        </div>

        <div class="mb-3">
          <label class="form-label fs-4">Longitud</label>
          <input type="text" class="form-control" placeholder="Longitud del punto de encuentro" v-model="formData.longitud" required />
        </div>
        <div class="mb-3">
          <label class="form-label fs-4" for="idGuia">Id del guía</label>
          <input @click.prevent="filtrarGuias" type="number" id="idGuia" class="form-control" placeholder="Id del guía asignado" v-model="formData.guia" required />
        </div> -->
        <div class="mb-3">
          <label class="form-label fs-4" for="guia">Introduzca una fecha antes de buscar guia disponible *</label>
          <select id="guia" class="form-control" @click="comprobarGuias" v-model="formData.guia">
            <option value="" disabled>Seleccione el id del guia</option>
            <option v-for="guia in listaGuias" :key="guia.id" :value="guia.id">{{ guia.nombre }}</option>
          </select>
        </div> 

        <div class="mb-4">
          <label class="form-label fs-4" for="punto">Punto de encuentro *</label>
          <input v-model="address" aria-required="true" id="punto" placeholder="Ej: Calle Fuentebuena, 23" class="form-control" />
          <input type="button" aria-level="Buscar ubicación" @click.prevent="searchLocation" value="Buscar Ubicación" class="mb-3 mt-3 btn btn-info">
          <div id="map" style="height: 200px;"></div>
        </div>
        <p title="Campos Obligatorios" class="text-danger">Los campos marcados con un * son obligatorios</p>
        <br>


        <button type="submit" aria-label="Envío formulario ruta" class="btn btn-primary w-100 mt-1">Enviar formulario de ruta</button>
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
input::placeholder, textarea::placeholder{
  font-size: 17px;
}
</style>
