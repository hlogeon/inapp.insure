<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromocodeActivations extends Model
{
    use HasFactory;

    protected $fillable = [
    	"promocode_id",
      "user_id",
    	"payment_id", // insurance cover
    	"activated_at", // total price
      "discount",
    ];
}
