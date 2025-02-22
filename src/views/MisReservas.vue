<script setup>
import { ref, onMounted, nextTick } from "vue";
import Swal from "sweetalert2";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import NoData from "@/components/NoData.vue";


const listaRutas = ref([]);
const error = ref("");
const emailUsuario = JSON.parse(localStorage.getItem("sesion")).email;

// Función para obtener las rutas desde la API
async function obtenerRutas() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/reservas?email=" + emailUsuario);
    if (!response.ok) throw new Error("Error al obtener las rutas");

    const data = await response.json();
    listaRutas.value = data; // Guardamos las rutas en la variable reactiva

    // Esperamos a que se actualice el DOM antes de inicializar los mapas
    await nextTick();
    inicializarMapas();
  } catch (err) {
    error.value = err.message;
  }
}

// Inicializamos los mapas en cada ruta
function inicializarMapas() {
  listaRutas.value.forEach((ruta) => {
    const mapId = `map-${ruta.id}`;

    const map = L.map(mapId).setView([ruta.ruta_latitud, ruta.ruta_longitud], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    // Agregar marcador con popup
    L.marker([ruta.ruta_latitud, ruta.ruta_longitud])
      .addTo(map)
      .bindPopup(`<b>${ruta.ruta_titulo}</b><br>${ruta.ruta_localidad}`)
      .openPopup();
  });
}

// Función para eliminar una ruta con confirmación de SweetAlert2
async function eliminarRuta(rutaId) {
  const confirmacion = await Swal.fire({
    title: "¿Está seguro que quiere cancelar la ruta?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  });

  if (confirmacion.isConfirmed) {
    try {
      const response = await fetch(`http://localhost/APIFreetours/api.php/usuarios?id=${rutaId}`, {
        method: "DELETE",
      });

      if (!response.ok) throw new Error("Error al cancelar la ruta");

      // Mostrar mensaje de éxito
      Swal.fire({
        icon: "success",
        title: "Ruta eliminada",
        text: "La ruta ha sido eliminada correctamente.",
        timer: 2000,
        showConfirmButton: false,
      });

      // Actualizar la lista de rutas
      obtenerRutas();
    } catch (err) {
      error.value = err.message;
      Swal.fire({
        icon: "error",
        title: "Error",
        text: err.message,
      });
    }
  }
}

// Llamamos a la función para mostrar las rutas al cargar el componente
//obtenerRutas()
onMounted(function(){
  obtenerRutas()
});
</script>

<template>
  <div v-if="listaRutas.length > 0" class="container">
    <h1 class="text-center mb-4 mt-4">Mis Reservas</h1>

    <p v-if="error" class="text-red-500">{{ error }}</p>

    <div class="tarjetas justify-content-between col-xs-12 col-md-12 col-lg-12">
      <div v-for="ruta in listaRutas" :key="ruta.id" class="bg-white shadow rounded row pt-4 pb-5 col-xs-12 mb-4">
        <h2 class="text-center mb-4">{{ ruta.ruta_titulo }}</h2>

        <img :src="ruta.foto" alt="Imagen de la ruta" title="Imagen de la ruta" class="rounded col-xl-8 col-lg-10 col-md-12">

        <div class="col 4">
          <div class="col-12 row">
            <p class="text-gray-700 font-semibold col-5">📅 {{ ruta.ruta_fecha }}</p>
            <p class="text-gray-700 font-semibold col-5">🏙️ {{ ruta.ruta_localidad }}</p>
            <p class="text-gray-700 font-semibold col-5">⌚ {{ ruta.ruta_hora }}</p>
          </div>
          <button aria-label="Eliminar reserva" @click="eliminarRuta(ruta.id)" class="btn-delete col-7 mt-1 p-2">❌ Eliminar reserva</button>
        </div>
        <div :id="'map-' + ruta.id" class="map-container"></div>
      </div>
    </div>
  </div>
  <NoData v-else mensaje="No se encontraron rutas" submensaje="Reserve alguna ruta para previsualizarlas en este apartado." />

</template>

<style scoped>
.map-container {
  height: 300px;
  width: 100%;
  border-radius: 10px;
  margin-top: 20px;
}

.btn-delete {
  background-color: #ff4d4d;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
  transition: background 0.3s ease;
}

.btn-delete:hover {
  background-color: #cc0000;
}
</style>
