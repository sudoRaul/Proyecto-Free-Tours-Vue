<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import NoData from "@/components/NoData.vue";

const router = useRouter();
const sesion = localStorage.getItem("sesion");
const rol = sesion ? JSON.parse(sesion).rol : null;

if (rol !== "cliente") {
  router.push("/");
}

const listaRutasFuturas = ref([]);
const listaRutasPasadas = ref([]);
const error = ref("");
const emailUsuario = JSON.parse(localStorage.getItem("sesion")).email;
const isLogued = rol ? true : false;


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
    listaRutasPasadas.value = data.filter(ruta => ruta.ruta_fecha < hoy);
  } catch (err) {
    error.value = err.message;
  }
}
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
        comentario: ruta.comentario || "",
      }),
    });
    const data = await response.json();

    if (!response.ok) throw new Error("Error al guardar la valoración");

    if (data.status == "error") {
      Swal.fire("Error", data.message, "error");
      ruta.comentario = "";
      return;
    }

    Swal.fire("Valoración guardada", "Tu valoración ha sido guardada con éxito", "success");
  } catch (err) {
    Swal.fire("Error", err.message, "error");
  }
}

onMounted(obtenerRutas);
</script>

<template>
  <div v-if="listaRutasFuturas.length > 0 || listaRutasPasadas.length > 0" class="container">
    <h1 class="text-center mb-4 mt-4">Mis Reservas</h1>
    <p v-if="error" class="text-red-500">{{ error }}</p>

    <h2 class="text-center mt-4">Rutas Futuras</h2>
    <div class="tarjetas" v-if="listaRutasFuturas.length">
      <div v-for="ruta in listaRutasFuturas" :key="ruta.ruta_id" class="tarjeta" @click="verDetallesRuta(ruta.ruta_id)">
        <h2 class="text-center">{{ ruta.ruta_titulo }}</h2>
        <img :src="ruta.ruta_foto" alt="Imagen de la ruta" class="rounded">
        <p>📅 {{ ruta.ruta_fecha }} - 🏙️ {{ ruta.ruta_localidad }} - ⌚ {{ ruta.ruta_hora }}</p>
      </div>
    </div>

    <h2 class="text-center mt-4">Rutas Pasadas</h2>
    <div class="tarjetas" v-if="listaRutasPasadas.length">
      <div v-for="ruta in listaRutasPasadas" :key="ruta.ruta_id" class="tarjeta">
        

        <div class="valoracion"></div>
        <h3 class="mb-4">¡Cuenténos cómo fue su experiencia!</h3>
        <h3>{{ ruta.ruta_titulo }}</h3>
        <p class="fs-5">📅 {{ ruta.ruta_fecha }} - 🏙️ {{ ruta.ruta_localidad }}</p>
        <div class="row">
          <button @click="ruta.valoracion = Math.max(ruta.valoracion - 1, 1)" class="col-1">-</button>
          <span class="col-2 text-center fs-3">
            <span v-for="n in ruta.valoracion" :key="n" class="star">⭐</span>
          </span>
          <button @click="ruta.valoracion = Math.min(ruta.valoracion + 1, 5)" class="col-1">+</button>
        </div>
        <div class="row mt-4">
          <textarea v-model="ruta.comentario" placeholder="Añade un comentario" class="col-3"></textarea>
          <button @click="guardarValoracion(ruta)" class="col-3">Guardar</button>
        </div>



      </div>
    </div>
  </div>

  <NoData v-else mensaje="No tienes rutas pasadas para valorar" />

</template>

<style scoped>
.tarjeta {
  background: white;
  padding: 15px;
  margin-bottom: 10px;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.valoracion {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 10px;
}

button {
  margin: 5px;
  padding: 5px 10px;
  background: #007bff;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}

button:hover {
  background: #0056b3;
}
</style>
