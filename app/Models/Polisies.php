<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Polisies extends Model
{
    use HasFactory;

    protected $fillable = [
    	"active",
    	"user_id",
    	"number",
    	"address",
    	"appartment",
    	"start",
    	"finish",
    	"tarrif_id",
        "subscribed",
        "changed_tarrif",
        "status_id"
    ];

    protected $casts = [
	    'start' => 'datetime:Y-m-d',
	    'finish' => 'datetime:Y-m-d',
	];
    
}
