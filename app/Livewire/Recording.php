<?php

namespace App\Livewire;

use App\Models\Clip;
use Livewire\Component; 

class Recording extends Component
{
    public $uri;
    public $name;

    public function mount( $clip )
    {
        $this->name = $clip->name;
        $this->uri = $clip->getUri();
    }

    public function delete()
    {
        // Delete  db record + s3 file
        $clip =   Clip::where('name',$this->name)->first();
        $result = $clip->deleteClip();

        // Let Parent know that a clip has been deleted so it can refresh its list
        $this->dispatch('clip-deleted');
    }

    public function render()
    {
        return view('livewire.recording');
    }
}
