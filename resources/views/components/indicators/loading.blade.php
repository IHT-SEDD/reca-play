@props([
'id' => 'loadingIndicator'
])


<div id="{{ $id }}"
    class="fixed inset-0 z-50 w-full h-screen bg-magnesium/70 flex justify-center items-center opacity-0 invisible transition-opacity duration-200">
    <div class="w-[20vw] max-w-[200px] min-w-[100px] aspect-square">
        <dotlottie-wc src="{{ asset('assets/animations/loading.lottie') }}" class="w-full h-full" speed="1.5" autoplay
            loop>
        </dotlottie-wc>
    </div>
</div>