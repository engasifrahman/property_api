<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'bill_country_code',
        'description',
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'latitude',
        'longitude',
        'google_place_id',
        'city',
        'state',
        'country',
        'zip_code',
        'star_rating',
        'property_type',
        'is_active',
        'is_deleted',
    ];
}
