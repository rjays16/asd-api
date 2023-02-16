<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;
use App\Mail\AbstractSubmission\ThankYou;
use Exception;

class Abstracts extends Model
{
    use SoftDeletes;

    public $table = 'abstracts';

    protected $fillable = [
        'convention_member_id',
        'title',
        'structured_abstract',
        'keywords',
        'conflict_interest',
        'commercial_funding',
        'submission_date',
        'abstract_category',
        'abstract_type',
    ];

    protected $casts = [
		'submission_date' => 'date:Y-m-d',
    ];

    public function member() {
        return $this->belongsTo(ConventionMember::class, 'convention_member_id');
    }

    public function authors() {
        return $this->hasMany(AbstractAuthor::class, 'abstract_id');
    }

    public static function sendThankYouEmail($user, $abstract_submission) {
        try {
            Mail::mailer('info_smtp')->to($user->email)->send(new ThankYou($user, $abstract_submission));
            return 200;
        } catch(Exception $e) {
            throw $e;
        }
    }
}
