@props([
'id' => 'loadingIndicator'
])


<div id="{{ $id }}"
    class="fixed inset-0 z-50 w-full h-screen bg-magnesium/70 flex justify-center items-center opacity-0 invisible transition-opacity duration-200">
    <dotlottie-wc src="{{ asset('assets/animations/loading.lottie') }}"
        class="lg:w-[40%] md:w-[30%] sm:w-[20%] w-auto h-auto" speed="1.5" autoplay loop>
    </dotlottie-wc>
</div>