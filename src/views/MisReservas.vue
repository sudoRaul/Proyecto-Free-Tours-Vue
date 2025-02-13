<script setup>
    import { ref } from "vue";

    // Estado para almacenar la lista de rutas
    const listaRutas = ref([]);
    const error = ref(null);
    const emailUsuario = JSON.parse(localStorage.getItem("sesion")).email;
    //alert(emailUsuario)

    // Función para obtener las rutas desde la API
    async function obtenerRutas() {
        try {
            const response = await fetch("http://localhost/APIFreetours/api.php/reservas?email="+emailUsuario);
            if (!response.ok) throw new Error("Error al obtener las rutas");
            
            const data = await response.json();
            listaRutas.value = data; // Guardamos las rutas en la variable reactiva
        } catch (err) {
            error.value = err.message;
        }
    };
    obtenerRutas();
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