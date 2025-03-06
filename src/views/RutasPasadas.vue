<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import NoData from "@/components/NoData.vue";

const router = useRouter();
const sesion = localStorage.getItem("sesion");
const rol = sesion ? JSON.parse(sesion).rol : null;
const idCliente = sesion ? JSON.parse(sesion).id : null;

const listaRutasPasadas = ref([]);

const emailUsuario = sesion ? JSON.parse(sesion).email: null;
const error = ref("");


if (rol !== "cliente") {
  router.push("/");
}

async function obtenerRutas() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/reservas?email=" + emailUsuario);
    if (!response.ok) throw new Error("Error al obtener las rutas");

    const data = await response.json();
    const hoy = new Date().toISOString().split("T")[0];

    // Filtrar rutas pasadas
    listaRutasPasadas.value = data.filter(ruta => ruta.ruta_fecha < hoy).map(ruta => ({
      ...ruta,
      yaValorada: false, // Se actualizará con las valoraciones reales
      valoracion: 1, // Se actualizará luego
      comentarioTemp: "" // Se actualizará luego
    }));

    // Llamar a la API de valoraciones
    await obtenerValoraciones();
  } catch (err) {
    error.value = err.message;
  }
}

async function obtenerValoraciones() {
  try {
    const response = await fetch("http://localhost/APIFreetours/api.php/valoraciones?user_id=" + idCliente);
    if (!response.ok) throw new Error("Error al obtener las valoraciones");

    const valoraciones = await response.json();

    // Asignar valoraciones a las rutas correspondientes
    listaRutasPasadas.value.forEach(ruta => {
      const valoracion = valoraciones.find(v => v.ruta_id === ruta.ruta_id);
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

onMounted(obtenerRutas);

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
        comentario: ruta.comentarioTemp, // Se envía el comentario actualizado
      }),
    });

    if (!response.ok) throw new Error("Error al guardar la valoración");

    const data = await response.json();
    if (data.status == "error") {
      Swal.fire("Error", data.message, "error");
      return;
    }

    // Una vez guardado, deshabilitar textarea
    ruta.comentario = ruta.comentarioTemp;
    ruta.yaValorada = true;

    Swal.fire("Valoración guardada", "Tu valoración ha sido guardada con éxito", "success");
  } catch (err) {
    Swal.fire("Error", err.message, "error");
  }
}

onMounted(obtenerRutas)



</script>
<template>
    <div v-if="listaRutasPasadas.length > 0" class="ms-5">
        <h2 class="text-center mt-4" v-if="listaRutasPasadas.length">Rutas Pasadas</h2>
        <hr>
        <div class="tarjetas">
            <div v-for="ruta in listaRutasPasadas" :key="ruta.ruta_id" class="tarjeta">
                <h3>{{ ruta.ruta_titulo }}</h3>
                <p class="fs-5">📅 {{ ruta.ruta_fecha }} - 🏙️ {{ ruta.ruta_localidad }}</p>
                <h3 class="mb-4">¡Cuenténos cómo fue su experiencia!</h3>
                <div class="row">
                    <button @click="ruta.valoracion = Math.max(ruta.valoracion - 1, 1)" class="col-1 valoracion"
                        :disabled="ruta.yaValorada || ruta.valoracion === 1">-</button>
                    <span class="col-2 text-center fs-3">
                        <span v-for="n in Math.max(ruta.valoracion, 1)" :key="n" class="star">⭐</span>
                    </span>
                    <button @click="ruta.valoracion = Math.min(ruta.valoracion +1, 5)" class="col-1 valoracion"
                        :disabled="ruta.yaValorada">+</button>
                </div>
                <div class="row mt-4">
                    <textarea v-model="ruta.comentarioTemp" placeholder="Añade un comentario" class="col-3"
                        :disabled="ruta.yaValorada"></textarea>
                    <button @click="guardarValoracion(ruta)" class="col-3 valoracion">Guardar</button>
                </div>
            </div>
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
}</style>