<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import NoData from "@/components/NoData.vue";
import router from "@/router";

const sesion = localStorage.getItem("sesion");
const cliente_id = sesion ? JSON.parse(sesion).id : null;

// Almacenamos la lista de rutas
const listaRutas = ref([]);
const error = ref(null);

// Datos para duplicar ruta
const fechaSeleccionada = ref("");
const guiasDisponibles = ref([]);
const horasDisponibles = ref([]);
const guiaSeleccionado = ref("");
const horaSeleccionada = ref("");
const rutaAduplicar = ref(null);

// Función para obtener las rutas
async function obtenerRutas() {
    try {
        const response = await fetch("http://localhost/APIFreetours/api.php/rutas");
        if (!response.ok) throw new Error("Error al obtener las rutas");
        
        const data = await response.json();
        listaRutas.value = data;
    } catch (err) {
        error.value = err.message;
    }
}

// Función para eliminar una ruta
async function eliminarRuta(id) {
    const result = await Swal.fire({
        title: "¿Estás seguro?",
        text: "No podrás revertir este proceso.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6"
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch(`http://localhost/APIFreetours/api.php/rutas?id=${id}`, {
            method: "DELETE",
        });

        if (!response.ok) throw new Error("Error al eliminar la ruta");
        
        obtenerRutas();
        Swal.fire("¡Eliminado!", "La ruta ha sido eliminada correctamente.", "success");
    } catch (err) {
        error.value = err.message;
        Swal.fire("Error", "No se pudo eliminar la ruta.", "error");
    }
}

// Función para abrir el modal de duplicar ruta
function abrirModalDuplicar(ruta) {
    rutaAduplicar.value = ruta;
    fechaSeleccionada.value = "";
    guiasDisponibles.value = [];
    horasDisponibles.value = [];
    guiaSeleccionado.value = "";
    horaSeleccionada.value = "";
    new bootstrap.Modal(document.getElementById("modalDuplicarRuta")).show();
}

// Obtener guías y horarios cuando se seleccione la fecha
async function obtenerGuiasYhorarios() {
    if (!fechaSeleccionada.value) {
        Swal.fire("Error", "Debes seleccionar una fecha", "error");
        return;
    }

    try {
        const response = await fetch(`http://localhost/APIFreetours/api.php/guias?fecha=${fechaSeleccionada.value}`);
        if (!response.ok) throw new Error("Error al obtener los guías");

        const data = await response.json();
        guiasDisponibles.value = data.guias;
        horasDisponibles.value = data.horas;
    } catch (err) {
        error.value = err.message;
        Swal.fire("Error", "No se pudieron cargar los guías y horarios", "error");
    }
}

// Función para duplicar la ruta
async function duplicarRuta() {
    if (!fechaSeleccionada.value || !guiaSeleccionado.value || !horaSeleccionada.value) {
        Swal.fire("Error", "Todos los campos son obligatorios", "error");
        return;
    }

    const nuevaRuta = {
        nombre: rutaAduplicar.value.nombre,
        fecha: fechaSeleccionada.value,
        guia_id: guiaSeleccionado.value,
        hora: horaSeleccionada.value,
        foto: rutaAduplicar.value.foto
    };

    try {
        const response = await fetch("http://localhost/APIFreetours/api.php/rutas", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(nuevaRuta)
        });

        if (!response.ok) throw new Error("Error al duplicar la ruta");

        Swal.fire("¡Éxito!", "Ruta duplicada correctamente", "success");
        obtenerRutas();
        bootstrap.Modal.getInstance(document.getElementById("modalDuplicarRuta")).hide();
    } catch (err) {
        error.value = err.message;
        Swal.fire("Error", "No se pudo duplicar la ruta.", "error");
    }
}

// Cargar rutas al iniciar
onMounted(obtenerRutas);
</script>

<template>
    <div v-if="listaRutas.length > 0" class="container col-xs-12 mb-4">
        <h2 class="text-center mt-4 mb-4">Listado de Rutas</h2>
        <p v-if="error" class="text-red-500">{{ error }}</p>

        <div class="row">
            <div v-for="ruta in listaRutas" :key="ruta.id" class="col-md-6 mb-4">
                <div class="tarjeta bg-white shadow rounded p-4">
                    <img :src="ruta.foto" title="Imagen de la ruta" alt="Imagen de la ruta" class="ruta-img rounded img-fluid">
                    <main>
                        <p class="text-gray-700 font-semibold">📅 {{ ruta.fecha }}</p>
                        <p class="text-gray-500">👤 Guía: {{ ruta.guia_nombre }}</p>
                        <button class="btn btn-primary m-2" @click="abrirModalDuplicar(ruta)">🔁 Duplicar</button>
                        <button class="btn btn-danger m-2" @click="eliminarRuta(ruta.id)">❌ Eliminar</button>
                    </main>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bootstrap -->
    <div class="modal fade" id="modalDuplicarRuta" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Duplicar Ruta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label>Selecciona Fecha:</label>
                    <input type="date" v-model="fechaSeleccionada" class="form-control mb-2" @change="obtenerGuiasYhorarios">
                    
                    <label>Selecciona Guía:</label>
                    <select v-model="guiaSeleccionado" class="form-control mb-2">
                        <option v-for="guia in guiasDisponibles" :key="guia.id" :value="guia.id">{{ guia.nombre }}</option>
                    </select>

                    <label>Selecciona Hora:</label>
                    <select v-model="horaSeleccionada" class="form-control">
                        <option v-for="hora in horasDisponibles" :key="hora" :value="hora">{{ hora }}</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success" @click="duplicarRuta">Duplicar Ruta</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.tarjeta {
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
