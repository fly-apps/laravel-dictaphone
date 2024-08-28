<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Clip;

use Log;

class Recorder extends Component
{
    use WithFileUploads;
  
    public $recordingFile;
    public $recordingName;
    public $recordingList = [];


    public function mount()
    {
        $clips = Clip::all();
        $this->recordingList = [];
        foreach( $clips as $clip ){
            //  clip name, plus the s3 link into an array
            $this->recordingList[] = $clip->getAsArrayItem();
        }
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
        $this->recordingList[] = $clip->getAsArrayItem();
    }

    public function render()
    {
        return view('livewire.recorder');
    }
}
