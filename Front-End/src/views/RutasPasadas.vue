<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import NoData from "@/components/NoData.vue";

const router = useRouter();

// Cogemos el rol para evitar que se pueda entrar en otras vistas
const sesion = localStorage.getItem("sesion");
const rol = sesion ? JSON.parse(sesion).rol : null;
const idCliente = sesion ? JSON.parse(sesion).id : null;

// Inicializamos la lista de rutas 
const listaRutasPasadas = ref([]);
const emailUsuario = sesion ? JSON.parse(sesion).email : null;
const error = ref("");

// Paginación
const currentPage = ref(1);
const itemsPerPage = 2;

const paginatedRutas = ref([]);

// Redireccionamos a home si no es cliente
if (rol !== "cliente") {
  router.push("/");
}

// Obtenemos las rutas pasadas del cliente
async function obtenerRutas() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/reservas?email=" + emailUsuario);
    if (!response.ok) throw new Error("Error al obtener las rutas");

    const data = await response.json();
    const hoy = new Date().toISOString().split("T")[0];

    // Filtramos para obtener las rutas pasadas
    listaRutasPasadas.value = data.filter(ruta => ruta.ruta_fecha < hoy).map(ruta => ({
      ...ruta,
      yaValorada: false,
      valoracion: 1,
      comentarioTemp: ""
    }));

    
    await obtenerValoraciones();
    actualizarPaginacion();
  } catch (err) {
    error.value = err.message;
  }
}

//Obtenemos las valoraciones de las rutas
async function obtenerValoraciones() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/valoraciones?user_id=" + idCliente);
    if (!response.ok) throw new Error("Error al obtener las valoraciones");

    const valoraciones = await response.json();
    listaRutasPasadas.value.forEach(ruta => {
      const valoracion = valoraciones.find(v => v.ruta_id === ruta.ruta_id);
      //Si esa ruta está valorada, la marcamos como ya valorada y desactivaremos los botones de valoración
      if (valoracion) {
        ruta.yaValorada = true;
        ruta.valoracion = valoracion.valoracion_id;
        ruta.comentarioTemp = valoracion.comentario;
      }
    });
  } catch (err) {
    error.value = err.message;
  }
}

// Actualizamos la paginación
function actualizarPaginacion() {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  paginatedRutas.value = listaRutasPasadas.value.slice(start, end);
}

// Cambiamos de página
function nextPage() {
  if (currentPage.value * itemsPerPage < listaRutasPasadas.value.length) {
    currentPage.value++;
    actualizarPaginacion();
  }
}
 
// Cambiamos de página
function prevPage() {
  if (currentPage.value > 1) {
    currentPage.value--;
    actualizarPaginacion();
  }
}


// Guardamos la valoración de la ruta
async function guardarValoracion(ruta) {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/valoraciones", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        user_id: ruta.cliente_id,
        ruta_id: ruta.ruta_id,
        estrellas: ruta.valoracion,
        comentario: ruta.comentarioTemp,
      }),
    });

    if (!response.ok) throw new Error("Error al guardar la valoración");

    const data = await response.json();
    if (data.status == "error") {
      Swal.fire("Error", data.message, "error");
      return;
    }

    //Marcamos como ya valorada esa ruta para desativar los botones de valoración
    ruta.comentario = ruta.comentarioTemp;
    ruta.yaValorada = true;

    Swal.fire("Valoración guardada", "Tu valoración ha sido guardada con éxito", "success");
  } catch (err) {
    Swal.fire("Error", err.message, "error");
  }
}

// Cargamos las rutas
onMounted(() => {
  obtenerRutas();
});
</script>

<template>
  <div v-if="listaRutasPasadas.length > 0" class="ms-5">
    <h2 class="text-center mt-4">Rutas Pasadas</h2>
    <hr>
    <div class="tarjetas">
      <div v-for="ruta in paginatedRutas" :key="ruta.ruta_id" class="tarjeta">
        <h3>{{ ruta.ruta_titulo }}</h3>
        <p class="fs-5">📅 {{ ruta.ruta_fecha }} - 🏙️ {{ ruta.ruta_localidad }}</p>
        <h3 class="mb-4">¡Cuenténos cómo fue su experiencia!</h3>
        <div class="row">
          <button @click="ruta.valoracion = Math.max(ruta.valoracion - 1, 1)" class="col-1 valoracion"
            :disabled="ruta.yaValorada || ruta.valoracion === 1">-</button>
          <span class="col-2 text-center fs-3">
            <span v-for="n in Math.max(ruta.valoracion, 1)" :key="n" class="star">⭐</span>
          </span>
          <button @click="ruta.valoracion = Math.min(ruta.valoracion + 1, 5)" class="col-1 valoracion"
            :disabled="ruta.yaValorada">+</button>
        </div>
        <div class="row mt-4">
          <textarea v-model="ruta.comentarioTemp" placeholder="Añade un comentario" class="col-3"
            :disabled="ruta.yaValorada"></textarea>
          <button @click="guardarValoracion(ruta)" class="col-3 valoracion">Guardar</button>
        </div>
      </div>
    </div>
    <div class="d-flex justify-content-center mt-5 mb-4">
      <button class="btn btn-primary me-2" @click="prevPage" :disabled="currentPage === 1">Anterior</button>
      <span>Página {{ currentPage }} de {{ Math.ceil(listaRutasPasadas.length / itemsPerPage) }}</span>
      <button class="btn btn-primary ms-2" @click="nextPage"
        :disabled="currentPage * itemsPerPage >= listaRutasPasadas.length">Siguiente</button>
    </div>
  </div>
  <NoData v-else mensaje="No tienes rutas reservadas" submensaje="Reserve alguna ruta para verlas en esta sección" />
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

.tarjeta:hover {
  transform: scale(1.02);
}
</style>
