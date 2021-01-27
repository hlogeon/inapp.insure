<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BsoIndexes extends Model
{
    use HasFactory;

    protected $table = 'bso_indexes';

    public $timestamps = false;

    protected $fillable = [
        "company",
        "pattern",
        "index"
    ];
}
