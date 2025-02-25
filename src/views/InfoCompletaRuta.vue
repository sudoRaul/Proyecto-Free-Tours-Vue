<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";
import L from "leaflet";
import "leaflet/dist/leaflet.css";


const router = useRouter()
const route = useRoute();
const idRuta = ref(route.params.id);
const isReservado = ref(route.params.logued);
const infoRuta = ref([]);
const numPersonas = ref(1);
let map;


const sesion = localStorage.getItem("sesion");
const cliente_id = sesion ? JSON.parse(sesion).id : null;

const email = sesion ? JSON.parse(sesion).email : null;

const url = route.path.split("/")[1]

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
  L.marker([infoRuta.value.latitud, infoRuta.value.longitud]).addTo(map).openPopup();
  map.setView([infoRuta.value.latitud, infoRuta.value.longitud], 13);
}

// Realizamos la reserva
async function reservarRuta() {

  if (!email) {
    Swal.fire("Error", "No se pudo obtener el email del usuario. Inicia sesión nuevamente.", "error");
    return;
  }

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
    } else {
      Swal.fire("Error", data.message || "No se pudo realizar la reserva. Inténtalo de nuevo.", "error");
    }
  } catch (error) {
    console.error("Error:", error);
    Swal.fire("Error", "No se pudo conectar con el servidor. Inténtalo de nuevo.", "error");
  }
}

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

function volverReservas(){
  router.push('/mis-reservas')
}


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
        <button v-if="isReservado" class="btn btn-primary" @click="volverReservas">Volver a Reservas</button>
        <button v-else-if="cliente_id" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reservaModal">Reservar ruta</button>
        <button v-else class="btn btn-primary" @click="comprobarLogin">Reservar ruta</button>

      </div>
    </div>

    <div class="col-lg-12 mb-5 container-fluid h-100">
      <br>
      <a :href="`https://www.google.com/maps?q=${infoRuta.latitud},${infoRuta.longitud}`" target="_blank"
        class="btn btn-success text-center regular-button">
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
          <label for="numPersonas" class="form-label">Número de personas (máx. 4):</label>
          <input type="number" id="numPersonas" v-model="numPersonas" class="form-control" min="1" max="4">
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
</style>