<div class="bg-amber-500 bg-center">
    <section class="main-controls bg-amber-500">
        <canvas class="visualizer" height="60px"></canvas>
        <div id="buttons">
          <button class="record btn btn-blue">Record</button>
          <button class="stop btn btn-red" onClick="uploadChunks()">Stop</button>
        </div>
    </section>

    <section class="sound-clips">
        @foreach( $recordingList as $record )
          <livewire:recording 
            :clip=$record 
            wire:key="{{ $record->name }}"/>
        @endforeach
    </section>

    @vite(['resources/js/dictaphone.js','resources/js/echo.js'])
    
    @script
    <script>
      // Set up basic variables for app  
      connect( $wire,
        document.querySelector(".record"),
        document.querySelector(".stop"),
        document.querySelector(".sound-clips"),
        document.querySelector(".visualizer"),
        document.querySelector(".main-controls"),
        document.querySelector('input[type="file"]')
      );

      // Serves as a flag on whether to pause reaction to a clip-created event from listening to websocket clips channel
      $pendingUpdate = false;

      // Listen to a websocket channel tru Laravel Echo js package 
      // Make sure to add a "." before the event name, other wise it ain't gonna listen to the event
      Echo.channel('clips')
      .listen('.clip-created', e => {
        //console.log(e);
        //console.log( @this.currentClipId );

        if (document.querySelector(".record").style.backgroundColor !== "") {
          // Pause reaction if user recording 
          $pendingUpdate = true;
        }else if($pendingUpdate || e.clip.id != @this.currentClipId ){
          // Ask livewire to refresh the list it has, so that the new clip from another client can get shown
          // @this.<LivewireComponentFunctionName> -> syntax for calling livewire func in the background, sent tru "/livewire/update" route
          @this.refreshList();
          $pendingUpdate = false;
        }    
      });
    </script>
    @endscript
</div>
