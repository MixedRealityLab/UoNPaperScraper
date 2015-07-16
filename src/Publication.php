<?php

/**
 * University of Nottingham publication scraper.
 * 
 * @author Martin Porcheron <martin@porcheron.uk>
 * @license MIT
 */

namespace NottPubs;

/**
 * Details of a publication.
 */
class Publication
{
    private $doi;
    private $year;
    private $title;
    private $html;

    /**
     * Construct the model of a publication.
     * 
     * @param string $doi
     *  DOI of the publication.
     * @param int $year
     *  Year of the publication.
     * @param string $title
     *  Title of the publication.
     * @param string $html
     *  HTML of the publication scraped from the webpage.
     */
    public function __construct($doi, $year, $title, $html)
    {
        $this->doi = $doi;
        $this->year = $year;
        $this->title = $title;
        $this->html = $html;
    }

    /**
     * @return int DOI of the publication.
     */
    public function doi()
    {
        return $this->doi;
    }

    /**
     * @return int Year of the publication.
     */
    public function year()
    {
        return $this->year;
    }

    /**
     * @return string Title of the publication.
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * @return string HTML of the publication scraped from the webpage.
     */
    public function html()
    {
        return $this->html;
    }

}