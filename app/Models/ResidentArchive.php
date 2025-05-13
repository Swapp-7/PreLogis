<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentArchive extends Model
{
    protected $table = "RESIDENTARCHIVE";
    protected $primaryKey = "IDRESIDENTARCHIVE";
    public $timestamps = false;

    public function parents()
    {
        return $this->belongsToMany(Parents::class,'AVAITPOURPARENT','IDRESIDENTARCHIVE','IDPARENT');
    }
    public function chambre()
    {
        return $this->hasOne(Chambre::class, 'IDCHAMBRE', 'IDCHAMBRE');
    }
    public function adresse(): BelongsTo
    {
        return $this->belongsTo(
            Adresse::class,
            "IDADRESSE", 
            "IDADRESSE"  
        );
    }
    public function fichiers()
    {
        return $this->hasMany(Fichier::class, 'IDRESIDENTARCHIVE', 'IDRESIDENTARCHIVE');
    }
}
