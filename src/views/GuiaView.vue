<script setup>
import { ref, onMounted, computed, nextTick } from "vue";
import NoData from "@/components/NoData.vue";
import router from "@/router";
import L from "leaflet";

const listaRutas = ref([]);
const error = ref(null);
const sesion = localStorage.getItem("sesion");
const idGuia = sesion ? JSON.parse(sesion).id : null;

const rol = sesion ? JSON.parse(sesion).rol : null;

// Redirección si no es guía
if (rol !== "guia") {
  router.push("/");
}

function obtenerRutas() {
  fetch(`http://localhost/APIFreetours/api.php/asignaciones?guia_id=${idGuia}`)
    .then((response) => {
      if (!response.ok) throw new Error("Error al obtener las rutas");
      return response.json();
    })
    .then(async (data) => {
      listaRutas.value = data;
      await nextTick();
      inicializarMapas();
    })
    .catch((err) => {
      console.error("Error:", err);
      error.value = err.message;
    });
}

onMounted(obtenerRutas);


const currentPage = ref(1);
const itemsPerPage = 2;
const totalPages = computed(() => Math.ceil(listaRutas.value.length / itemsPerPage));

const paginatedRutas = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return listaRutas.value.slice(start, start + itemsPerPage);
});

function cambiarPagina(pagina) {
  if (pagina >= 1 && pagina <= totalPages.value) {
    currentPage.value = pagina;
    nextTick(() => {
      inicializarMapas();
    });
  }
}

// Inicializamos los mapas para cada ruta
function inicializarMapas() {
  paginatedRutas.value.forEach((ruta) => {
    const mapId = `map-${ruta.ruta_id}`;
    const mapElement = document.getElementById(mapId);

    if (!mapElement) {
      console.warn(`Elemento no encontrado: ${mapId}`);
      return;
    }

    const map = L.map(mapId).setView([parseFloat(ruta.ruta_latitud), parseFloat(ruta.ruta_longitud)], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "&copy; OpenStreetMap contributors",
    }).addTo(map);

    L.marker([parseFloat(ruta.ruta_latitud), parseFloat(ruta.ruta_longitud)])
      .addTo(map)
      .bindPopup(ruta.ruta_titulo)
      .openPopup();
  });
}
</script>

<template>
  <div v-if="listaRutas.length > 0 && idGuia">
    <h2 class="text-center mt-5 mb-5">Listado de rutas pendientes</h2>
    <p v-if="error" class="text-red-500">{{ error }}</p>

    <div class="row justify-content-around">
      <div v-for="ruta in paginatedRutas" :key="ruta.ruta_id" class="col-md-5 row">
        <div class="tarjeta bg-white shadow rounded p-4 row text-start">
          <h3 class="text-center mb-3 col-md-12">{{ ruta.ruta_titulo }}</h3>
          <img :src="ruta.ruta_foto" alt="Imagen de la ruta" class="img-fluid col-md-10">

          <main class="col-12 row mt-3">
            <p class="text-gray-700 font-semibold col-6 fs-5 border rounded text-center bg-success ">📍 {{
              ruta.ruta_localidad }}</p>
            <p class="text-gray-700 font-semibold col-6 fs-5 border rounded text-center bg-success">📅 {{
              ruta.ruta_fecha }}</p>
          </main>
          <div class="row col-12 justify-content-around">
            <div class="col-4 border rounded">
              <p class="font-semibold mt-3 col-12 fs-5 text-center"><strong>🧑‍🤝‍🧑 Asistentes</strong></p>
              <table class="table col-12">
                <thead>
                  <tr>
                    <th class="fs-6 text-center">Nombre</th>
                    <th class="fs-6 text-center">Nº Asistentes</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="reserva in ruta.reservas" :key="reserva.reserva_id">
                    <td class="text-center">{{ reserva.cliente.nombre }}</td>
                    <td class="text-center">{{ reserva.num_personas }}</td>
                  </tr>
                </tbody>
              </table>

            </div>
            <div :id="'map-' + ruta.ruta_id" class="map-container mb-5 shadow col-md-5 mt-4 "></div>
          </div>
        </div>
      </div>
    </div>

    <nav v-if="totalPages > 1" class="mt-4">
      <ul class="pagination justify-content-center">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button class="page-link fs-5" @click="cambiarPagina(currentPage - 1)">Anterior</button>
        </li>

        <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: currentPage === page }">
          <button class="page-link fs-5" @click="cambiarPagina(page)">{{ page }}</button>
        </li>

        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button class="page-link fs-5" @click="cambiarPagina(currentPage + 1)">Siguiente</button>
        </li>
      </ul>
    </nav>
  </div>

  <NoData v-else mensaje="No se encontraron rutas" submensaje="Actualmente no tiene rutas pendientes." />
</template>

<style scoped>
.tarjeta {
  background: linear-gradient(180deg, rgba(231, 255, 216, 0.8) 0%, rgba(227, 238, 229, 0.8), rgba(233, 240, 236, 0.8) 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 15px;
  border-radius: 10px;
}


.map-container {
  height: 250px;
  width: 65%;
  border-radius: 10px;
}

li {
  list-style-type: number;
}

nav>ul>li {
  list-style-type: none;
}
td, th{
  background-color: transparent;
}
tr:hover{
  background-color: #c3edfa;
}
</style>
