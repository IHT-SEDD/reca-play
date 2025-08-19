<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <!-- Camera live preview -->
                        <div class="w-full">
                            <h2 class="text-lg font-bold mb-4">Live Camera Preview</h2>

                            {{-- Live Preview (pakai WebRTC player) --}}
                            <video id="cameraVideo" autoplay playsinline muted
                                style="display:block; max-width:100%; background:black;">
                            </video>
                            <button id="fullscreenBtn">Fullscreen</button>
                        </div>

                        <div class="w-full">
                        </div>
                    </div>

                    <div class="mt-4 flex space-x-2">
                        <button id="btnStart" class="px-4 py-2 bg-green-500 text-white rounded">
                            Start Recording
                        </button>
                        <button id="btnStop" class="px-4 py-2 bg-red-500 text-white rounded">
                            Stop Recording
                        </button>
                    </div>

                    {{-- Recording List --}}
                    <div class="mt-8 border-t pt-4">
                        <h3 class="text-lg font-bold">Recorded Videos</h3>
                        <table class="mt-4 border w-full text-sm">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="p-2 border">Thumbnail</th>
                                    <th class="p-2 border">Start</th>
                                    <th class="p-2 border">End</th>
                                    <th class="p-2 border">Action</th>
                                </tr>
                            </thead>
                            <tbody id="recordingsTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script src="{{ asset('assets/js/camera.js') }}"></script>
        <script>
            const video = document.getElementById('cameraVideo');
            const fullscreenBtn = document.getElementById('fullscreenBtn');
            fullscreenBtn.addEventListener('click', () => {
                if (video.requestFullscreen) {
                    video.requestFullscreen();
                } else if (video.webkitRequestFullscreen) {
                    video.webkitRequestFullscreen();
                } else if (video.msRequestFullscreen) {
                    video.msRequestFullscreen();
                }
            });
        </script>
    </x-slot>
</x-app-layout>