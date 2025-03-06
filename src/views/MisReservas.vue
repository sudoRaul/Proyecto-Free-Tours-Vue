<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import NoData from "@/components/NoData.vue";
import bootstrap from "bootstrap/dist/js/bootstrap.bundle.min.js"; 

const router = useRouter();
const sesion = localStorage.getItem("sesion");
const rol = sesion ? JSON.parse(sesion).rol : null;

if (rol !== "cliente") {
  router.push("/");
}

const listaRutasFuturas = ref([]);
const error = ref("");
const emailUsuario = sesion ? JSON.parse(sesion).email: null;
const isLogued = !!rol;

// Variables para la actualización de asistentes
const reservaSeleccionada = ref(null);
const numPersonas = ref(1);

function verDetallesRuta(id) {
  router.push(`/info-completa-ruta/${id}/${isLogued}`);
}

async function obtenerRutas() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/reservas?email=" + emailUsuario);
    if (!response.ok) throw new Error("Error al obtener las rutas");

    const data = await response.json();
    const hoy = new Date().toISOString().split("T")[0];

    listaRutasFuturas.value = data.filter(ruta => ruta.ruta_fecha >= hoy);
  } catch (err) {
    error.value = err.message;
  }
}

async function cancelarReserva(id) {
  const confirmacion = await Swal.fire({
    title: "¿Estás seguro?",
    text: "Esta acción cancelará tu reserva y no podrás recuperarla.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, cancelar",
    cancelButtonText: "No, mantener"
  });

  if (confirmacion.isConfirmed) {
    try {
      const response = await fetch(`http://localhost/APIFreetours/api.php/reservas?id=${id}`, {
        method: "DELETE"
      });

      if (!response.ok) throw new Error("Error al cancelar la reserva");

      listaRutasFuturas.value = listaRutasFuturas.value.filter(ruta => ruta.reserva_id !== id);

      Swal.fire("Cancelada", "Tu reserva ha sido cancelada con éxito.", "success");
    } catch (err) {
      Swal.fire("Error", err.message, "error");
    }
  }
}

// Función para abrir el modal de actualización de asistentes
function abrirModalActualizarAsistentes(ruta) {
  reservaSeleccionada.value = ruta;
  numPersonas.value = ruta.num_personas; // Cargar el número actual de asistentes

  const modal = new bootstrap.Modal(document.getElementById("modalActualizarAsistentes"));
  modal.show();
}

// Función para actualizar el número de asistentes
async function actualizarAsistentes() {
  if (!reservaSeleccionada.value) return;

  if (numPersonas.value < 1 || numPersonas.value > 8) {
    Swal.fire("Atención", "El número de asistentes debe estar entre 1 y 8", "warning");
    return;
  }

  try {
    const response = await fetch(`http://localhost/APIFreetours/api.php/reservas?id=${reservaSeleccionada.value.reserva_id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ num_personas: numPersonas.value })
    });

    if (!response.ok) throw new Error("Error al actualizar la reserva");

    const index = listaRutasFuturas.value.findIndex(ruta => ruta.reserva_id === reservaSeleccionada.value.reserva_id);
    if (index !== -1) {
      listaRutasFuturas.value[index].num_personas = numPersonas.value;
    }

    Swal.fire("Actualizado", "El número de asistentes ha sido actualizado.", "success");
  } catch (err) {
    Swal.fire("Error", err.message, "error");
  }
}


onMounted(obtenerRutas);

</script>

<template>
  <div v-if="listaRutasFuturas.length > 0" class="container col-xs-12 mb-4">
    <h1 class="text-center mb-4 mt-4">Mis Reservas</h1>
    <p v-if="error" class="text-danger">{{ error }}</p>

    <div class="tarjetas row" v-if="listaRutasFuturas.length">
      <h2 class="text-center mt-4">Rutas Futuras</h2>
      <hr>
      <div v-for="ruta in listaRutasFuturas" :key="ruta.ruta_id" class="col-md-6 mb-4">
        <div class="tarjeta bg-white shadow rounded p-4">
          <h2 class="text-center">{{ ruta.ruta_titulo }}</h2>
          <img :src="ruta.ruta_foto" alt="Imagen de la ruta" class="rounded ruta-img rounded img-fluid">
          <main class="row">
            <p class="text-gray-700 font-semibold fs-5">📅 {{ ruta.ruta_fecha }}</p>
            <p class="text-gray-500 fs-5">🏙️ Localidad: {{ ruta.ruta_localidad }}</p>
            <p class="text-gray-500 fs-5">⌚ Hora: {{ ruta.ruta_hora }}</p>
            <p class="text-gray-500 fs-5">👥 Asistentes: {{ ruta.num_personas }}</p>
          </main>
          <button @click="verDetallesRuta(ruta.ruta_id)" class="btn btn-info mt-2 w-100">Ver Detalles</button>
          <button @click="abrirModalActualizarAsistentes(ruta)" class="btn btn-warning mt-2 w-100">Modificar
            Asistentes</button>
          <button @click="cancelarReserva(ruta.reserva_id)" class="btn btn-danger mt-2 w-100">Cancelar Reserva</button>
        </div>
      </div>
    </div>

  </div>
  <NoData v-else mensaje="No tienes rutas reservadas" submensaje="Reserve alguna ruta para verlas en esta sección" />


  <!-- Modal para actualizar asistentes -->
  <div class="modal fade" id="modalActualizarAsistentes" tabindex="-1" aria-labelledby="modalActualizarAsistentesLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalActualizarAsistentesLabel">Modificar Asistentes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for="numPersonas" class="form-label">Número de asistentes (máx. 8):</label>
          <input type="number" id="numPersonas" v-model="numPersonas" class="form-control" min="1" max="8"
            placeholder="Ingrese el número de asistentes">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" @click="actualizarAsistentes"
            data-bs-dismiss="modal">Actualizar</button>
        </div>
      </div>
    </div>
  </div>

</template>


<style scoped>
.tarjeta {
  background: white;
  padding: 15px;
  margin-bottom: 10px;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease-in-out;
  cursor: pointer;
}

.valoracion {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 10px;
  margin: 5px;
  padding: 5px 10px;
  background: #007bff;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}


.valoracion:hover {
  background: #0056b3;
}

.ruta-img {
  width: 100%;
  max-height: 200px;
  object-fit: cover;
}

.tarjeta:hover {
  transform: scale(1.02);
}

.hover:hover {
  color: blue
}
</style>
