<?php

// src/Queue/NullExceptionHandler.php

namespace PackBot;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Throwable;

/**
 * Простая реализация ExceptionHandler для очереди,
 * чтобы Worker мог обрабатывать исключения.
 */
class NullExceptionHandler implements ExceptionHandler
{
    /** Не логируем */
    public function report(Throwable $e): void
    {
        error_log(print_r($e, true));
    }

    /** Всегда пропускаем */
    public function shouldReport(Throwable $e): bool
    {
        return false;
    }

    /** Не отображаем HTTP-рендер */
    public function render($request, Throwable $e)
    {
        // noop
    }

    /** Не отображаем консольный рендер
     */
    public function renderForConsole($output, Throwable $e)
    {
        // noop
    }
}
