<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class Recorder extends Component
{
    use WithFileUploads;
    public $recordingName;

    
    public $recordingBlob;

    public function mount()
    {
    }

    public function render()
    {
        return view('livewire.recorder');
    }
}
