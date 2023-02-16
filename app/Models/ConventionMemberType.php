<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConventionMemberType extends Model
{
    public $table = "convention_member_types";

    protected $fillable = [
        'name'
    ];

    public function member() {
        return $this->hasOne(Fee::class, 'member_type');
    }
}
