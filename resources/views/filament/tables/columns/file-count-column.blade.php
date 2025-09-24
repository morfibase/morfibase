<div {{ $getExtraAttributeBag() }}>
    @php
       $files = $getState() !== null ? json_decode($getState()) : [];
       $filesCount = count($files);
    @endphp

    @if($filesCount >= 1)
        {{ $filesCount }} file{{$filesCount == 1 ? '' : 's'}}
    @else
        <span class="text-gray-500">No files</span>
    @endif
</div>
