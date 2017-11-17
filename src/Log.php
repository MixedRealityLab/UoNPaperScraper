<?php

/**
 * University of Nottingham publication scraper.
 *
 * @author  Martin Porcheron <martin-uonpaperscraper@porcheron.uk>
 * @license MIT
 */

namespace Porcheron\UonPaperScraper;

/**
 * Basic logging functionality. No need for complexity with this.
 */
class Log
{
    /** @const int No logging output */
    const LOG_NONE = 0;

    /** @const int Debug logging output */
    const LOG_DEBUG = 1;

    /** @const int Verbose logging output */
    const LOG_VERBOSE = 256;

    /** @var int Logging level to use */
    private static $level = self::LOG_NONE;

    /**
     * Set the logging level.
     *
     * @param int
     *  New logging level.
     */
    public static function setLevel($level) {
        self::$level = $level;
    }

    /**
     * Print a debug message.
     *
     * @param string
     *  Message to print out.
     */
    public static function debug($str) {
        if (self::$level >= self::LOG_DEBUG) {
            echo $str . "\n";
            \flush();
        }
    }

    /**
     * Print a verbose message or a period, if verbose logging is disabled.
     *
     * @param string
     *  Message to print out.
     */
    public static function status($str) {
        if (self::$level >= self::LOG_VERBOSE) {
            self::verbose($str);
        } else if (self::$level > self::LOG_NONE) {
            echo ".";
            \flush();
        }
    }

    /**
     * Print a verbose message.
     *
     * @param string
     *  Message to print out.
     */
    public static function verbose($str) {
        if (self::$level >= self::LOG_VERBOSE) {
            echo "\t" . $str . "\n";
            \flush();
        }
    }

}