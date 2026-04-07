<?php

namespace App\Support;

use Illuminate\Support\Str;
use Sentry\State\HubInterface;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;

class Tracing
{
    protected static ?string $traceId = null;

    protected static ?Transaction $activeTransaction = null;

    public static function getTraceId(): string
    {
        if (! static::$traceId) {
            static::$traceId = (string) Str::uuid();
        }

        return static::$traceId;
    }

    public static function setTraceId(string $id): void
    {
        static::$traceId = $id;

        if (app()->bound(HubInterface::class)) {
            app(HubInterface::class)->configureScope(function ($scope) use ($id) {
                $scope->setTag('trace_id', $id);
            });
        }
    }

    public static function startTransaction(string $name, string $op): ?Transaction
    {
        if (! app()->bound(HubInterface::class)) {
            return null;
        }

        $context = new TransactionContext;
        $context->setName($name);
        $context->setOp($op);

        static::$activeTransaction = app(HubInterface::class)->startTransaction($context);

        return static::$activeTransaction;
    }

    public static function finishTransaction(): void
    {
        if (static::$activeTransaction) {
            static::$activeTransaction->finish();
            static::$activeTransaction = null;
        }
    }

    public static function startSpan(string $op, string $description = ''): ?Span
    {
        if (! static::$activeTransaction) {
            return null;
        }

        $context = new SpanContext;
        $context->setOp($op);
        $context->setDescription($description);

        return static::$activeTransaction->startChild($context);
    }
}
