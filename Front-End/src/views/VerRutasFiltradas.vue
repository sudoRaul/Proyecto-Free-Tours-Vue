<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import Swal from "sweetalert2";
import NoData from "@/components/NoData.vue";

const route = useRoute();
//OBtenemos los parámetro necesarios para filtrar las rutas
const localidad = ref(route.params.localidad || "");
const fecha = ref(route.params.fecha);
//Inicializamos la lista de rutas
const listaRutas = ref([]);
// Página actual
const currentPage = ref(1);
// Rutas por página
const itemsPerPage = 2;

// Obtenemos las rutas filtradas
async function obtenerRutasFiltradas() {
    try {
        let url = `http://localhost/APIFreetours/api.php/rutas?fecha=${fecha.value}`;
        if (localidad.value) {
            url += `&localidad=${localidad.value}`;
        }

        const response = await fetch(url);
        if (!response.ok) throw new Error("Error al obtener las rutas");

        const data = await response.json();
        listaRutas.value = data;
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.message
        });
    }
}

// Calculaamos el total de páginas según el número de rutas y el número de rutas por página
const totalPages = computed(() => Math.ceil(listaRutas.value.length / itemsPerPage));
const paginatedRutas = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return listaRutas.value.slice(start, start + itemsPerPage);
});

// Cambiamos de página
function changePage(page) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

// Cargamos las rutas
onMounted(obtenerRutasFiltradas);
</script>

<template>
    <div v-if="listaRutas.length > 0" class="container col-xs-12 mb-4">
        <h1 class="text-center my-4">Rutas {{ localidad ? `en ${localidad}` : "" }} para la fecha {{ fecha }}</h1>
        
        <div class="row">
            <router-link v-for="ruta in paginatedRutas" :key="ruta.id" :to="`/info-completa-ruta/${ruta.id}`"
                class="text-decoration-none col-md-6 mb-4 text-dark">
                <div class="tarjeta bg-white shadow rounded p-4">
                    <h2 class="text-center">{{ ruta.titulo }}</h2>
                    <img :src="ruta.foto" title="Imagen de la ruta" alt="Imagen de la ruta" class="ruta-img rounded img-fluid">
                    <div class="row">
                        <strong class="text-gray-700 font-semibold col-6 fs-5  mt-4 mb-3">📅 {{ ruta.fecha }}</strong>
                        <strong class="text-gray-700 font-semibold col-6 fs-5 rounded mt-4">⌚ {{ ruta.hora }}</strong>
                        <hr>
                        <strong class="text-gray-500 col-3 fs-5 rounded text-cent mt-1 mb-3">📍 {{ ruta.localidad }}</strong>
                        <button class="btn btn-info mt-2 w-100 fs-4"><strong>Ver Detalles</strong></button>
                    </div>
                </div>
            </router-link>
        </div>

        
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <button class="page-link" @click="changePage(currentPage - 1)">Anterior</button>
                </li>
                <li class="page-item" v-for="page in totalPages" :key="page" :class="{ active: page === currentPage }">
                    <button class="page-link" @click="changePage(page)">{{ page }}</button>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <button class="page-link" @click="changePage(currentPage + 1)">Siguiente</button>
                </li>
            </ul>
        </nav>
    </div>
    
    <NoData v-else mensaje="No se encontraron rutas" submensaje="Pruebe en otra fecha o vuelva en otro momento." />
</template>

<style scoped>
.tarjeta {
    min-height: 250px;
    padding: 20px;
    transition: transform 0.2s ease-in-out;
    cursor: pointer;
}

.tarjeta:hover {
    transform: scale(1.02);
}

.ruta-img {
    width: 100%;
    max-height: 200px;
    object-fit: cover;
}
</style>
