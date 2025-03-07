<script setup>
import { ref, onMounted, computed, nextTick } from "vue";
import NoData from "@/components/NoData.vue";
import router from "@/router";
import L from "leaflet";
import { Modal } from "bootstrap"; // Importamos Bootstrap para manejar el modal
import Swal from "sweetalert2";


//Inicializamos las rutas y el error
const listaRutas = ref([]);
const error = ref(null);

// Cogemos el rol para evitar que se pueda entrar en otras vistas
const sesion = localStorage.getItem("sesion");
const idGuia = sesion ? JSON.parse(sesion).id : null;

const rol = sesion ? JSON.parse(sesion).rol : null;

// Redireccionamos a home si no es guía
if (rol !== "guia") {
  router.push("/");
}

// Referencia a la reserva seleccionada con toda su información
const reservaSeleccionada = ref(0);
let modalInstance = null;

function abrirModal(reserva) {
  // Clonamos la reserva para evitar modificaciones directas
  reservaSeleccionada.value = { ...reserva }; 

  // Inicializamos el modal de modificación de los asistentes
  if (!modalInstance) {
    //Creamos la instancia del modal
    modalInstance = new Modal(document.getElementById("editarAsistentesModal"));
  }
  // Mostramos el modal
  modalInstance.show();
}
// Guardamos los cambios tras editar el número de asistentes
async function guardarCambios() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/reservas?id=" + reservaSeleccionada.value.reserva_id, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        num_personas: reservaSeleccionada.value.num_personas,
      }),
    });

    if (!response.ok) throw new Error("Error al actualizar la reserva");

    // Buscamos la reserva original en la lista y la actualizamos directamente
    const reservaIndex = listaRutas.value
    // Aseguramos que se accede a todas las reservas
      .flatMap(ruta => ruta.reservas) 
      .findIndex(r => r.reserva_id === reservaSeleccionada.value.reserva_id);

    // Si se encuentra la reserva, actualizamos el número de personas
    if (reservaIndex !== -1) {
      listaRutas.value
        .flatMap(ruta => ruta.reservas)[reservaIndex].num_personas = reservaSeleccionada.value.num_personas;
    }

    Swal.fire("Actualizado", "El número de asistentes ha sido modificado con éxito", "success");

    // Cerramos el modal
    modalInstance.hide(); 
  } catch (error) {
    Swal.fire("Error", error.message, "error");
  }
}

// Obtenemos las rutas asignadas al guía e inicializamos el mapa
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

// Cargamos las rutas
onMounted(obtenerRutas);

// Inicializamos las constantes para la paginación
const currentPage = ref(1);
const itemsPerPage = 2;
//Calculamos el total de páginas según el número de rutas y el número de rutas por página
const totalPages = computed(() => Math.ceil(listaRutas.value.length / itemsPerPage));

// Obtenemos las rutas paginadas
const paginatedRutas = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return listaRutas.value.slice(start, start + itemsPerPage);
});

// Cambiamos de página e inicializamos el mapa de nuevo en cada página
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
      .bindPopup(ruta.ruta_localidad)
      .openPopup();
  });
}
</script>

<template>
  <div v-if="listaRutas.length > 0 && idGuia">
    <h2 class="text-center mt-5 mb-5">Listado de rutas pendientes</h2>
    <p v-if="error" class="text-red-500">{{ error }}</p>

    <div class="row justify-content-around">
      <div v-for="ruta in paginatedRutas" :key="ruta.ruta_id" class="col-md-12 col-lg-6 col-xl-6 col-xxl-6 row">
        <div class="tarjeta bg-white shadow rounded p-4 row text-start">
          <h3 class="text-center mb-3 col-md-12">{{ ruta.ruta_titulo }}</h3>
          <img :src="ruta.ruta_foto" alt="Imagen de la ruta" class="img-fluid col-md-10 rounded">

          <main class="col-12 row mt-3">
            <p class="text-gray-700 font-semibold col-6 fs-5 border rounded text-center">📍 {{ ruta.ruta_localidad }}
            </p>
            <p class="text-gray-700 font-semibold col-6 fs-5 border rounded text-center ">📅 {{ ruta.ruta_fecha }}</p>
            <p class="text-gray-700 font-semibold col-6 fs-5 border rounded text-center ">⌚ {{ ruta.ruta_hora }}</p>
          </main>
          <div class="row col-12">
            <div :id="'map-' + ruta.ruta_id"
              class="map-container mb-5 shadow col-md-5 col-xl-5 col-sm-12 mt-4 text-center ms-2"></div>

            <div class="col-md-4 col-sm-12 col-md-12 border rounded">
              <p class="font-semibold mt-3 col-12 fs-5 text-center"><strong>🧑‍🤝‍🧑 Asistentes</strong></p>
              <table class="table col-12 table-hover">
                <thead>
                  <tr>
                    <th scope="col" id="nombre" class="fs-6 text-center">Nombre</th>
                    <th scope="col" id="asistentes" class="fs-6 text-center">Nº Asistentes</th>
                    <th scope="col" id="editar" class="fs-6">Editar</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="reserva in ruta.reservas" :key="reserva.reserva_id">
                    <td headers="nombre" class="text-center">{{ reserva.cliente.nombre }}</td>
                    <td headers="asistentes" class="text-center">
                      {{ reserva.num_personas }}
                    </td>
                    <td headers="editar">
                      <button @click="abrirModal(reserva)" class="btn btn-sm btn-warning ms-2 text-center">✏️</button>
                    </td>
                  </tr>
                </tbody>
              </table>

              <div class="modal fade" id="editarAsistentesModal" tabindex="-1" aria-labelledby="modalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modalLabel">Editar número de asistentes</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <label for="numAsistentes" class="form-label">Número de personas:</label>
                      <input type="number" v-model="reservaSeleccionada.num_personas" min="1" max="8" id="numAsistentes"
                        class="form-control" />
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <button @click="guardarCambios" class="btn btn-success">Guardar</button>
                    </div>
                  </div>
                </div>
              </div>

            </div>
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

main>p:hover {
  background-color:  rgb(187, 255, 187);;
}
th{
  background-color: rgb(96, 252, 96);
}td{
  background-color: rgb(216, 250, 216);
}
</style>
