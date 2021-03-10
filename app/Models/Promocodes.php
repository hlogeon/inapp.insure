<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocodes extends Model
{
    use HasFactory;

    protected $fillable = [
    	"name",
      "description",
    	"from", // insurance cover
    	"to", // total price
      "is_percent",
    	"value",
    	"code",
      "max_activations",
      "activations",
    ];
}
