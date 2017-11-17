<?php

/**
 * University of Nottingham publication scraper.
 *
 * @author  Martin Porcheron <martin@porcheron.uk>
 * @license MIT
 */

namespace Porcheron\UonPaperScraper;

/**
 * Error during crawling.
 */
class CrawlException extends Exception
{

    /**
     * Throw the CrawlException
     *
     * @param string $message
     *  Details of the crawl error.
     */
    public function __construct($message)
    {
        super::__construct('Error during crawl: ' . $message);
    }
}
