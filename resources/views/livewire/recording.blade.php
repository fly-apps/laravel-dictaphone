<div>
    <article class="clip">
        <audio controls="" src="{{  $uri }}" preload="none"></audio>
        <button @click="$dispatch('prompt-update')" ><p>{{ $name }}</p></button>
        <button class="delete btn btn-danger" wire:click="delete">Delete</button>
    </article>

    @script
    <script>
        $wire.on('prompt-update',(event)=>{
            const newClipName = prompt('Enter a new name for your sound clip?');
            if (newClipName === null || newClipName === "") {
                console.log('oks');
            } else {
                $wire.updateName(newClipName);
            }
        });
    </script>
    @endscript
</div>
