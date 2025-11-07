<?php

namespace ACPT\Core\CQRS\Command;

interface LogFormatInterface
{
    /**
     * @return array
     */
    public function logFormat(): array;
}