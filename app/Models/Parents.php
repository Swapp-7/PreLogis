<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parents extends Model
{
    protected $table = "PARENT";
    protected $primaryKey = "IDPARENT";
    public $timestamps = false;

    public function residents()
    {
        return $this->belongsToMany(Resident::class, 'APOURPARENT', 'IDPARENT', 'IDRESIDENT');
    }

    public function residentsArchives()
    {
        return $this->belongsToMany(ResidentArchive::class, 'AVAITPOURPARENT', 'IDPARENT', 'IDRESIDENTARCHIVE');
    }

}
