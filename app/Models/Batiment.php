<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batiment extends Model
{
    protected $table = "BATIMENT";
    protected $primaryKey = "IDBATIMENT";
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    public function chambres(): HasMany
    {
        return $this->hasMany(Chambre::class, 'IDBATIMENT', 'IDBATIMENT');
        
    }
}
    