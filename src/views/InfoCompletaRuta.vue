<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

// Inicializamos el router
const router = useRouter()
const route = useRoute();

//Cogemos los parámetro necesarios para hacer las peticiones
const idRuta = ref(route.params.id);
const isReservado = ref(route.params.logued);
const infoRuta = ref([]);
const numPersonas = ref(1);
let map;


const sesion = localStorage.getItem("sesion");
// Cogemos el id y el rol del usuario logueado
const cliente_id = sesion ? JSON.parse(sesion).id : null;
const cliente_rol = sesion ? JSON.parse(sesion).rol : null;


const email = sesion ? JSON.parse(sesion).email : null;

// Obtenemos la información de la ruta
function obtenerInfo() {
  fetch(`http://localhost/APIFreetours/api.php/rutas?id=${idRuta.value}`)
    .then(response => response.json())
    .then(function (data) {
      infoRuta.value = data;
      inicializarMapas();
    })
    .catch(error => console.error("Error:", error));
}

// Inicializamos el mapa
function inicializarMapas() {
  map = L.map("map").setView([parseFloat(infoRuta.value.latitud), parseFloat(infoRuta.value.longitud)], 13);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors",
  }).addTo(map);
  L.marker([infoRuta.value.latitud, infoRuta.value.longitud]).addTo(map).bindPopup(infoRuta.value.localidad).openPopup();
  map.setView([infoRuta.value.latitud, infoRuta.value.longitud], 13);
}

// Realizamos la reserva
async function reservarRuta() {
  //Comprobamos que el usuario esté logueado, y que introduzca un valor válido
  if (!email) {
    Swal.fire("Error", "No se pudo obtener el email del usuario. Inicia sesión nuevamente.", "error");
    numPersonas.value = 1;
    return;
  }
  if(numPersonas.value < 1){
    Swal.fire("Atención", "Por favor, ingrese un número entre 1 y 8", "warning");
    numPersonas.value = 1;
    return;
  }
  if(numPersonas.value > 8){
    Swal.fire("Atención", "El número máximo de asistentes por cada reserva es de 8 personas, inténtelo de nuevo", "warning");
    numPersonas.value = 1;
    return;
  }
  // Creamos el objeto con los datos de la reserva
  const reservaData = {
    ruta_id: idRuta.value,
    email: email, 
    num_personas: numPersonas.value,
  };

  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/reservas", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(reservaData),
    });

    const data = await response.json();

    if (response.ok) {
      Swal.fire("¡Reserva realizada!", "Tu reserva ha sido confirmada.", "success");
      router.push('/mis-reservas')
    } else {
      Swal.fire("Error", data.message || "No se pudo realizar la reserva. Inténtalo de nuevo.", "error");
    }
  } catch (error) {
    console.error("Error:", error);
    Swal.fire("Error", "No se pudo conectar con el servidor. Inténtalo de nuevo.", "error");
  }
}

// Comprobamos si el usuario está logueado
function comprobarLogin(){
  if(!cliente_id){
    Swal.fire({
      icon: "warning",
      title: "Atención",
      text: "Por favor, inicie sesión para realizar una reserva."
    });
    return;
  }
}
// Redirigimos a mis reservas al reservar
function volverReservas(){
  router.push('/mis-reservas')
}

// Cargamos la información de la ruta
onMounted(obtenerInfo);
</script>

<template>
  <div class="container mt-4 mb-5">
    <h1 class="text-center mb-1">{{ infoRuta.titulo }}</h1>

    <div class="row">
      <div class="col-lg-5 text-center mt-3">
        <img :src="infoRuta.foto" title="Imagen de la ruta" alt="Imagen de la ruta" class="rounded img-fluid shadow-lg">
      </div>

      <div class="col-lg-6 d-flex flex-column justify-content-center">
        <p class="fs-5"><strong>📅 Fecha:</strong> {{ infoRuta.fecha }}</p>
        <p class="fs-5"><strong>📍 Localidad:</strong> {{ infoRuta.localidad }}</p>
        <p class="fs-5"><strong>⌚ Horario:</strong> {{ infoRuta.hora }}</p>
        <p class="fs-5"><strong>📔 Descripción:</strong> {{ infoRuta.descripcion }}</p>

        <button v-if="isReservado" class="btn btn-primary" @click="volverReservas">Volver a Reservas</button>
        <strong v-else-if="cliente_rol!='cliente'" class="fs-5 text-primary">Inicie sesión como cliente para reservar una ruta</strong>
        <button v-else-if="cliente_id" class="btn btn-primary fs-4 mt-3" data-bs-toggle="modal" data-bs-target="#reservaModal"><strong>Reservar Ruta</strong></button>
        <button v-else class="btn btn-primary fs-4" @click="comprobarLogin"><strong>Reservar Ruta</strong></button>

      </div>
    </div>

    <div class="col-lg-12 mb-5 container-fluid h-100">
      <br>
      <a :href="`https://www.google.com/maps?q=${infoRuta.latitud},${infoRuta.longitud}`" target="_blank"
        class="btn btn-success text-center regular-button fs-5">
        📍 Ver en Google Maps
      </a>
      <div id="map" class="map-container mb-5 shadow"></div>
    </div>
  </div>

  <!-- Modal para realizar la reserva -->
  <div class="modal fade" id="reservaModal" tabindex="-1" aria-labelledby="reservaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reservaModalLabel">Reserva tu Ruta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for="numPersonas" class="form-label">Número de personas (máx. 8):</label>
          <input type="number" id="numPersonas" v-model="numPersonas" class="form-control" min="1" max="8" placeholder="Ingrese el número de asistentes">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" @click="reservarRuta" data-bs-dismiss="modal">Confirmar Reserva</button>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>

button {
  width: 40%;
}

.container {
  width: 60%;
  border: 4px dashed rgb(195, 255, 235)
}

.map-container {
  height: 290px;
  width: 100%;
  border-radius: 10px;
  margin-top: 20px;
}
.btn-close{
  margin-left: 30px;
  margin-right: -75px;
}
</style>