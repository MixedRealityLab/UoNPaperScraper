<?php

/**
 * University of Nottingham publication scraper.
 *
 * @author  Martin Porcheron <martin@porcheron.uk>
 * @license MIT
 */

namespace NottPubs;

/**
 * Details of an author.
 */
class Author
{
    private $surname;
    private $otherNames;
    private $url;

    /**
     * Construct the model of the author.
     *
     * @param string $surname
     *  Surname of the author.
     * @param string $otherNames
     *  All other names of the author.
     * @param string $url
     *  URL to the author's public eStaffProfile page with publications.
     */
    public function __construct($surname, $otherNames, $url)
    {
        $this->surname = $surname;
        $this->otherNames = $otherNames;
        $this->url = $url;
    }

    /**
     * @return string The author's surname.
     */
    public function surname()
    {
        return $this->surname;
    }

    /**
     * @return string All of the author's other names.
     */
    public function otherNames()
    {
        return $this->otherNames;
    }

    /**
     * @return string URL to the author's public eStaffProfile page with publications.
     */
    public function url()
    {
        return $this->url;
    }
}
