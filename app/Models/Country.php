<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
	public $table = 'countries';

	public $fillable = [
		'name',
	];

	protected $hidden = [
        'created_at',
        'updated_at',
    ];
}