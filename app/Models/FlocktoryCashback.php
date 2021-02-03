<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlocktoryCashback extends Model
{
    use HasFactory;

    protected $fillable = [
    	"agreement",
    	"siteinfo",
    	"conditions",
    	"site_domain",
    	"site_title",
      "logo",
      "activation_url",
      "premium",
      "popular",
      "featured",
      "updated_at",
      "created_at",
      "deleted_at",
    ];
}
