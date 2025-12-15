<?php
namespace LaravelExceptionAnalyzer\Models;

use Illuminate\Database\Eloquent\Model;

class RepetitiveExceptionModel extends Model
{
    public $timestamps = true;
    protected $table = 'repetitive_exceptions';

    protected $fillable =
        [
            'cfl',
            'is_solved',
            'short_error_message',
            'detailed_error_message',
            'occurrence_count',
            'is_internal',
            'severity',
            'carrier',
        ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_internal' => 'boolean',
        'is_solved' => 'boolean',
    ];

}
