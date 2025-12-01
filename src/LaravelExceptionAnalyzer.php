<?php

namespace NikolajVE\LaravelExceptionAnalyzer;

use NikolajVE\LaravelExceptionAnalyzer\Clients\ReportClient;
use Throwable;
class LaravelExceptionAnalyzer {
    public function __construct(
        private readonly ReportClient $reportClient,
    ) {}

    public function report(Throwable $exception): void
    {
        $this->reportClient->report($exception);
    }


}
