<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occupation extends Model
{
    protected $table = "OCCUPATION";
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;

    public function momentEvenement()
    {
        return $this->belongsTo(MomentEvenement::class, 'IDMOMENT');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'IDSALLE');
    }
    public function evenement()
    {
        return $this->belongsTo(Evenement::class, 'IDEVENEMENT');
    }
}
