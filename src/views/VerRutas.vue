<script setup>
import { ref, onMounted, computed } from "vue";
import Swal from "sweetalert2";
import NoData from "@/components/NoData.vue";
import router from "@/router";

const sesion = localStorage.getItem("sesion");
const rol = sesion ? JSON.parse(sesion).rol : null;

// Redirección si no es guía
if (rol !== "admin") {
  router.push("/");
}
// Almacenamos la lista de rutas
const listaRutas = ref([]);
const error = ref(null);

const fechaHoy = new Date().toISOString().split("T")[0];


const currentPage = ref(1); // Página actual
const itemsPerPage = 4; // Número de rutas por página
const totalPages = computed(() => Math.ceil(listaRutas.value.length / itemsPerPage));


// Obtener las rutas para la página actual
const paginatedRutas = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return listaRutas.value.slice(start, start + itemsPerPage);
});

// Cambiar de página
function cambiarPagina(pagina) {
    if (pagina >= 1 && pagina <= totalPages.value) {
        currentPage.value = pagina;
    }
}

// Datos para duplicar ruta
const fechaSeleccionada = ref("");
const guiasDisponibles = ref([]);
const horasDisponibles = ref([
    "10:00", "11:00", "12:00", "13:00", "14:00", "15:00",
    "16:00", "17:00", "18:00", "19:00", "20:00"
]);
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
    guiaSeleccionado.value = "";
    horaSeleccionada.value = "";
    new bootstrap.Modal(document.getElementById("modalDuplicarRuta")).show();
}

// Obtener guías disponibles cuando se seleccione la fecha
async function filtrarGuias() {
    if (!fechaSeleccionada.value) return;
    try {
        const response = await fetch(`http://localhost/APIFreetours/api.php/asignaciones?fecha=${fechaSeleccionada.value}`);
        const data = await response.json();
        guiasDisponibles.value = data;
    } catch (err) {
        error.value = err.message;
    }
}

function comprobarGuias() {
    if (!fechaSeleccionada.value) {
        Swal.fire({
            icon: "warning",
            title: "Atención",
            text: "Por favor, seleccione una fecha antes de buscar."
        });
        return;
    }
}

// Función para duplicar la ruta
async function duplicarRuta() {
    if (!rutaAduplicar.value || !fechaSeleccionada.value || !guiaSeleccionado.value || !horaSeleccionada.value) {
        Swal.fire("Error", "Debe completar todos los campos antes de duplicar.", "error");
        return;
    }

    const nuevaRuta = {
        titulo: rutaAduplicar.value.titulo,
        localidad: rutaAduplicar.value.localidad,
        descripcion: rutaAduplicar.value.descripcion,
        guia_id: guiaSeleccionado.value,
        fecha: fechaSeleccionada.value,
        hora: horaSeleccionada.value,
        foto: rutaAduplicar.value.foto,
        latitud: rutaAduplicar.value.latitud,
        longitud: rutaAduplicar.value.longitud
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

// Cargamos las rutas al iniciar
onMounted(obtenerRutas);
</script>

<template>
    <div v-if="listaRutas.length > 0" class="container col-xs-12 mb-4">
        <h2 class="text-center mt-4 mb-4">Listado de Rutas</h2>
        <p v-if="error" class="text-red-500">{{ error }}</p>

        <div class="row">
            <div v-for="ruta in paginatedRutas" :key="ruta.id" class="col-md-6 mb-4">
                <div class="tarjeta bg-white shadow rounded p-4">
                    <h2>{{ ruta.titulo }}</h2>
                    <img :src="ruta.foto" title="Imagen de la ruta" alt="Imagen de la ruta"
                        class="ruta-img rounded img-fluid">
                    <main class="row mt-3">
                        <p class="text-gray-700 fs-5 col-6 border rounded p-1"><strong>📅 Fecha:</strong> {{ ruta.fecha }}</p>
                        <p class="text-gray-500 fs-5 col-6 border rounded p-1"><strong>👤 Guía:</strong> {{ ruta.guia_nombre }}</p>
                        <p class="text-gray-500 fs-5 col-6 border rounded p-1"><strong>⌚ Hora:</strong> {{ ruta.hora }}</p>
                        <p class="text-gray-500 fs-5 col-6 border rounded p-1"><strong>📍 Localidad:</strong> {{ ruta.localidad }}</p>
                        <button class="btn btn-primary m-2" @click="abrirModalDuplicar(ruta)">🔁 Duplicar</button>
                        <button class="btn btn-danger m-2" @click="eliminarRuta(ruta.id)">❌ Eliminar</button>
                    </main>
                </div>
            </div>
        </div>
        <nav v-if="totalPages > 1" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <button class="page-link" @click="cambiarPagina(currentPage - 1)">Anterior</button>
                </li>

                <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: currentPage === page }">
                    <button class="page-link" @click="cambiarPagina(page)">{{ page }}</button>
                </li>

                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <button class="page-link" @click="cambiarPagina(currentPage + 1)">Siguiente</button>
                </li>
            </ul>
        </nav>
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
                    <label class="form-label fs-4">Selecciona Fecha:</label>
                    <input type="date" v-model="fechaSeleccionada" :min="fechaHoy" class="form-control mb-2" @change="filtrarGuias">

                    <label class="form-label fs-4" for="guia">Seleccione un guía disponible*</label>
                    <select id="guia" class="form-control" @click="comprobarGuias" v-model="guiaSeleccionado">
                        <option value="" disabled>Seleccione un guía</option>
                        <option v-for="guia in guiasDisponibles" :key="guia.id" :value="guia.id">{{ guia.nombre }}
                        </option>
                    </select>

                    <label class="form-label fs-4">Selecciona Hora:</label>
                    <select v-model="horaSeleccionada" class="form-control">
                        <option value="" disabled>Seleccione una hora</option>
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
p:hover{
    background-color:  rgb(187, 255, 187);;
}
</style>
