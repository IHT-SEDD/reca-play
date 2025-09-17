<div x-data="{ open: false }" class="w-full">
 <!-- Thumbnail Videos -->
 <a href="javascript:void(0)">
  <div class="bg-base-300 rounded-xl p-3 h-40 mb-2 relative"
   style="background-image: url('{{ Storage::url($video->thumbnail_path) }}'); background-size: cover; background-position: center;">
   <!-- Total duration -->
   <div class="absolute bottom-2 right-2 text-xs font-mono bg-eerie-black/70 text-white p-2 rounded-xl">
    {{ $recording->duration ?? '-' }}
   </div>
  </div>
 </a>

 <!-- Videos Description -->
 <div class="text-sm space-y-1">
  <p class="font-medium">{{ $recording->video_name }}</p>
  <p>{{ $recording->field->venue->name ?? '-' }} - {{ $recording->field->name }}</p>
 </div>
</div>