<template>
    <div class="card">
      <h3>{{ title }}</h3>
      <slot>Contenido por defecto</slot>
    </div>
  </template>
  
  <script setup>
  defineProps({
    title: String
  });
  </script>
  
  <style scoped>
  .card {
    border: 1px solid #ddd;
    padding: 1rem;
    border-radius: 8px;
    background: white;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
  }
  </style>
  