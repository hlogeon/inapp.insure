<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteCashback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cashback_id',
        'value',
    ];
}
