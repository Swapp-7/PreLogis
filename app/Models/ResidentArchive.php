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
    protected $fillable = [
        'IDCHAMBRE',                 
        'IDADRESSE',
        'NOMRESIDENTARCHIVE',
        'PRENOMRESIDENTARCHIVE',
        'TELRESIDENTARCHIVE',
        'MAILRESIDENTARCHIVE',
        'DATENAISSANCEARCHIVE',
        'NATIONALITEARCHIVE',
        'ETABLISSEMENTARCHIVE',
        'ANNEEETUDEARCHIVE',
        'PHOTOARCHIVE',
        'DATEINSCRIPTIONARCHIVE',
        'DATEARCHIVE',
        'TYPEARCHIVE',           
        'CHAMBREOCCUPEESARCHIVE' 
    ];

    protected $dates = [
        'DATENAISSANCEARCHIVE',
        'DATEINSCRIPTIONARCHIVE',
        'DATEARCHIVE'
    ];

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
    public function isGroup()
    {
        return $this->TYPEARCHIVE === 'group';
    }
    public function getChambresOccupees()
    {
        if ($this->isGroup() && $this->CHAMBREOCCUPEESARCHIVE) {
            return explode(' | ', $this->CHAMBREOCCUPEESARCHIVE);
        }
        return [];
    }
}
