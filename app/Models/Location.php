<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'location_name',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
