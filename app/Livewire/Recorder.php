<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

use Log;

class Recorder extends Component
{
    use WithFileUploads;
  
    public $recordingFile;
    public $recordingName;

    public function updatedRecordingFile(){
        // Upload this new recording to S3 bucket, in clips directory
        $fileName   = $this->recordingName; 
        $resultPath = $this->recordingFile->storePubliclyAs('clips',  $fileName, 's3');
    
    }

    public function render()
    {
        return view('livewire.recorder');
    }
}
