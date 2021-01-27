<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bso extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bso';

    public $timestamps = false;

    protected $fillable = [
        "company",
        "field_1",
        "field_2"
    ];
}
