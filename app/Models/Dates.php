<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dates extends Model
{
    protected $table = "DATES";
    protected $primaryKey = "DATEPLANNING";
    protected $casts = [
        'DATEPLANNING' => 'date:Y-m-d',
    ];
    public $timestamps = false;

}
