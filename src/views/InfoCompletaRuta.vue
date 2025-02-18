<script setup>
import { ref, onMounted, nextTick } from "vue";
import { useRoute } from "vue-router";
import Swal from "sweetalert2";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const route = useRoute();
const idRuta = ref(route.params.id);
const infoRuta = ref([]);
let map;


//Obtenemos la información de la ruta
function obtenerInfo(){
    
        fetch(`http://localhost/APIFreetours/api.php/rutas?id=${idRuta.value}`)
        .then(response => response.json())
        .then(function (data) {
            infoRuta.value = data;
            inicializarMapas();
        })
        .catch(error => console.error('Error:', error));

   
}

// Inicializamos el mapa
 function inicializarMapas() {
  
    map = L.map('map').setView([parseFloat(infoRuta.value.latitud), parseFloat(infoRuta.value.longitud)], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
  L.marker([infoRuta.value.latitud, infoRuta.value.longitud]).addTo(map)
      .openPopup();
    map.setView([infoRuta.value.latitud, infoRuta.value.longitud], 13);
  
}

onMounted(function(){
    obtenerInfo();
});
</script>
<template>
    <div class="container mt-4">
      <h1 class="text-center mb-4">{{ infoRuta.titulo }}</h1>
  
      <div class="row">
        <div class="col-lg-4 text-center mt-4">
          <img :src="infoRuta.foto" title="Imagen de la ruta" alt="Imagen de la ruta" class="rounded img-fluid shadow-lg">
        </div>
  
        <div class="col-lg-6 d-flex flex-column justify-content-center">
          <p class="fs-5"><strong>📅 Fecha:</strong> {{ infoRuta.fecha }}</p>
          <p class="fs-5"><strong>📍 Localidad:</strong> {{ infoRuta.localidad }}</p>
          <p class="fs-5"><strong>⌚ Horario:</strong> {{ infoRuta.hora }}</p>
          <button class="btn btn-primary">Reservar ruta</button>
        </div>
        
        
      </div>
      <div class="col-lg-12 mb-5">
        <br>
            <h3 class="text-center">📌 <strong>Punto de encuentro:</strong> {{ infoRuta.longitud }} - {{ infoRuta.latitud }}</h3>
          <div id="map" class="map-container mb-5 shadow"></div>
        </div>
    </div>
  </template>
  
  <style scoped>
  h3:hover{
    color: blue;
  }button{
    width: 40%;
  }
  .container{
    border: 4px dashed rgb(195, 255, 235)
  }
  .map-container {
    height: 290px;
    width: 100%;
    border-radius: 10px;
    margin-top: 20px;
  }
  </style>
  