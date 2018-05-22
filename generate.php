<?php

/**
 * File to generate publication lists with Contensis-compatible HTML
 *
 * @author Martin Porcheron <martin-uonpaperscraper@porcheron.uk>
 * @license MIT
 */

require 'vendor/autoload.php';

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Timezone (PHP requirement)
\date_default_timezone_set('Europe/London');

// Research Group eStaffProfile directory
\define('URL_ESP', 'http://www.nottingham.ac.uk/research/groups/mixedrealitylab/people/index.aspx');

// Sleep time between publication scraping requests; if 0, you may crash the
// publications list appliance for the University website
\define('CRAWL_SLEEP', 5);

// Page title
\define('STR_TITLE', 'Publications');

// String for when no DOI is available
\define('STR_NO_DOI', 'No DOI number is available');

// First year to group publications from
\define('GRP_ST', 1995);

// Last year to group publications to
\define('GRP_END', 2020);

// How many years appear in each group
\define('GRP_INC', 5);

// Home/root/latest publications page
\define('PATH_ROOT', '/research/groups/mixedrealitylab/publications/latest.aspx');

// Path for where to save publications by year (%s = year)
\define('PATH_YR', 'build/year/%s.html');

// Path for where to save publications by group (%s = last year, %s = first year)
\define('PATH_GRP', 'build/group/');

// Filename for where to save publications by group (%s = last year, %s = first year)
\define('PATH_GRP_FILE', '%s-%s.html');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Logging level
\Porcheron\UonPaperScraper\Log::setLevel(\Porcheron\UonPaperScraper\Log::LOG_VERBOSE);

// Fetch all publications for all staff
$authors = new \Porcheron\UonPaperScraper\Authors(URL_ESP);
$pubs = $authors->publications(true, CRAWL_SLEEP);

if (empty($pubs)) {
    die('No publications');
}

// Collate publications by year
$pubsByYear = [];
foreach ($pubs as &$pub) {
    $year = $pub->year();
    if (empty($year)) {
        continue;
    }

    $doi = $pub->doi();
    if (\is_null($doi)) {
        $doi = STR_NO_DOI;
    }

    if (!isset($pubsByYear[$year])) {
        $pubsByYear[$year] = [];
    }

    $cssClass = (count($pubsByYear[$year]) % 2) === 0 ? 'sys_alt' : '';

    $html = \sprintf('<li title="%s" class="%s">', $doi, $cssClass);
    $html .= $pub->html();
    $html .= '</li>';

    $pubsByYear[$year][] = $html;
}
unset($pub);

// Create seperate files for each year
foreach ($pubsByYear as $year => $pubs) {
    $file = \sprintf(PATH_YR, $year);

    $html = '<div id="lookup-publications" class="sys_profilePad ui-tabs-panel ui-widget-content ui-corner-bottom">';
    $html .= '<ul class="sys_publicationsListing">';
    $html .= \implode('', $pubsByYear[$year]);
    $html .= '</ul></div>';

    @\mkdir(\dirname($file), 0777, true);
    \file_put_contents($file, $html);
}

// Create pages for groups for the website to reduce the total number of pages
$years = \range(GRP_ST, GRP_END, GRP_INC);
$numYears = \count($years) - 1;
for ($i = 0; $i < $numYears; $i++) {
    $firstYear = $years[$i];
    $lastYear = $years[$i+1]-1;

    $html = '';
    for ($year = $lastYear; $year >= $firstYear; $year--) {
        $file = \sprintf(PATH_YR, $year);

        if (\is_file($file)) {
            $html .= '<h2 class="headingBackground"><a id="Year'. $year .'" class="CMS-Anchor" href="#contensis" data-cms="{\'anchor\':\'Year'. $year .'\'}">'. $year .'</a></h2>';
            $html .= \file_get_contents($file);
        }
    }

    if (empty($html)) {
        continue;
    }

    $title = \sprintf('<h1>%s</h1>', STR_TITLE);
    $file = \sprintf(PATH_GRP . PATH_GRP_FILE, $lastYear, $firstYear);

    if ($lastYear == GRP_END-1) {
        $file = PATH_GRP . 'latest.html';
    } else {
        $title = '<a title="'. STR_TITLE .'" href="#contensis" data-cms="{\'contentId\':8925030,\'contentTypeId\':16}">'. STR_TITLE .'</a> &raquo; '. $lastYear .' &mdash; '. $firstYear .'</h1>';
    }

    $html = $title . $html;
    @\mkdir(\dirname($file), 0777, true);
    \file_put_contents($file, $html);
}
