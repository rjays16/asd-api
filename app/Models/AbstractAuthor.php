<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbstractAuthor extends Model
{
    use SoftDeletes;

    public $table = 'abstract_authors';

    protected $fillable = [
        'abstract_id',
        'last_name',
        'first_name',
        'city',
        'country',
        'email',
        'institution',
        'department',
        'affiliation_city',
        'affiliation_country',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function abstract() {
        return $this->belongsTo(Abstracts::class, 'abstract_id');
    }
}
