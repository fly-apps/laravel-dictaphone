<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class Recorder extends Component
{
    use WithFileUploads;
  
    public $recordingBlob;
    public $recordingName;

    public function render()
    {
        return view('livewire.recorder');
    }
}
