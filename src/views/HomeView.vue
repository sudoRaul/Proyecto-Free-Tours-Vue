<script setup>
 import { ref } from "vue";
 import router from "../router";
 import Swal from "sweetalert2";



const localidad = ref("");
const fecha = ref("")
const listaRutas = ref([]);


const video = ref(null);
const showControls = ref(false);
const isPlaying = ref(false);
const isMuted = ref(false);
const isFullscreen = ref(false);
const speed = ref(1); // Inicializamos la velocidad a 1
// Necesitaremos la fecha de hoy para que se introduzca una fecha a partir de esta
const fechaHoy = new Date().toISOString().split("T")[0];

const pausePlay = () => {
  if (video.value.paused) {
    video.value.play();
    isPlaying.value = true;
  } else {
    video.value.pause();
    isPlaying.value = false;
  }
};

const volumeUp = () => {
  if (video.value.volume < 1) video.value.volume += 0.1;
};

const volumeDown = () => {
  if (video.value.volume > 0) video.value.volume -= 0.1;
};

const increaseTime = () => {
  video.value.currentTime += 5
}

const decreaseTime = () => {
  video.value.currentTime -= 5
}

const mute = () => {
  video.value.muted = !video.value.muted;
  isMuted.value = video.value.muted;
};
const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    video.value.requestFullscreen?.() || video.value.webkitRequestFullscreen?.();
    isFullscreen.value = true;
  } else {
    document.exitFullscreen?.() || document.webkitExitFullscreen?.();
    isFullscreen.value = false;
  }
};

const increaseSpeed = () => {
  if (speed.value < 2) {
    speed.value += 0.5;
    video.value.playbackRate = speed.value;
  }else{
    speed.value = 1
  }
};

async function obtenerRutas() {
    try {
        const response = await fetch("http://localhost/APIFreetours/api.php/rutas");
        if (!response.ok) throw new Error("Error al obtener las rutas");
        
        const data = await response.json();
        listaRutas.value = data; // Guardamos las rutas en la variable 
    } catch (err) {
        error.value = err.message;
    }
};
obtenerRutas()

const validarFormulario = () => {
  if (!fecha.value) {
    Swal.fire({
      icon: "warning",
      title: "Atención",
      text: "Por favor, seleccione una fecha antes de buscar."
    });
    return;
  }
  router.push(`/rutas-filtradas/${fecha.value}/${localidad.value}`);
};

