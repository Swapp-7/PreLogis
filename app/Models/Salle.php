<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $table = "SALLE";
    protected $primaryKey = "IDSALLE";
    public $timestamps = false;
}
