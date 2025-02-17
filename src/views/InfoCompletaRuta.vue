<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import Swal from "sweetalert2";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const route = useRoute();
const idRuta = ref(route.params.id);
const listaRutas = ref([]);



async function obtenerInfo(){
    try{
        const response = await fetch(`http://localhost/APIFreetours/api.php/rutas?id=${idRuta.value}`)
        if (!response.ok) throw new Error("Error al obtener las rutas");
        
        const data = await response.json();
        listaRutas.value = data; // Guardamos la información de la ruta

        
    } catch (err) {
        err.message;
    }
}

// Inicializamos los mapas en cada ruta
function inicializarMapas() {
  
    const mapId = `map-${listaRutas.value.id}`;

    const map = L.map(mapId).setView([listaRutas.value.latitud, listaRutas.value.longitud], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    // Agregar marcador con popup
    L.marker([listaRutas.value.latitud, listaRutas.value.longitud])
      .addTo(map)
      .bindPopup(`<b>${listaRutas.value.ruta_titulo}</b><br>${listaRutas.value.ruta_localidad}`)
      .openPopup();
  
}

onMounted(function(){
    obtenerInfo()
    inicializarMapas()
})
</script>
<template>
    <h1 class="text-center mt-4">{{listaRutas.titulo}}</h1>
    <div class="row">
        <img :src="listaRutas.foto" title="Imagen de la ruta" alt="Imagen de la ruta" class="rounded col-3 ms-5">
                
                <div class="col-7 row">
                    <p class="text-gray-700 font-semibold col-12 fs-5">📅 {{ listaRutas.fecha }}</p>
                    <p class="text-gray-500 col-12 fs-5">📍 {{ listaRutas.localidad }}</p>
                    <p class="text-gray-500 col-12 fs-5">⌚ {{ listaRutas.fecha }}</p>
                </div>
                <div :id="'map-' + listaRutas.id" class="map-container"></div>

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