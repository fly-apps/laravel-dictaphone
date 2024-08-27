<div class="bg-amber-500 bg-center">
    <section class="main-controls bg-amber-500">
        <canvas class="visualizer" height="60px"></canvas>
        <div id="buttons">
          <button class="record btn btn-blue">Record</button>
          <button class="stop btn btn-red" onClick="uploadChunks()">Stop</button>
        </div>
    </section>

    <input type="file"  />
    <section class="sound-clips">
        <article class="clip">
              <audio controls="" src="/audio/<%- encodeURI(clip.name) %>" preload="none"></audio>
              <p>Test</p>
              <button class="delete">Delete</button>
              <p class="text">Description</p>
        </article>
    </section>

    @asset
    @vite('resources/js/dictaphone.js')
    @endasset

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
      
       
    </script>
    @endscript
</div>
