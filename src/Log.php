<?php declare(strict_types=1);

namespace Tetraquark;

class Log
{
    static protected int     $indent        = 0;
    static protected int     $verbose       = 0;
    static protected int     $maxVerbose    = 0;
    static protected int     $classLimit    = 20;
    static protected int     $functionLimit = 20;
    static protected ?string $timeStart     = null;
    static protected bool    $addClass      = true;
    static protected bool    $addFunction   = true;
    static protected string  $oldFunction   = '';

    function __construct()
    {
    }

    static public function timeStart(): void
    {
        self::$timeStart = microtime();
    }

    static public function timeEnd(): void
    {
        $timeEnd = self::getMilliseconds(microtime());

        if (\is_null(self::$timeStart)) {
            throw new Exception('Time start is null', 400);
        }

        $timeStart = self::getMilliseconds(self::$timeStart);
        $time = $timeEnd - $timeStart;
        self::log('Duration: ' . ($time/1000) . 's', null, 1);
    }

    static private function getMilliseconds(string $microtime) {
        $mt = explode(' ', $microtime);
        return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
    }

    static public function time(): void
    {
        list($usec, $sec) = explode(" ", microtime());
        self::log(date('H:i:s:') . round($usec * 1000), 0 , 1);
    }

    static public function log(string $output, ?int $verbose = null, int $traceLvl = 0): void
    {
        if (is_null($verbose)) {
            $verbose = self::$verbose;
        }

        if ($verbose <= self::$maxVerbose) {
            $message = str_repeat("  ", self::$indent) . $output;
            $classStr = '';
            $function = '';
            if (self::$addClass) {
                $debug = debug_backtrace()[1 + $traceLvl];
                $class = self::fitString($debug['class'], self::$classLimit) . ' | ';
            }
            if (self::$addFunction) {
                if (!isset($debug)) {
                    $debug = debug_backtrace()[1 + $traceLvl];
                }
                $function = self::fitString($debug['function'], self::$functionLimit) . ' | ';

            }
            echo $class . $function . str_replace("\r", '\r', str_replace("\n", '\n', $message))   . PHP_EOL;
        }
    }

    static protected function fitString(string $string, int $size): string
    {
        $slimed = '';
        if (strlen($string) > $size) {
            $slimed = substr($string, 0, (int) (floor($size/2) - 1)) . '..';
            $slimed .= substr($string, (int) -ceil($size/2) + 1);
        } elseif (strlen($string) < $size) {
            $slimed = $string . str_repeat(' ',  $size - strlen($string));
        }
        return $slimed;
    }

    static public function getIndent(): int
    {
        return self::$indent;
    }

    static public function setIndent(int $indent): void
    {
        self::$indent = $indent;
    }

    static public function increaseIndent(): void
    {
        self::$indent++;
    }

    static public function decreaseIndent(): void
    {
        self::$indent--;
    }

    static public function setVerboseLevel(int $level): void
    {
        self::$verbose = $level;
    }
}
