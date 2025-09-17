<div class="w-full grid grid-cols-1 md:grid-cols-5 gap-4">
 @forelse ($recordings as $recording)
 @foreach ($recording->recordedVideo as $video)
 @include('pages.recording.partials.recording_card', [
 'recording' => $recording,
 'video' => $video
 ])
 @endforeach
 @empty
 <p class="col-span-5 text-center text-sm text-magnesium">No recordings found.</p>
 @endforelse

 @if ($recordings->hasMorePages())
 <div class="items-center flex justify-center md:col-span-5 w-full mt-8">
  <x-secondary-button class="w-fit max-w-xs" btnId="seemore_btn" bg="bg-base-200" hoverBg="hover:bg-base-300"
   textColor="text-after-midnight">
   {{ __('See More') }}
   <i data-lucide="ellipsis" class="w-4 h-4 ms-2"></i>
  </x-secondary-button>
 </div>
 @endif
</div>