</script>
<template>
    <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
      </div>
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="@/images/carrusel/granCanion.png" class="d-block w-100 carousel-image" alt="Gran Canyon">
          <div class="carousel-caption d-none d-md-block">
            <h5>Gran Canyon, Colorado, USA</h5>
          </div>
        </div>
        <div class="carousel-item">
          <img src="@/images/carrusel/alpesSuizos.webp" class="d-block w-100 carousel-image" alt="Los Alpes">
          <div class="carousel-caption d-none d-md-block">
            <h5>Los Alpes, Suiza</h5>
          </div>
        </div>
        <div class="carousel-item">
          <img src="@/images/carrusel/sierraSegura.webp" class="d-block w-100 carousel-image" alt="Sierra de Segura">
          <div class="carousel-caption d-none d-md-block">
            <h5>Sierra de Segura, Jaén, España</h5>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
      </button>
    </div>

    <div class="mt-5">
      <h2 class="text-center fw-bold mb-3" id="reservar"> <img src="@/images/iconos/search.png" alt="Icono de búsqueda" title="Buscar ruta"> Planificamos tu próxima aventura</h2>
      <div class="container text-center">
        <form>
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-12 p-0">
                          <select class="form-control search-slt" name="localidad" id="localidad" v-model="localidad">
                            <option value="" disabled>Seleccione una ciudad</option>
                            <option v-for="localidad in listaRutas" :key="localidad.localidad" :value="localidad.localidad">{{ localidad.localidad }}</option>
                          </select>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-12 p-0">
                            <input type="date" :min="fechaHoy" v-model="fecha" class="form-control search-slt" required>
                        </div>
                        
                        <div class="col-lg-3 col-md-3 col-sm-12 p-0">
                            <button type="button" aria-label="Buscar ruta" @click.prevent="validarFormulario" class="btn btn-primary wrn-btn">Buscar ruta</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    </div>

    <div class="row mt-5 mb-5">
        <h2 class="text-center fw-bold" id="quienesSomos">¿Por qué elegirnos?</h2>
        <div class="row mt-3 text-center">
            <div class="col-4">
              <img src="@/images/iconos/phoneIcon.png" alt="Icono Teléfono">
                <h3 class="fw-bold">Experiencias personalizadas</h3>
                <p class="fw-normal">Tours adaptados a las <br>necesidades de los clientes</p>
            </div>
            <div class="col-4">
              <img src="@/images/iconos/star.png" alt="Icono Teléfono">
                <h3 class="fw-bold">Guías expertos</h3>
                <p class="fw-normal">Equipo formado por profesionales <br>con profundo conocimiento histórico y cultural</p>
            </div>
            <div class="col-4">
              <img src="@/images/iconos/relax.png" alt="Icono Teléfono">
                <h3 class="fw-bold">Comodidad y seguridad</h3>
                <p class="fw-normal">Seguros de viaje, atención al cliente 24 horas <br>los 365 días del año y cancelación gratuita</p>
            </div>
        </div>
    </div>

    <div id="topDestinos">
      <h2 class="text-center fw-bold mb-4">Mejores destinos</h2>
      <div class="row justify-content-around mb-5">
        <img src="@/images/contenido/madrid.jpg" alt="Foto Madrid" aria-hidden="true" title="Madrid" class="imagenContenedor col-3 mb-4">
        <img src="@/images/contenido/canada.jpg" alt="Foto Canada" aria-hidden="true" title="Canadá" class="imagenContenedor col-3">
        <img src="@/images/contenido/paris.jpg" alt="Foto Paris" aria-hidden="true" title="París" class="imagenContenedor col-3">
        <img src="@/images/contenido/japon.jpg" alt="Foto Japon" aria-hidden="true" title="Japón" class="imagenContenedor col-3">
        <img src="@/images/contenido/australia.jpg" alt="Foto Australia" aria-hidden="true" title="Australia" class="imagenContenedor col-3">
        <img src="@/images/contenido/roma.jpg" alt="Foto Roma" aria-hidden="true" title="Roma" class="imagenContenedor col-3">
        <img src="@/images/contenido/sf.jpg" alt="Foto San Francisco" aria-hidden="true" title="San Francisco" class="imagenContenedor col-3">
        <img src="@/images/contenido/londres.jpg" alt="Foto Londres" aria-hidden="true" title="Londres" class="imagenContenedor col-3">
      </div>
    </div>
    <div class="mb-5">
      <h2 class="text-center fw-bold mb-4">¡Sigue nuestros vídeos en nuestras plataformas!</h2>
      <div class="video-container" @mouseover="showControls = true" @mouseleave="showControls = false">
    <video ref="video" @play="isPlaying = true" @pause="isPlaying = false">
      <source src="@/images/videoRonda.mp4" type="video/mp4" />
      <source src="@/images/videoRondaFire.ogg" type="video/mp4"/>
    </video>

    <div class="controls" v-show="showControls">
      <button aria-label="Iniciar/Pausar vídeo" @click="pausePlay">{{ isPlaying ? "⏸️" : "▶️" }}</button>
      <button aria-label="Activar/Desactivar sonido" @click="mute">{{ isMuted ? "🔇" : "🔊" }}</button>
      <button aria-label="Aumentar velocidad" @click="increaseSpeed">⏩ x{{ speed }}</button>
      <button aria-label="Aumentar 5 segundos" @click="increaseTime">+5🕰️</button>
      <button aria-label="Decrementar 5 segundos" @click="decreaseTime">-5🕰️</button>
      <button aria-label="Subir volumen" @click="volumeUp">🔊</button>
      <button aria-label="Bajar volumen" @click="volumeDown">🔉</button>
      <button aria-label="Pantalla grande" @click="toggleFullscreen">{{ "🔳" }}</button>
    </div>
  </div>
    </div>
  </template>
  
  <style scoped>
  .carousel-image {
    object-fit: cover;
    height: 400px;
  }.imagenContenedor {
    width: 390px;
    height: 150px;
    border-radius: 50px;
    transition: transform 0.5s ease;
  }.imagenContenedor:hover {
    transform: scale(1.1);
}
.video-container {
      position: relative;
      width: 600px;
      margin: auto;
    }

    video {
      width: 100%;
      display: block;
      border-radius: 8px;
    }

    .controls {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      background: rgba(0, 0, 0, 0.7);
      display: flex;
      justify-content: center;
      gap: 10px;
      padding: 10px;
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }

    .video-container:hover .controls {
      opacity: 1;
    }

    .controls button {
      background: rgba(255, 255, 255, 0.8);
      border: none;
      padding: 8px 12px;
      cursor: pointer;
      font-size: 16px;
      border-radius: 5px;
      transition: background 0.2s;
    }

    .controls button:hover {
      background: white;
    }
    .search-sec{
    padding: 2rem;
}
.search-slt{
    display: block;
    width: 100%;
    font-size: 0.875rem;
    line-height: 1.5;
    color: #55595c;
    background-color: #fff;
    background-image: none;
    border: 1px solid #ccc;
    height: calc(3rem + 2px) !important;
    border-radius:0;
}
.wrn-btn{
    width: 100%;
    font-size: 16px;
    font-weight: 400;
    text-transform: capitalize;
    height: calc(3rem + 2px) !important;
    border-radius:0;
}
form{
  margin-left: 21%;
}
  </style>
  
