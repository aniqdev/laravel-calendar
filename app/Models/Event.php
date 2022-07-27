<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'summary',
        'description',
        'location',
        'start',
        'end',
        'link',
        'status',
        'event_id',
    ];
}
