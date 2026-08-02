<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOfficer extends Model
{
    protected $fillable = ['name', 'role', 'short_description', 'email'];
}
