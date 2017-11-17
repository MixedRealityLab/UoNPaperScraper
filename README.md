<h1 align="center">
	University of Nottingham<br />
	Paper Scraper
</h1>

A utility to scrape a collection of staff profile pages from the University of Nottingham to allow for collation of publication lists.

## Setup

Download the latest version of the repository (no 'releases' are planned). You will need a local copy of [Composer](http://getcomposer.org) in the root of the downloaded repository. Run the command `php composer.phar install`. Beyond this, look at how to use the code by checking out the `example.php` file.

## Examples
Below are a number of separate examples. See `example.php` for example scraping of an entire website and converting the output into HTML, including grouping publications into five-year batches.

### Author List
To extract an [iterable list](http://php.net/manual/en/class.arrayobject.php) of authors:
	
	$url = 'URL TO PEOPLE/STAFF DIRECTORY';
	$authors = new \Porcheron\UonPaperScraper\Authors($url); // specifying a URL causes a crawl to occur

	foreach ($authors as &$author) {
		// each author object has surname(), otherNames(), and
		// url() methods to return their respective content
	}

This list can also be converted to a JSON list using the standard PHP `json_encode` function:

	\json_encode($authors);

### A Staff Member's Publication List
To retrieve the list of publications for a single staff member:

	$surname = 'SURNAME';
	$otherNames = 'OTHER NAMES';
	$url = 'URL TO STAFF MEMBER'S PUBLIC ESTAFFPROFILE PAGE';
	$author = new \Porcheron\UonPaperScraper\Author($surname, $otherNames, $url);

	$publications = $author->publications(true); // true causes a crawl to occur

Again, this `$publications` object is iterable, and can also be converted to a JSON string:

	foreach ($publications as &$publication) {
		// each publication object has doi(), title(), year() and 
		// html() functions to return their respective content  
	}

	\json_encode($publications);

### All Staff Members' Publication Lists as One List
To retrieve a combined list of publications from all staff, use:

	$url = 'URL TO PEOPLE/STAFF DIRECTORY';
	$authors = new \Porcheron\UonPaperScraper\Authors($url);
	$publications = $authors->publications(true);

