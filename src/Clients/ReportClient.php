<?php

namespace NikolajVE\LaravelExceptionAnalyzer\Facades;

class ReportClient
{
    public static function report(\Throwable $exception): void
    {
        // Save exception to database
    }
}
