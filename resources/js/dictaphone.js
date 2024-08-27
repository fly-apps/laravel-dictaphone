window.connect = function( $wire,record, stop, soundClips, canvas, mainSection, fileInput ){    
  // Disable stop button while not recording
  stop.disabled = true;

  // Visualiser setup - create web audio api context and canvas
  let audioCtx;
  const canvasCtx = canvas.getContext("2d");

  // Main block for doing the audio recording
  if (navigator.mediaDevices.getUserMedia) {
      
      console.log("The mediaDevices.getUserMedia() method is supported.");
      const constraints = { audio: true };
      let chunks = [];

      let onSuccess = function (stream) {
          const mediaRecorder = new MediaRecorder(stream, {
            mimeType: MediaRecorder.isTypeSupported("audio/mp4")
              ? "audio/mp4"
              : "audio/webm; codecs=opus",
          });
    
          visualize(stream); 
    
          record.onclick = function () {
            mediaRecorder.start();
            console.log(mediaRecorder.state);
            console.log("Recorder started.");
            record.style.background = "red";
    
            stop.disabled = false;
            record.disabled = true;
          };
    
          stop.onclick = function () {
            mediaRecorder.stop();
            console.log(mediaRecorder.state);
            console.log("Recorder stopped.");
            record.style.background = "";
            record.style.color = "";
    
            stop.disabled = true;
            record.disabled = false;
          };
    
          mediaRecorder.onstop = async function (e) {
            console.log("Last data to read (after MediaRecorder.stop() called).");

            const blob = new Blob(chunks, { type:chunks[0].type });

            // File Name to Save
            const clipName = prompt(
              "Enter a name for your sound clip?",
              "My unnamed clip"
            );
            let fileName =  clipName+'.mp4';
            let file = new File([blob], fileName);

            // Update the filename in local javascript( will be sent up alongside the upload below )
            $wire.set("recordingName",fileName);
            // Upload the file
            $wire.upload("recordingFile",file,(uploadedFilename) => {
              // Success callback...
              console.log("successfully uploaded blob!");
            }, (e) => {
              // Error callback...
              console.log("error in uploading blob!");
              console.log(e)
            }, (event) => {
              // Progress...
              console.log("uploading blob...");
            }, () => {
              // Cancelled...
              console.log('cancelled blob upload!');
            });
          };
    
          mediaRecorder.ondataavailable = function (e) {
            chunks.push(e.data);
          };
      };

      let onError = function (err) {
          console.log("The following error occured: " + err);
      };

      navigator.mediaDevices.getUserMedia(constraints).then(onSuccess, onError);
  }else{
    alert("MediaDevices.getUserMedia() not supported on your browser!");
  }

  function visualize(stream) {
      if (!audioCtx) {
        audioCtx = new AudioContext();
      }
  
      const source = audioCtx.createMediaStreamSource(stream);
  
      const analyser = audioCtx.createAnalyser();
      analyser.fftSize = 2048;
      const bufferLength = analyser.frequencyBinCount;
      const dataArray = new Uint8Array(bufferLength);
  
      source.connect(analyser);
  
      draw();
  
      function draw() {
        const WIDTH = canvas.width;
        const HEIGHT = canvas.height;
  
        requestAnimationFrame(draw);
  
        analyser.getByteTimeDomainData(dataArray);
  
        canvasCtx.fillStyle = "rgb(200, 200, 200)";
        canvasCtx.fillRect(0, 0, WIDTH, HEIGHT);
  
        canvasCtx.lineWidth = 2;
        canvasCtx.strokeStyle = "rgb(0, 0, 0)";
  
        canvasCtx.beginPath();
  
        let sliceWidth = (WIDTH * 1.0) / bufferLength;
        let x = 0;
  
        for (let i = 0; i < bufferLength; i++) {
          let v = dataArray[i] / 128.0;
          let y = (v * HEIGHT) / 2;
  
          if (i === 0) {
            canvasCtx.moveTo(x, y);
          } else {
            canvasCtx.lineTo(x, y);
          }
  
          x += sliceWidth;
        }
  
        canvasCtx.lineTo(canvas.width, canvas.height / 2);
        canvasCtx.stroke();
      }
  }
}


