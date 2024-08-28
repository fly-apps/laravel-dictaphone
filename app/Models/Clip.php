<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Storage;

class Clip extends Model
{
    use HasFactory;

      /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $fillable = [
      'name',
      'text'
    ];

    public function getAsArrayItem(){
        $filePath = env('CLIPS_DIRECTORY').'/'.$this->name;

        return [
            'name' => $this->name,
            'uri'  => Storage::disk('s3')->temporaryUrl(
                $filePath, now()->addMinutes(60)
            )
        ];
    }
}
