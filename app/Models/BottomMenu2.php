<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BottomMenu2 extends Model
{
    use HasFactory;

    protected $fillable = [
    	"title",
    	"href",
    	"active",
    	"sort"
    ];
}
