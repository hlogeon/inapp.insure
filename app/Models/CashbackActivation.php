<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashbackActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'flocktory_cashback_id',
        'created_at',
        'updated_at',
    ];
}
