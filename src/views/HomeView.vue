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

    <div class="row mt-5 mb-5">
        <h2 class="text-center fw-bold">¿Por qué elegirnos?</h2>
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

    <div class="row">
      <h2 class="text-center fw-bold">Mejores destinos</h2>
    </div>

  </template>
  
  <style scoped>
  .carousel-image {
    object-fit: cover;
    height: 400px;
  }
  </style>
  