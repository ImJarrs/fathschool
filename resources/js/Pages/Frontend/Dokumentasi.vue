<template>
  <Head title="Documentation" />
  <FrontendLayout>
    <template #header>
      <h1 class="text-4xl font-bold tracking-tight text-white md:text-5xl lg:text-6xl">
        {{ $page.props.setting.text_logo_name }}
      </h1>
      <p class="mt-6 max-w-3xl text-xl text-blue-gray-300" v-html="$page.props.setting?.app_description">
      </p>
    </template>
    <div class="mt-8">
      <div v-for="(doc, index) in documentations" :key="doc.id" :class="['flex', index % 2 === 0 ? 'flex-row' : 'flex-row-reverse', 'items-start', 'mb-8']">
        <div class="lg:w-1/2 w-full lg:mr-8 mb-4 lg:mb-0">
          <iframe 
            :src="formatYouTubeUrl(doc.video_link)" 
            class="w-full h-60 lg:h-80 rounded-lg shadow-lg" 
            frameborder="0" 
            allowfullscreen 
          ></iframe>
        </div>
        <div class="lg:w-1/2 w-full lg:ml-8 mb-4 lg:mb-0 flex flex-col justify-center">
          <h2 class="text-2xl font-semibold mb-4">{{ doc.title }}</h2>
          <p class="text-gray-700">{{ doc.description }}</p>
        </div>
      </div>
    </div>
  </FrontendLayout>
</template>

<script setup>
import FrontendLayout from "@/Layouts/FrontendLayout.vue";

// Define props to receive data
const props = defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  documentations: Array
});

// Method to format YouTube URL
const formatYouTubeUrl = (url) => {
  if (url) {
    // 1. Tangani berbagai format URL (watch, share link, dll)
    let videoId = '';
    
    if (url.includes('youtube.com/watch?v=')) {
      videoId = url.split('v=')[1]?.split('&')[0];
    } else if (url.includes('youtu.be/')) {
      videoId = url.split('youtu.be/')[1]?.split('?')[0];
    } else if (url.includes('youtube.com/embed/')) {
      return url; // Sudah dalam format embed
    }

    if (videoId) {
      // 2. Gunakan youtube-nocookie untuk menghindari masalah konfigurasi & privasi
      // 3. Tambahkan parameter origin untuk validasi domain
      const origin = window.location.origin;
      return `https://www.youtube-nocookie.com/embed/${videoId}?origin=${origin}&rel=0`;
    }
  }
  return '';
};

</script>
