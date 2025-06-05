<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = "ADMIN";
    protected $primaryKey = "IDADMIN";
    public $timestamps = false;
    
    protected $fillable = [
        'NOMUTILISATEUR',
        'EMAIL',
        'MOTDEPASSE'
    ];
}
