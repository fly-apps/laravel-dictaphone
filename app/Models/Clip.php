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

    public function getUri()
    {
      $filePath = env('CLIPS_DIRECTORY').'/'.$this->name;
      return  Storage::disk('s3')->temporaryUrl(
          $filePath, now()->addMinutes(60)
      );
    }

    public function deleteClip(): bool
    {
      try{
        $filePath = env('CLIPS_DIRECTORY').'/'.$this->name;
        Storage::disk('s3')->delete($filePath);
        Clip::where('name', $this->name)->delete();
        return true;
      }catch(\Exception $e){
        dd( $e );
        return false;
      }
    }

    public function reNameClip( $newName ): string
    {
      $oldName = $this->name;
      try{
        $filePath = env('CLIPS_DIRECTORY').'/'.$this->name;
        $newFilePath = env('CLIPS_DIRECTORY').'/'.$newName;

        // This is essentially renaming!
        Storage::disk('s3')->move($filePath, $newFilePath);
        Clip::where('name', $this->name)->update(['name'=>$newName]);
        return $newName;
      }catch(\Exception $e){
        
        return $oldName;
      }
    }

}
