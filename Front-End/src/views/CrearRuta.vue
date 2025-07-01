<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import router from "@/router";

// Cogemos el rol para evitar que se pueda entrar en otras vistas

const sesion = localStorage.getItem("sesion");
const rol = sesion ? JSON.parse(sesion).rol : null;

// Redireccionamos si no es admin
if (rol !== "admin") {
  router.push("/");
}



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

// Obtenemos la fecha de hoy en formato YYYY-MM-DD
const fechaHoy = new Date().toISOString().split("T")[0];
const address = ref('');
let map, marker;

// Cargamos el mapa antes que la vista para evitar errores
onMounted(() => {
  map = L.map('map').setView([40.4168, -3.7038], 13); 
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
    Swal.fire("Error", "No se ha encontrado la dirección indicada", "error");
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
// Filtramos los guías disponibles en la fecha seleccionada
async function filtrarGuias() {
  try{
    const response = await fetch(`http://localhost/APIFreetours/api.php/asignaciones?fecha=${formData.value.fecha}`)
    const data = await response.json();
    listaGuias.value = data;
    //console.log(listaGuias.value)
  }catch(err){
    err.message
  }
}
// Comprobamos que se haya introducido la fecha antes de buscar guías
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
  //Creamos el objeto con los datos del formulario
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
  <div class="container mt-5">
    <div class="card p-4 shadow-lg mb-5 border-0">
      <h2 class="mb-4 text-center text-primary">Formulario de Tour</h2>
      <form @submit.prevent="enviarFormulario">

        <div class="form-floating mb-4">
          <input aria-required="true" type="text" id="titulo" class="form-control form-control-lg shadow-sm pt-5 pb-3 pt-5 pb-3" placeholder="Ej: Mar de olivos" v-model="formData.titulo" />
          <label for="titulo" class="form-label fs-5 text-secondary">Título <span class="text-danger">*</span></label>
        </div>

        <div class="form-floating mb-4">
          <input aria-required="true" type="text" id="localidad" class="form-control form-control-lg shadow-sm pt-5 pb-3" placeholder="Ej: Arroyo del Ojanco" v-model="formData.localidad"/>
          <label for="localidad" class="form-label fs-5 text-secondary">Localidad <span class="text-danger">*</span></label>

        </div>

        <div class="form-floating mb-4">
          <textarea class="form-control form-control-lg shadow-sm pt-5  pb-3" aria-required="true" id="description" placeholder="Ej: Ruta a pie por la profundidad de los olivos" v-model="formData.descripcion"></textarea>
          <label for="description" class="form-label fs-5 text-secondary">Descripción <span class="text-danger">*</span></label>

        </div>

        <div class="form-floating mb-4">
          <input type="text" aria-required="true" id="foto" class="form-control form-control-lg shadow-sm pt-5 pb-3" placeholder="Ej: https://olivosJaen.png" v-model="formData.foto" />
          <label for="foto" class="form-label fs-5 text-secondary">Foto <span class="text-danger">*</span></label>

        </div>

        <div class="mb-4">
          <label for="fecha" class="form-label fs-5 text-secondary">Fecha <span class="text-danger">*</span></label>
          <input type="date" id="fecha" aria-required="true" @change="filtrarGuias" :min="fechaHoy" class="form-control form-control-lg shadow-sm" v-model="formData.fecha" />
        </div>

        <div class="mb-4">
          <label for="hora" class="form-label fs-5 text-secondary">Hora <span class="text-danger">*</span></label>
          <select class="form-select form-select-lg shadow-sm" id="hora" v-model="formData.hora">
            <option value="" disabled>Seleccione una hora</option>
            <option v-for="hora in horasDisponibles" :key="hora" :value="hora">{{ hora }}</option>
          </select>
        </div>

        <div class="mb-4">
          <label for="guia" class="form-label fs-5 text-secondary">Guía <span class="text-danger">*</span></label>
          <select id="guia" class="form-select form-select-lg shadow-sm" @click="comprobarGuias" v-model="formData.guia">
            <option value="" disabled>Seleccione el guía</option>
            <option v-for="guia in listaGuias" :key="guia.id" :value="guia.id">{{ guia.nombre }}</option>
            <option v-if="listaGuias.length == 0" value="" disabled>No hay guías disponibles en esta fecha</option>
          </select>
        </div> 

        <div class="form-floating mb-4">
          <input v-model="address" aria-required="true" id="punto" placeholder="Ej: Calle Fuentebuena, 23" class="form-control form-control-lg shadow-sm pt-5 pb-3" />
          <label for="punto" class="form-label fs-5 text-secondary">Punto de Encuentro <span class="text-danger">*</span></label>
          <input type="button" aria-label="Buscar ubicación" @click.prevent="searchLocation" value="Buscar Ubicación" class="btn btn-info w-100 mt-3">
          <div id="map" style="height: 300px;" class="mt-3 rounded"></div>
        </div>

        <p class="text-center text-muted">Los campos marcados con un <span class="text-danger">*</span> son obligatorios</p>

        
        <button type="submit" aria-label="Envío formulario ruta" class="btn btn-primary w-100 mt-3 py-3 fs-5"><strong>Enviar formulario de ruta</strong></button>
      </form>
    </div>
  </div>
</template>
<style scoped>
form {
  background: linear-gradient(135deg, #f3e7e9, #e3eeff);
  padding: 2rem;
  border-radius: 15px;
  box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
}

.form-control, .form-select {
  border-radius: 10px;
  border: none;
  box-shadow: inset 0px 0px 5px rgba(0, 0, 0, 0.1);
}

.form-control:focus, .form-select:focus {
  box-shadow: 0px 0px 10px rgba(142, 45, 226, 0.5);
}

.btn-primary {
  background: linear-gradient(135deg, #56d5ee, #8549fc);
  border: none;
  transition: all 0.3s ease-in-out;
}
.btn-primary:hover {
  background: rgb(61, 61, 252);
}
</style>

