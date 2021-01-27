<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;
    protected $fillable = [
    	"user_id",
    	"polic_id",
        "tarrif_id",
    	"Amount",
		"InvoiceId",
		"Status",
		"TransactionId",
		"AccountId",
        "CardFirstSix",
        "CardLastFour",
        "CardType",
    	"link",
    	"create_at",
    	"updated_at"
    ];
}
