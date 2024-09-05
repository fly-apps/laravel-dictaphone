<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Clip;
use Livewire\Attributes\On;

use Log;

class Recorder extends Component
{
    use WithFileUploads;
  
    public $currentClipId;
    public $recordingFile;
    public $recordingName;
    public $recordingList = [];


    public function mount()
    {
        // Set list of clips on init
        $this->refreshList();
    }

    #[On('clip-deleted')] 
    public function refreshList()
    {
        // RefreshList when called, or when clip-deleted livewire event dispatched
        $this->recordingList = Clip::all();
    }

    /**
     * Function HOOK! called from view by Livewire when the $recordingFile gets updated: 
     * 
     * Notice! in the view, `$wire.upload("recordingFile",...` is called to upload a file related to the $recordingFile
     * Notice! after successful upload of the file, Livewire updates attribute $recordingFile with details about the uploaded file, calling this hook afterwards!
     * 
     * This hook: 
     * 1. Updates the livewire component's attributes
     * 2. Uploads the file livewire uploaded in a temporary directory to the Tigris/S3 bucket
     * 3. Creates a row in the clips db table, through the use of App\Models\Clip model
     * 4. Updates the livewire component's recordingList by pushing new clip into the list( doing so will refresh the list! )
     * 5. Dispatches a ClipCreated Event to the frontend through websocket, thanks to the event's use of ShouldBroadcast interface, sending this to the connected clients
     * 
     */
    public function updatedRecordingFile(){
        // Upload this new recording to S3 bucket, in clips directory
        $fileName   = $this->recordingName; 
        $resultPath = $this->recordingFile->storePubliclyAs( env("CLIPS_DIRECTORY"),  $fileName, 's3' );

        // Create a new db record of the recording, as a clip row
        $clip = Clip::firstOrCreate(
            ['name' => $fileName],
            ['text' => '']
        );

        // Update the current created clip's id to frontend for reference
        // Will use this so a client where the clip originated from doesnt react to its own event dispatch
        $this->currentClipId = $clip->id;

        // Add this new clip to the view's recording list 
        $this->recordingList->push( $clip );

        // Send this clip created to all connected clients so they can refersh their List!
        \App\Events\ClipCreated::dispatch( $clip );
    }

    public function render()
    {
        return view( 'livewire.recorder' );
    }
}
