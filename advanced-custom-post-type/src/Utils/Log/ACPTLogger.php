<?php

namespace ACPT\Utils\Log;

class ACPTLogger
{
    const STATUS_INFO = 'INFO';
    const STATUS_NOTICE = 'NOTICE';
    const STATUS_DEBUG   = 'DEBUG';
    const STATUS_WARNING = 'WARNING';
    const STATUS_ERROR   = 'ERROR';
    const STATUS_FATAL = 'FATAL';

    /**
     * $log_file - path and log file name
     * @var string
     */
    protected static $log_file;

    /**
     * $file - file
     * @var resource
     */
    protected static $file;

    /**
     * $options - settable options
     * @var array $dateFormat of the format used for the log.json file; $logFormat used for the time of a single log event
     */
    protected static array $options = [
        'dateFormat' => 'Y-m-d',
        'logFormat' => 'Y-m-d H:i:s'
    ];

    private static $instance;

    /**
     * Create the log file
     *
     * @throws \Exception
     */
    public static function createLogFile()
    {
        $time = date(static::$options['dateFormat']);
        $logsDir = WP_CONTENT_DIR . "/acpt-logs";
        static::$log_file =  $logsDir . "/log-{$time}.json";

        //Check if directory /logs exists
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0777, true);
        }

        //Create log file if it doesn't exist.
        if (!file_exists(static::$log_file)) {
            fopen(static::$log_file, 'w') or exit("Can't create {static::log_file}!");
        }

        //Check permissions of file.
        if (!is_writable(static::$log_file)) {
            //throw exception if not writable
            throw new \Exception("ERROR: Unable to write to file!", 1);
        }
    }

    /**
     * Set logging options (optional)
     * @param array $options Array of settable options
     *
     * Options:
     *  [
     *      'dateFormat' => 'value of the date format the .json file should be saved int'
     *      'logFormat' => 'value of the date format each log event should be saved int'
     *  ]
     */
    public static function setOptions($options = [])
    {
        static::$options = array_merge(static::$options, $options);
    }

    /**
     * Info method (write info message)
     *
     * Used for e.g.: "The user example123 has created a post".
     *
     * @param mixed $message Descriptive text of the debug
     * @param string $context Array to expend the message's meaning
     *
     * @return void
     * @throws \Exception
     */
    public static function info($message, array $context = [])
    {
        static::writeLog(self::format($message, self::STATUS_INFO, $context));
    }

    /**
     * Notice method (write notice message)
     *
     * Used for e.g.: "The user example123 has created a post".
     *
     * @param mixed $message Descriptive text of the debug
     * @param string $context Array to expend the message's meaning
     *
     * @return void
     * @throws \Exception
     */
    public static function notice($message, array $context = [])
    {
        static::writeLog(self::format($message, self::STATUS_NOTICE, $context));
    }

    /**
     * Debug method (write debug message)
     *
     * Used for debugging, could be used instead of echo'ing values
     *
     * @param mixed $message Descriptive text of the debug
     * @param string $context Array to expend the message's meaning
     *
     * @return void
     * @throws \Exception
     */
    public static function debug($message, array $context = [])
    {
        static::writeLog(self::format($message, self::STATUS_DEBUG, $context));
    }

    /**
     * Warning method (write warning message)
     *
     * Used for warnings which is not fatal to the current operation
     *
     * @param mixed $message Descriptive text of the warning
     * @param string $context Array to expend the message's meaning
     *
     * @return void
     * @throws \Exception
     */
    public static function warning($message, array $context = [])
    {
        static::writeLog(self::format($message, self::STATUS_WARNING, $context));
    }

    /**
     * Error method (write error message)
     *
     * Used for e.g. file not found,...
     *
     * @param mixed $message Descriptive text of the error
     * @param string $context Array to expend the message's meaning
     *
     * @return void
     * @throws \Exception
     */
    public static function error($message, array $context = [])
    {
        static::writeLog(self::format($message, self::STATUS_ERROR, $context));
    }

    /**
     * Fatal method (write fatal message)
     *
     * Used for e.g. database unavailable, system shutdown
     *
     * @param mixed $message Descriptive text of the error
     * @param string $context Array to expend the message's meaning
     *
     * @return void
     * @throws \Exception
     */
    public static function fatal($message, array $context = [])
    {
        static::writeLog(self::format($message, self::STATUS_FATAL, $context));
    }

    /**
     * @param       $message
     * @param       $status
     * @param $
     * @param array $context
     *
     * @return array
     */
    private static function format($message, $status, array $context = [])
    {
        $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        return [
            'message' => $message,
            'bt' => $bt,
            'severity' => $status,
            'context' => $context
        ];
    }

    /**
     * Write to log file
     *
     * @param array $args Array of message (for log file), line (of log method execution), severity (for log file) and displayMessage (to display on frontend for the used)
     *
     * @return void
     * @throws \Exception
     */
    public static function writeLog($args = [])
    {
        //Create the log file
        static::createLogFile();

        // open log file
        if (!is_resource(static::$file)) {
            static::openLog();
        }

        //Grab time - based on the time format
        $time = date(static::$options['logFormat']);

        // Convert context to json
        $context = json_encode($args['context']);

        $caller = array_shift($args['bt']);
        $btLine = $caller['line'];
        $btPath = $caller['file'];

        // Convert absolute path to relative path (using UNIX directory seperators)
        $path = static::absToRelPath($btPath);

        // Create log variable = value pairs
        $timeLog = is_null($time) ? "[N/A] " : "{$time}";
        $severityLog = is_null($args['severity']) ? "[N/A]" : "{$args['severity']}";
        $messageLog = is_null($args['message']) ? "N/A" : $args['message'];

        $event = [
            'date' => $timeLog,
            'severity' => $severityLog,
            'path' => $path,
            'message' => $messageLog,
        ];

        if(!empty($args['context'])){
            $contextLog = empty($args['context']) ? "" : "{$context}";
            $event['context'] = $contextLog;
        }

        $handle = self::$file;

        // seek to the end
        fseek($handle, 0, SEEK_END);

        // are we at the end of is the file empty
        if (ftell($handle) > 0)
        {
            // move back a byte
            fseek($handle, -1, SEEK_END);

            // remove last ]
            $stat = fstat($handle);
            ftruncate($handle, $stat['size']-1);

            // add the trailing comma
            fwrite($handle, ',', 1);

            // add the new json string
            fwrite($handle, json_encode($event) . ']');
        } else {
            // write the first event inside an array
            fwrite($handle, "[" . json_encode($event) . "]");
        }

//        // Append event to JSON file
//        $logContent = file_get_contents(static::$log_file);
//
//        $json = json_decode($logContent);
//        $json[] = $event;
//
//        $logContent = json_encode($json);
//
//        file_put_contents(static::$log_file, $logContent);

        // Close file stream
        static::closeFile();
    }

    /**
     * Open log file
     * @return void
     */
    private static function openLog()
    {
        $openFile = static::$log_file;
        // 'a' option = place pointer at end of file
        static::$file = fopen($openFile, 'a') or exit("Can't open $openFile!");
    }

    /**
     *  Close file stream
     */
    public static function closeFile()
    {
        if (static::$file) {
            fclose(static::$file);
        }
    }

    /**
     * Convert absolute path to relative url (using UNIX directory seperators)
     *
     * E.g.:
     *      Input:      D:\development\htdocs\public\todo-list\index.php
     *      Output:     localhost/todo-list/index.php
     *
     * @param string Absolute directory/path of file which should be converted to a relative (url) path
     * @return string Relative path
     */
    public static function absToRelPath($pathToConvert)
    {
        $pathAbs = str_replace(['/', '\\'], '/', $pathToConvert);
        $documentRoot = str_replace(['/', '\\'], '/', $_SERVER['DOCUMENT_ROOT']);
        return ($_SERVER['SERVER_NAME'] ?? 'cli') . str_replace($documentRoot, '', $pathAbs);
    }

    /**
     * The Singleton's constructor should always be private to prevent direct
     * construction calls with the `new` operator.
     */
    protected function __construct()
    { }

    /**
     * Singletons should not be cloneable.
     */
    protected function __clone()
    { }

    /**
     * Singletons should not be restorable from strings.
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * The Singleton's constructor should always be private to prevent direct
     * construction calls with the `new` operator.
     */
    private function __destruct()
    {}
}