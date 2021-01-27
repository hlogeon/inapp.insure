<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarrifs extends Model
{
    use HasFactory;

    protected $fillable = [
    	"name",
    	"description",
    	"sort",
    	"create_at",
    	"update_at",
    	"price",
    	"per_month"
    ];
}
