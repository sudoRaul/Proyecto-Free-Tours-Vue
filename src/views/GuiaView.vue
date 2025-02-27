<script setup>
import { ref, onMounted } from "vue";
import NoData from "@/components/NoData.vue";
import router from "@/router";


const listaRutas = ref([]);
const error = ref(null);
const idGuia = JSON.parse(localStorage.getItem("sesion")).id;
const sesion = localStorage.getItem("sesion");
const rol = sesion ? JSON.parse(sesion).rol : null;

if(rol != "guia"){
  router.push("/")
}

async function obtenerRutas() {
    try {
        const response = await fetch(`http://localhost/APIFreetours/api.php/asignaciones?guia_id=${idGuia}`);
        if (!response.ok) throw new Error("Error al obtener las rutas");

        const data = await response.json();
                
        listaRutas.value = data;
    } catch (err) {
        error.value = err.message;
    }
}
onMounted(obtenerRutas);
</script>


<template>
    <div v-if="listaRutas.length > 0 && idGuia">
        <h2 class="text-center mt-5 mb-5">Listado de rutas pendientes</h2>
        <p v-if="error" class="text-red-500">{{ error }}</p>

        <!-- Usamos Bootstrap Grid para mostrar 2 tarjetas por fila -->
        <div class="row">
            <div v-for="ruta in listaRutas" :key="ruta.ruta_id" class="col-md-6 mb-4">
                <div class="tarjeta bg-white shadow rounded p-4">
                    <h3 class="text-center mb-3">{{ ruta.ruta_titulo }}</h3>
                    <img :src="ruta.ruta_foto" alt="Imagen de la ruta" class="ruta-img rounded img-fluid">

                    <main>
                        <p class="text-gray-700 font-semibold">📍 {{ ruta.ruta_descripcion }}</p>
                        <p class="text-gray-700 font-semibold">📅 {{ ruta.ruta_fecha }}</p>

                        <p class="font-semibold mt-3">🧑‍🤝‍🧑 Asistentes:</p>
                        <ul>
                            <li v-for="reserva in ruta.reservas" :key="reserva.reserva_id">
                                {{ reserva.cliente.nombre }}
                            </li>
                        </ul>
                    </main>
                </div>
            </div>
        </div>
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

.ruta-img {
    width: 100%;
    max-height: 200px;
    object-fit: cover;
}
</style>

