<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plans extends Model
{
    use HasFactory;

    protected $fillable = [
    	"period", // month|year
      "interval",
    	"coverage", // insurance cover
    	"price", // total price
      "price_per_month",
    	"create_at",
    	"update_at",
    ];
}
