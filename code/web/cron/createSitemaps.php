<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap_aspen.php';
global $configArray;
global $serverName;
global $interface;

$baseUrl = $configArray['Site']['url'];

require_once ROOT_DIR . '/sys/Module.php';
$module = new Module();
$module->enabled = true;
$module->find();
$addGroupedWorks = false;
while ($module->fetch()) {
	//See if we need to create a sitemap for it
	if ($module->indexName == 'grouped_works') {
		$addGroupedWorks = true;
	}
}

$library = new Library();
$library->find();
while ($library->fetch()){
	if ($addGroupedWorks) {
		$subdomain = $library->subdomain;
		global $solrScope;
		$solrScope = $subdomain;

		//Do a quick search to see how many results we have
		/** @var SearchObject_GroupedWorkSearcher $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject();
		$searchObject->init($searchSource);
		$searchObject->setFieldsToReturn('id');
		$searchObject->setLimit(1);
		$result = $searchObject->processSearch();
		if (!$result instanceof AspenError && empty($result['error'])) {
			$numResults = $searchObject->getResultTotal();
			$lastPage = (int)ceil($numResults / 100);
			$searchObject->setLimit(100);

			$numSitemaps = (int)ceil($numResults / 50000);

			//Now do searches in batch and create the sitemap files
			for ($curSitemap = 1; $curSitemap <= $numSitemaps; $curSitemap++) {
				set_time_limit(300);
				$curSitemapName = 'grouped_work_site_map_' . $subdomain . '_' . str_pad($curSitemap, 3, "0", STR_PAD_LEFT) . '.txt';
				//Store sitemaps in the sitemaps directory
				$sitemapFhnd = fopen(ROOT_DIR . '/sitemaps/' . $curSitemapName, 'w');

				$sitemapStartIndex = ($curSitemap - 1) * 50000;
				$sitemapStartPage = $sitemapStartIndex / 100;
				$sitemapEndIndex = $curSitemap * 50000;
				$sitemapEndPage = $sitemapEndIndex / 100;

				for ($curPage = $sitemapStartPage; $curPage < $sitemapEndPage; $curPage++) {
					$searchObject->setPage($curPage);
					$result = $searchObject->processSearch();
					if (!$result instanceof AspenError && empty($result['error'])) {
						foreach ($result['response']['docs'] as $doc) {
							$url = $baseUrl . '/GroupedWork/' . $doc['id'] . '/Home';
							fwrite($sitemapFhnd, $url . "\r\n");
						}
					}
				}
				fclose($sitemapFhnd);
			}
		}
	}
}
