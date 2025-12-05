<?php
namespace LaravelExceptionAnalyzer\Models;

use Illuminate\Database\Eloquent\Model;

class ExceptionModel extends Model
{
    public $timestamps = false;
    protected $table = 'exceptions';

    protected $fillable =
        [
            'message',
            'type',
            'code',
            'file',
            'line',
            'url',
            'hostname',
            'stack_trace',
            'user_id',
            'user_email',
            'session_id',
            'level',
            'created_at',
        ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

}
