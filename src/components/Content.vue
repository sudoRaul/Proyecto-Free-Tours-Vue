<script setup>

</script>

<template>
      
      <div class="barralateral row body-content">
        <aside class=" bg-secondary text-white p-2 col-3">
          <slot name="sidebar">Sidebar por defecto</slot>
        </aside>
        
        <main class="col-9">
          <slot name="main">Contenido principal</slot>
        </main>
      </div>
  
  </template>
  

  <style scoped>
  .barralateral{
    flex-grow: 1;
  }

  </style>
  