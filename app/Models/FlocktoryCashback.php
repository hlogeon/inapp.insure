<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CashbackCompany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlocktoryCashback extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
    	"agreement",
    	"siteinfo",
    	"conditions",
      "activation_url",
      "cashback_company_id",
      "flocktory_id",
      "premium",
      "popular",
      "featured",
      "updated_at",
      "created_at",
      "deleted_at",
    ];

    public function cashbackCompany()
    {
      return $this->belongsTo(CashbackCompany::class, "cashback_company_id");
    }

}
