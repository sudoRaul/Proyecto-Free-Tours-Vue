<script setup>
import { ref, onMounted, nextTick, defineComponent } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import NoData from "@/components/NoData.vue";

const router = useRouter(); // Necesario para la navegación


const sesion = localStorage.getItem("sesion");
const rol = sesion ? JSON.parse(sesion).rol : null;

if(rol != "cliente"){
  router.push("/")
}

const listaRutas = ref([]);
const error = ref("");
const emailUsuario = JSON.parse(localStorage.getItem("sesion")).email;

//Usamos este isLogued para en la vista de mostrar toda la Info
//saber si ha reservado ya o no
const isLogued = rol ? true : false


// Función para redirigir a la página de detalles de la ruta
function verDetallesRuta(id) {
  router.push(`/info-completa-ruta/${id}/${isLogued}`);
}

// Función para obtener las rutas desde la API
async function obtenerRutas() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/reservas?email=" + emailUsuario);
    if (!response.ok) throw new Error("Error al obtener las rutas");

    const data = await response.json();
    listaRutas.value = data;

  } catch (err) {
    error.value = err.message;
  }
}

// Función para eliminar una ruta
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

      Swal.fire({
        icon: "success",
        title: "Ruta eliminada",
        text: "La ruta ha sido eliminada correctamente.",
        timer: 2000,
        showConfirmButton: false,
      });

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

onMounted(obtenerRutas);
</script>

<template>
  <div v-if="listaRutas.length > 0 && emailUsuario" class="container">
    <h1 class="text-center mb-4 mt-4">Mis Reservas</h1>

    <p v-if="error" class="text-red-500">{{ error }}</p>

    <div class="tarjetas justify-content-between col-xs-12 col-md-12 col-lg-12">
      <div v-for="ruta in listaRutas" :key="ruta.ruta_id"
        class="bg-white shadow rounded row pt-4 pb-5 col-xs-12 mb-4 tarjeta" @click="verDetallesRuta(ruta.ruta_id)">
        <h2 class="text-center mb-4">{{ ruta.ruta_titulo }}</h2>

        <img :src="ruta.foto" alt="Imagen de la ruta" title="Imagen de la ruta"
          class="rounded col-xl-8 col-lg-10 col-md-12">

        <div class="col 4">
          <div class="col-12 row">
            <p class="text-gray-700 font-semibold col-5">📅 {{ ruta.ruta_fecha }}</p>
            <p class="text-gray-700 font-semibold col-5">🏙️ {{ ruta.ruta_localidad }}</p>
            <p class="text-gray-700 font-semibold col-5">⌚ {{ ruta.ruta_hora }}</p>
          </div>
          <button aria-label="Eliminar reserva" @click="eliminarRuta(ruta.ruta_id)" class="btn-delete col-7 mt-1 p-2">
            ❌ Eliminar reserva
          </button>
        </div>
      </div>
    </div>
  </div>
  <NoData v-else mensaje="No se encontraron rutas"
    submensaje="Reserve alguna ruta para previsualizarlas en este apartado." />
</template>

<style scoped>
.tarjeta {
  cursor: pointer;
  transition: transform 0.2s ease-in-out;
}

.tarjeta:hover {
  transform: scale(1.03);
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
