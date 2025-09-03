<p class="text-xs font-medium text-after-midnight mt-2">Password indicator</p>
<meter id="{{ $meterId ?? 'strengthMeter' }}" min="0" max="4" low="1" high="3" optimum="4" value="0" class="w-full"></meter>
<div id="strengthLabel" class="mt-2 text-xs font-medium text-after-midnight"></div>

{{-- style="width:100%; height:18px; margin-top:6px;" --}}