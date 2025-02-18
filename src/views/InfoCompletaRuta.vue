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
    <h1 class="text-center mt-4">{{infoRuta.titulo}}</h1>
    <div class="row">
        <img :src="infoRuta.foto" title="Imagen de la ruta" alt="Imagen de la ruta" class="rounded col-3 ms-5">
                
                <div class="col-7 row">
                    <p class="text-gray-700 font-semibold col-12 fs-5">📅 {{ infoRuta.fecha }}</p>
                    <p class="text-gray-500 col-12 fs-5">📍 {{ infoRuta.localidad }}</p>
                    <p class="text-gray-500 col-12 fs-5">⌚ {{ infoRuta.fecha }}</p>
                </div>
                <div id="map" class="map-container"></div>

    </div>
</template>
<style scoped>
.map-container {
  height: 300px;
  width: 100%;
  border-radius: 10px;
  margin-top: 20px;
}
</style>