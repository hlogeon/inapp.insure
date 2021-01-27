<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopMenu extends Model
{
    use HasFactory;
    protected $fillable = ["title", "href", "active", "sort", "create_at"];
}
