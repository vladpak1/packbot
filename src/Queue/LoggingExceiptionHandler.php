<?php

namespace PackBot;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Throwable;

class LoggingExceptionHandler implements ExceptionHandler
{
    /** Записываем полную информацию об исключении */
    public function report(Throwable $e): void
    {
        $message = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s\n\n",
            date('c'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        // Убедитесь, что директория /var/log/packbot есть и доступна воркеру
        @file_put_contents('/var/www/www-root/data/www/packbotv2.hloop.me/worker_out.log', $message, FILE_APPEND | LOCK_EX);
    }

    public function shouldReport(Throwable $e): bool
    {
        return true; // мы хотим логировать всё
    }

    public function render($request, Throwable $e)
    {
        // не нужно
    }

    public function renderForConsole($output, Throwable $e)
    {
        // на случай запуска воркера в консоли
        $output->writeln($e->__toString());
    }
}
