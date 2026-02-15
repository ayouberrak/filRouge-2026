<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'formateur_id'
    ];

    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function squads()
    {
        return $this->hasMany(Squad::class);
    }

    public function students()
    {
        return $this->hasMany(User::class);
    }

    public function briefs()
    {
        return $this->belongsToMany(Brief::class, 'brief_classroom');
    }
}
