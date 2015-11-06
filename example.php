<?php

/**
 * Example usage file.
 *
 * @author Martin Porcheron <martin@porcheron.uk>
 * @license MIT
 */

require 'vendor/autoload.php';

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Timezone (PHP requirement)
\date_default_timezone_set('Europe/London');

// Print progress?
\define('PROGRESS', true);

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
\define('GRP_ST', 1990);

// Last year to group publications to
\define('GRP_END', 2020);

// How many years appear in each group
\define('GRP_INC', 5);

// Path for where to save publications by year (%s = year)
\define('PATH_YR', 'build/year/%s.html');

// Path for where to save publications by group (%s = last year, %s = first year)
\define('PATH_GRP', 'build/group/%s-%s.html');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Logging level
\NottPubs\Log::setLevel(\NottPubs\Log::LOG_NONE);

// Fetch all publications for all staff
$authors = new \NottPubs\Authors(URL_ESP);
$pubs = $authors->publications(true, CRAWL_SLEEP);

if (empty($pubs)) {
    die('No publications');
}

// Collate publications by year
$pubsByYear = [];
foreach ($pubs as $pub) {
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

// Create seperate files for each year
foreach ($pubsByYear as $year => $pubs) {
    $file = \sprintf(PATH_YR, $year);

    $html = '<div id="lookup-publications" class="sys_profilePad ui-tabs-panel ui-widget-content ui-corner-bottom">';
    $html .= '<ul class="sys_publicationsListing">';
    $html .= \implode('', $pubsByYear[$year]);
    $html .= '</ul></div>';

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
            $html .= '<h2 class="headingBackground">'. $year .'</h2>';
            $html .= \file_get_contents($file);
        }
    }

    if (empty($html)) {
        continue;
    }

    $html = \sprintf('<h1>%s</h1>%s', STR_TITLE, $html);
    $file = \sprintf(PATH_GRP, $lastYear, $firstYear);
    \file_put_contents($file, $html);
}
