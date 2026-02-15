<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'duration',
        'status',
        'justification_file'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
