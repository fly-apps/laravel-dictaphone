<div>
    <article class="clip">
        <audio controls="" src="{{  $uri }}" preload="none"></audio>
        <p>{{ $name }}</p>
        <button class="delete btn btn-danger" wire:click="delete">Delete</button>
    </article>
</div>
