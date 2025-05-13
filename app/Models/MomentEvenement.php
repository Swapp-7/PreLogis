<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MomentEvenement extends Model
{
    protected $table = "MOMENTEVENEMENT";
    protected $primaryKey = "IDMOMENT";
    public $timestamps = false;
}
