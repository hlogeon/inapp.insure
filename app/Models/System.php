<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    use HasFactory;

    protected $fillable = [
    	"status_id",
    	"user_id",
    	"name",
    	"value",
    	"polic_id"
    ];
}
