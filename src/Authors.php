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
class Authors extends \ArrayObject
{
    /**
     * Crawl a staff directory and retrieve a list of all staff listed
     *
     * @param  string $url
     *  URL to the staff directory
     * @return Authors $this
     */
    public function crawl($url)
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

        $client = new Client();
        $crawler = $client->request('GET', $url);
        $crawler->filter('.sys_stafflistazsection table tbody td a')->each($scrapeAuthors);

        return $this;
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
        $this->offsetSet($fullName, new Author($surname, $otherNames, $url));
    }

    /**
     * @return Publication[] A list of publications for all authors in the object.
     */
    public function crawlPublications()
    {
        $publications = [];
        $i = 5;

        foreach ($this as $author) {
            $pubs = new Publications();

            try {
                $pubs->crawl($author->url());
                foreach ($pubs as $pub) {
                    $year = $pub->year();
                    $title = \str_replace(' ', '', $pub->title());
                    $publications[$year. $title] = $pub;
                }
            } catch (RuntimeException $e) {
                die('Issue scraping - is the website working?');
            }
        }

        \krsort($publications);
        return $publications;
    }
}
