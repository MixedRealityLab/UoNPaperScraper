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
 * A list of publications for an author.
 */
class Publications extends \ArrayObject
{
    /**
     * Crawl a public eStaffProfile page and retrieve a list of all publications
     *
     * @param  string $url
     *  URL to the author's eStaffProfile page
     * @return Publications $this
     */
    public function crawl($url)
    {
        $scrapePubs = function ($node) {
            $html = $node->html();

            $doi = $node->attr('title');
            if (\is_null($doi) || empty($doi) || $doi === 'No DOI number is available') {
                $doi = null;
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
                    $year = $yearMatches[0];
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
                    $title = $tObj->text();

                    if (\is_null($doi)) {
                        $doi = $tObj->attr('href');
                        if (\is_null($doi) || empty($doi) || $doi === 'No DOI number is available') {
                            $doi = null;
                        } else {
                            $doi = \str_replace('http://dx.doi.org/', '', $doi);
                        }
                    }


                    break;
                }
            }

            $this->addPub($doi, $year, $title, $html);
        };

        $client = new Client();
        $crawler = $client->request('GET', $url);
        $crawler->filter('.sys_publicationsListing li')->each($scrapePubs);

        return $this;
    }

    /**
     * Add a publication to the list.
     *
     * @param string $doi
     *  DOI of the publication.
     * @param int    $year
     *  Year of the publication.
     * @param string $title
     *  Title of the publication.
     * @param string $html
     *  HTML of the publication scraped from the webpage.
     */
    public function addPub($doi, $year, $title, $html)
    {
        $this->append(new Publication($doi, $year, $title, $html));
    }
}
