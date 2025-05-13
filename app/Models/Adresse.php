<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adresse extends Model
{
    protected $table = "ADRESSE";
    protected $primaryKey = "IDADRESSE";
    public $timestamps = false;
}
