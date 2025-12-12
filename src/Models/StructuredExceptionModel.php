<?php
namespace LaravelExceptionAnalyzer\Models;

use Illuminate\Database\Eloquent\Model;

class StructuredExceptionModel extends Model
{
    public $timestamps = true;
    protected $table = 'structured_exception';

    protected $fillable =
        [
            'exception_id',
            'user_id',
            'affected_carrier',
            'is_internal',
            'severity',
            'concrete_error_message',
            'full_readable_error_message',
            'code',
            'file_name',
            'line_number',
            'cfl',
        ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_internal' => 'boolean',
    ];

}
