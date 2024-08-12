<?php

namespace wtg\IpCountryDetector\Models;

use Illuminate\Database\Eloquent\Model;

class IpCountry extends Model
{
    protected $table = 'ip_country';

    protected $fillable = [
        'first_ip',
        'last_ip',
        'country',
    ];

    public $timestamps = true;
}
