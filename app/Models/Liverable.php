<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deliverable extends Model
{
    use HasFactory;

    protected $table = 'deliverables';

    protected $fillable = [
        'link',
        'date_submission',
        'status',
        'student_id',
        'brief_id'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function brief()
    {
        return $this->belongsTo(Brief::class);
    }
}
