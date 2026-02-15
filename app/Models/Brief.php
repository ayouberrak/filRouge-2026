<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brief extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date_start',
        'date_end',
        'file',
        'formateur_id'
    ];

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'brief_classroom');
    }

    public function deliverables()
    {
        return $this->hasMany(Deliverable::class);
    }
}
