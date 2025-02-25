<script setup>
import { ref, onMounted } from "vue";
import NoData from "@/components/NoData.vue";
import router from "@/router";

const sesion = localStorage.getItem("sesion");
const id = sesion ? JSON.parse(sesion).id : null;

if (!id) {
    router.push("/");
}

const listaValoraciones = ref([]);

async function obtenerValoraciones() {
    try {
        const response = await fetch(`http://localhost/APIFreetours/api.php/valoraciones?user_id=${id}`);
        if (!response.ok) throw new Error("Error al obtener las valoraciones");

        const data = await response.json();
        listaValoraciones.value = data;
    } catch (err) {
        console.log(err);
    }
}

onMounted(obtenerValoraciones);
</script>
<template>
    <div v-if="listaValoraciones.length > 0">
        <h2 class="text-center mt-5 mb-5">Listado de valoraciones realizadas</h2>

        <div class="tarjetas justify-content-between col-xs-12 col-md-12 col-lg-12">
            <div v-for="ruta in listaValoraciones" :key="ruta.valoracion_id" class="bg-white shadow rounded row pt-4 pb-5 col-xs-12 mb-4">
                <h1 class="ms-5">{{ ruta.ruta_titulo }}</h1>
                <main class="col-7 ms-4 mt-3">
                    <textarea name="coment" id="coment" disabled>{{ ruta.comentario }}</textarea>
                    <p v-for="stars in ruta.puntuacion"> ⭐ </p>
                </main>
            </div>
        </div>
    </div>
    <NoData v-else mensaje="No se encontraron valoraciones" submensaje="Realice una ruta para poder hacer valoraciones" />

</template>