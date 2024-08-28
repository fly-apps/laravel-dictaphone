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
  
    public $recordingFile;
    public $recordingName;
    public $recordingList = [];


    public function mount()
    {
        $this->refreshList();
    }

    #[On('clip-deleted')] 
    public function refreshList()
    {
        $this->recordingList = Clip::all();
    }

    public function updatedRecordingFile(){
        // Upload this new recording to S3 bucket, in clips directory
        $fileName   = $this->recordingName; 
        $resultPath = $this->recordingFile->storePubliclyAs(env("CLIPS_DIRECTORY"),  $fileName, 's3');

        // Create a new db record
        $clip = Clip::firstOrCreate(
            ['name' => $fileName],
            ['text' => '']
        );

        // Add this new clip to the view's recording list 
        $this->recordingList->push( $clip );
    }

   

    public function render()
    {
        return view('livewire.recorder');
    }
}
