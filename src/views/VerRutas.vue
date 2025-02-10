<script setup>
import { ref, onMounted } from "vue";

// Estado para almacenar la lista de rutas
const listaRutas = ref([]);
const error = ref(null);

// Función para obtener las rutas desde la API
const obtenerRutas = async () => {
    try {
        const response = await fetch("http://localhost/APIFreetours/api.php/rutas");
        if (!response.ok) throw new Error("Error al obtener las rutas");
        
        const data = await response.json();
        listaRutas.value = data; // Guardamos las rutas en la variable reactiva
    } catch (err) {
        error.value = err.message;
    }
};

// Función para eliminar una ruta
async function eliminarRuta(id) {
    if (!confirm("¿Estás seguro de que quieres eliminar esta ruta?")) return;

    try {
        const response = await fetch(`http://localhost/APIFreetours/api.php/rutas?id=${id}`, {
            method: "DELETE",
        });

        if (!response.ok) throw new Error("Error al eliminar la ruta");

        // Tras eliminar mostramos las rutas
        listaRutas.value = listaRutas.value.filter(ruta => ruta.id !== id);
    } catch (err) {
        error.value = err.message;
    }
};

// Cargar rutas al montar el componente
onMounted(obtenerRutas);
</script>

<template>
    <div class="container">
        <h2 class="text-center">Listado de Rutas</h2>
        
        <p v-if="error" class="text-red-500">{{ error }}</p>

        <div class="tarjetas justify-content-between">
            <div v-for="ruta in listaRutas" :key="ruta.id" class="bg-white shadow rounded row pt-4 pb-5">
                <img :src="ruta.foto" alt="Imagen de la ruta" class="rounded col-5">

                <div class="col 7">
                    <div class="col-12">
                        <p class="text-gray-700 font-semibold col-5">📅 {{ ruta.fecha }}</p>
                        <p class="text-gray-500 col-4">👤 Guía: {{ ruta.guia_nombre }}</p>
                    </div>
                    <button @click="eliminarRuta(ruta.id)" class="btn-delete col-7 mt-1 p-2">❌ Eliminar</button>

                </div>

               
            </div>
        </div>
    </div>
</template>

<style scoped>
.tarjetas {
    width: 50%;
    margin: auto;
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
