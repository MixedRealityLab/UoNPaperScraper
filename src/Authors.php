<?php

/**
 * University of Nottingham publication scraper.
 *
 * @author  Martin Porcheron <martin@porcheron.uk>
 * @license MIT
 */

namespace NottPubs;

use Goutte\Client;

/**
 * A list of authors from a school or research group.
 */
class Authors extends \ArrayObject implements \JsonSerializable
{
    private $url;
    private $publications;

    /**
     * Create a list of authors. We can crawl a staff directory
     * and retrieve a list of all staff listed if a URL is provided.
     *
     * @param  string|null $url
     *  URL to the staff directory, set to {@code null} to disable.
     */
    public function __construct($url = null)
    {
        $this->url = $url;
        $this->publications = new \NottPubs\Publications();
        if (!\is_null($url)) {
            $this->crawl($url);
        }
    }

    /**
     * Crawl a staff directory and retrieve a list of all staff listed.
     *
     * @param  string $url
     *  URL to the staff directory
     */
    private function crawl($url)
    {
        $urlPrefix = \str_replace('index.aspx', '', $url);
        $scrapeAuthors = function ($node) use ($urlPrefix) {
            $fullName = $node->text();
            $href = $node->attr('href');

            if (\strpos($href, 'mailto') === 0) {
                return;
            }

            $commaPos = \strpos($fullName, ',');
            $surname = \substr($fullName, 0, $commaPos);
            $otherNames = \substr($fullName, $commaPos+2);

            $this->addAuthor($surname, $otherNames, $urlPrefix . $href);
        };

        try {
            $client = new \Goutte\Client();
            $crawler = $client->request('GET', $url);
            $crawler->filter('.sys_stafflistazsection table tbody td a')->each($scrapeAuthors);
        } catch (\RuntimeException $e) {
            throw new \NottPubs\CrawlException($e->getMessage());
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new \NottPubs\CrawlException($e->getMessage());
        }
    }

    /**
     * Add an author to the list.
     *
     * @param string $surname
     *  Surname of the author.
     * @param string $otherNames
     *  All other names of the author.
     * @param string $url
     *  URL to the author's public eStaffProfile page with publications.
     */
    public function addAuthor($surname, $otherNames, $url)
    {
        $fullName = $otherNames . ' ' . $surname;
        $this->offsetSet($fullName, new \NottPubs\Author($surname, $otherNames, $url));
    }

    /**
     * @deprecated boolean
     *  This was replaced with {@code publications($crawl)} for consistency.
     * @return \NottPubs\Publications A list of publications for all authors in the object.
     */
    public function crawlPublications()
    {
        return $this->publications(true);
    }

    /**
     * Retrieve all authors publication lists.
     *
     * @param boolean
     *  Crawl for a list of publications, if {@code false} returns publications
     *  added already without crawling for new publciations.
     * @throws \NottPubs\CrawlException
     *  Thrown if there is an error during the crawl.
     * @return \NottPubs\Publications A list of publications for all authors in the object.
     */
    public function publications($crawl = false)
    {
        foreach ($this as $author) {
            $this->publications->merge($author->publications($crawl));
        }

        $this->publications->ksort();
        return $this->publications;
    }

    /**
     * Get a array copy of this list of Authors.
     *
     * @param boolean $numeric
     *  If {@code true}, a numeric array is returned.
     */
    public function getArrayCopy($numeric = true)
    {
        $array = [];

        if ($numeric) {
            foreach ($this as $author) {
                $array[] = $author;
            }
        } else {
            foreach ($this as $key => $author) {
                $array[$key] = $author;
            }
        }

        return $array;
    }

    /**
     * Prepare this list of Authors for JSON-encoding.
     *
     * @return string
     *  JSON-ready array of authors.
     */
    public function jsonSerialize()
    {
        return $this->getArrayCopy(true);
    }
}
