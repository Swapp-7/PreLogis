<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fichier extends Model
{
    protected $table = "FICHIER";
    protected $primaryKey = "IDFICHIER";
    public $timestamps = false;
}
