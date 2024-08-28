<?php

namespace App\Livewire;

use Livewire\Component;

class Recording extends Component
{
    public $uri;
    public $name;

    public function mount( $record )
    {
        $this->name = $record['name'];
        $this->uri = $record['uri'];
    }

    public function render()
    {
        return view('livewire.recording');
    }
}
