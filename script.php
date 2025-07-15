<?php

declare(strict_types=1);

require_once 'Scraper.php';

$threadUrls = trim(readline('Enter thread urls (comma separated): '));

(new Scraper())->scrape(explode(',', $threadUrls));
