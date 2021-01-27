<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashback extends Model
{
    use HasFactory;

    protected $fillable = [
    	"image",
    	"name",
    	"description",
    	"sort",
    	"public",
    	"create_at",
    	"updated_at"
    ];
}
