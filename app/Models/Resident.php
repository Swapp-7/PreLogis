<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    protected $table = "RESIDENT";
    protected $primaryKey = "IDRESIDENT";
    public $timestamps = false;

    // Constantes pour les types de résidents
    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_GROUP = 'group';

    public function parents()
    {
        return $this->belongsToMany(Parents::class, 'APOURPARENT', 'IDRESIDENT', 'IDPARENT');
    }

    public function adresse(): BelongsTo
    {
        return $this->belongsTo(
            Adresse::class,
            "IDADRESSE", 
            "IDADRESSE"  
        );
    }
    
    public function chambre()
    {
        return $this->hasOne(Chambre::class, 'IDRESIDENT', 'IDRESIDENT');
    }
    
    public function chambreAssigne()
    {
        return $this->belongsTo(Chambre::class, 'CHAMBREASSIGNE', 'IDCHAMBRE');
    }

    public function fichiers()
    {
        return $this->hasMany(Fichier::class, 'IDRESIDENT', 'IDRESIDENT');
    }

    // Méthodes pour vérifier le type de résident
    public function isIndividual()
    {
        return $this->TYPE === self::TYPE_INDIVIDUAL;
    }

    public function isGroup()
    {
        return $this->TYPE === self::TYPE_GROUP;
    }

    // Méthode pour récupérer les groupes
    public static function groups()
    {
        return self::where('TYPE', self::TYPE_GROUP);
    }
}


