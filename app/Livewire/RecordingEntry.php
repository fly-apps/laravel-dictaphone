<?php

namespace App\Livewire;

use Livewire\Component;

class RecordingEntry extends Component
{
    public $recordingName;
    
    public function render()
    {
        return view('livewire.recording-entry');
    }
}
