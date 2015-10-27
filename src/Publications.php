<?php

/**
 * University of Nottingham publication scraper.
 *
 * @author  Martin Porcheron <martin@porcheron.uk>
 * @license MIT
 */

namespace NottPubs;

/**
 * A list of publications for an author. Attempts are made to eliminate duplicate papers.
 */
class Publications extends \ArrayObject implements \JsonSerializable
{
    /**
     * @deprecated
     *  This function has been replaced by \NottPubs\Author::publications($crawl);
     * @throws \BadFunctionCallException
     */
    public function crawl($url)
    {
        throw new \BadFunctionCallException('This function has been removed');
    }

    /**
     * Add an existing \Publication to the list.
     *
     * @param \NottPubs\Publication $pub
     *  Existing \NottPubs\Publication object.
     */
    public function add(\NottPubs\Publication $pub)
    {
        if (!\is_null($pub->doi())) {
            if ($this->offsetExists($pub->doi())) {
                return;
            }

            $key = $pub->doi();
        } else {
            $key = $pub->year() . \str_replace(' ', '', $pub->title());
            if ($this->offsetExists($key)) {
                return;
            }
        }

        $this->offsetSet($key, $pub);
    }

    /**
     * Add a new publication to the list.
     *
     * @param string|null $doi
     *  DOI of the publication.
     * @param int    $year
     *  Year of the publication.
     * @param string $title
     *  Title of the publication.
     * @param string $html
     *  HTML of the publication scraped from the webpage.
     */
    public function addNew($doi, $year, $title, $html)
    {
        if (!\is_null($doi)) {
            if ($this->offsetExists($doi)) {
                return;
            }

            $key = $doi;
        } else {
            $key = $year . \str_replace(' ', '', $title);
            if ($this->offsetExists($key)) {
                return;
            }
        }

        $this->offsetSet($key, new \NottPubs\Publication($doi, $year, $title, $html));
    }

    /**
     * @deprecated
     *  This was renamed to {@code \NottPubs\Publications::appendNew($doi, $year, $title, $html)} for clarity.
     */
    public function addPub($doi, $year, $title, $html)
    {
        $this->addNew($doi, $year, $title, $html);
    }

    /**
     * Merge an existing publications list.
     *
     * @param \NottPubs\Publications $pubs
     *  An existing publications list to merge.
     */
    public function merge(\NottPubs\Publications $pubs)
    {
        foreach ($pubs as $pub) {
            $this->add($pub);
        }
    }

    /**
     * Get a array copy of this list of Publications.
     *
     * @param boolean $numeric
     *  If {@code true}, a numeric array is returned.
     */
    public function getArrayCopy($numeric = true)
    {
        $array = [];

        if ($numeric) {
            foreach ($this as $pub) {
                $array[] = $pub;
            }
        } else {
            foreach ($this as $key => $pub) {
                $array[$key] = $pub;
            }
        }

        return $array;
    }

    /**
     * Prepare this list of Publications for JSON-encoding.
     *
     * @return string
     *  JSON-ready array of publications.
     */
    public function jsonSerialize()
    {
        return $this->getArrayCopy(true);
    }
}
