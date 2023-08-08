<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Log;

trait LoggerAnnotationTrait
{
    public string $logName = Log::CALL_CLASS_NAME;

    public string $logGroup = Log::CALL_LOG_CONFIG;

    public function getLogName(): string
    {
        return $this->logName;
    }

    public function getLogGroup(): string
    {
        return $this->logGroup;
    }
}
