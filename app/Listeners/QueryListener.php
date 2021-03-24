<?php

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Monolog\Logger;

class QueryListener
{

    public $monolog;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->monolog    = new Logger('sql');
        $path_to_log_file = storage_path("logs/" . date('Y-m-d', time()) . "/sql.log");
        $handler          = new \Monolog\Handler\StreamHandler($path_to_log_file, \Monolog\Logger::NOTICE);
        $handler->setFormatter(
            new \Monolog\Formatter\LineFormatter("[%datetime%] log.%level_name% %message% %context% %extra%\n", 'Y-m-d H:i:s.u', true, true)
        );
        $bufferHandler = new \Monolog\Handler\BufferHandler($handler);
        $this->monolog->setHandlers([$bufferHandler]);
    }

    /**
     * Handle the event.
     *
     * @param  QueryExecuted  $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        $sql = str_replace("?", "'%s'", $event->sql);

        try {
            //执行sql日志
            $log = vsprintf($sql, $event->bindings);
        } catch (\Exception $e) {
            $log = json_encode($event->bindings) . $sql;
        }
        if (strpos($sql, " `admin_")) {
            return;
        }
        $this->monolog->notice($log);
    }
}
