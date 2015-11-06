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
 * Details of an author.
 */
class Author implements \JsonSerializable
{
    private $surname;
    private $otherNames;
    private $url;
    private $publications;

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
        $this->url($url);
        $this->publications = new \NottPubs\Publications();
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
     * @param string|null $url
     *  Pass a value to replace current URL, leave as {@code null} to just retrieve.
     * @return string URL to the author's public eStaffProfile page with publications.
     */
    public function url($url = null)
    {
        if (!\is_null($url) && !empty($url)) {
            $this->url = $url;
        }

        return $this->url;
    }

    /**
     * Retrieve a list of publications for this author.
     *
     * @param boolean
     *  Crawl for a list of publications, if {@code false} returns publications
     *  added already without crawling for new publciations. URL must have been
     *  provided already.
     * @throws \NottPubs\CrawlException
     *  Thrown if there is an error during the crawl.
     * @return \NottPubs\Publications A list of publications for the author.
     */
    public function publications($crawl = false)
    {
        if ($crawl && !\is_null($this->url)) {
            Log::status('Crawling publication list for ' . $this->otherNames() . ' ' . $this->surname());

            $scrapePubs = function ($node) {
                $html = \trim($node->html());

                $doi = \trim($node->attr('title'));
                if (\is_null($doi) || empty($doi) || $doi === 'No DOI number is available') {
                    $doi = null;
                } else {
                    $doi = \str_replace('http://dx.doi.org/http://dx.doi.org/', '', $doi);
                    $doi = \str_replace('http://dx.doi.org/', '', $doi);
                }

                $year = null;
                $yearClasses = ['.citationArticleYear',
                    '.citationConferenceContributionYear',
                    '.citationConferenceYear',
                    '.citationChapterYear',
                    '.citationAuthoredBookYear'];
                foreach ($yearClasses as $yearClass) {
                    $yObjs = $node->filter($yearClass);
                    if ($yObjs->count() > 0) {
                        $yObj = $yObjs->first();
                        \preg_match('/([0-9]+)/', $yObj->text(), $yearMatches);
                        $year = \intval($yearMatches[0]);
                    }
                }

                $title = null;
                $titleClasses = ['.citationArticleTitle',
                    '.citationConferenceContributionTitle',
                    '.citationChapterDetails',
                    '.citationAuthoredBookTitle'];
                foreach ($titleClasses as $titleClass) {
                    $tObjs = $node->filter($titleClass);
                    if ($tObjs->count() > 0) {
                        $tObj = $tObjs->first();
                        $title = preg_replace("/\r|\n/", '', \trim($tObj->text()));

                        if (\is_null($doi)) {
                            $doi = $tObj->attr('href');
                            if (\is_null($doi) || empty($doi) || $doi === 'No DOI number is available') {
                                $doi = null;
                            } else {
                                $doi = \str_replace('http://dx.doi.org/http://dx.doi.org/', '', $doi);
                                $doi = \str_replace('http://dx.doi.org/', '', $doi);
                            }
                        }


                        break;
                    }
                }

                $this->publications->addNew($doi, $year, $title, $html);
            };

            try {
                $client = new \Goutte\Client();
                $crawler = $client->request('GET', $this->url);
                $crawler->filter('.sys_publicationsListing li')->each($scrapePubs);
            } catch (RuntimeException $e) {
                throw new \NottPubs\CrawlException($e->getMessage());
            }
        }

        return $this->publications;
    }

    /**
     * Prepare this Author for JSON-encoding.
     *
     * @return string
     *  JSON-ready object of author details.
     */
    public function jsonSerialize()
    {
        return [
            'surname' => $this->surname(),
            'otherNames' => $this->otherNames(),
            'url' => $this->url()
        ];
    }
}
