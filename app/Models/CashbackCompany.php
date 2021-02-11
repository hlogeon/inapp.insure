<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FlocktoryCashback;

class CashbackCompany extends Model
{
    use HasFactory;

    protected $fillable = [
      "flocktory_id",
      "title",
      "domain",
      "logo",
      "created_at",
      "updated_at",
    ];

    public function flocktoryCashbacks()
    {
      return $this->hasMany(FlocktoryCashback::class);
    }
}
