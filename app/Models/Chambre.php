<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chambre extends Model
{
    protected $table = "CHAMBRE";
    protected $primaryKey = "IDCHAMBRE";
    public $timestamps = false;


    public function resident(): BelongsTo
    {
        return $this->belongsTo(
            Resident::class,
            "IDRESIDENT", 
            "IDRESIDENT"  
        );
    }
    public function futureResidents()
    {
        return $this->hasMany(Resident::class, 'CHAMBREASSIGNE', 'IDCHAMBRE')
                    ->where('DATEINSCRIPTION', '>', now())
                    ->orderBy('DATEINSCRIPTION', 'asc');
    }
    
}
