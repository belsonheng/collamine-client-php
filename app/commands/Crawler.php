<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use GuzzleHttp\Exception\RequestException;

class Crawler extends Command {

	const COLLAMINE_DOWNLOAD_URL = 'http://172.20.131.150:9001/download/html/';
	const COLLAMINE_UPLOAD_URL = 'http://172.20.131.150:9001/upload/html/multipart/';
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'crawl';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Crawl a website given the URL';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Execute the console command.
	 *
	 * @return void
	 */

	public function fire() {

		$url = $this->argument('url');
		$pattern = $this->option('pattern');

		$domain = parse_url($url, PHP_URL_HOST);		
		$now = Carbon::now(new DateTimeZone("Asia/Singapore"));	

		if ($pattern && !preg_match($pattern, $url)) {
			$this->info('Sorry, ' . $url . ' does not match the pattern: ' . $pattern . '\n');
			return;
		}
		
		// Add URL to the list of URLs to search. 
		$queue = array();
		array_push($queue, $url);

		// Array to store URLs that have been searched
		$visited_urls = array();

        $i = 0;

		// Take URL from the queue array
		while (!empty($queue)) {
			$queue_url = $queue[$i];
			// If the URL has not been visited, it must not be in the $visited_urls array
			if (!in_array($queue_url, $visited_urls)) {
				// Add the URL to searched_links array
				array_push($visited_urls, $queue_url);

				// Begin crawling
				$this->info("\nBegin crawling: " . $queue_url);
				$client = new Goutte\Client();

				$collamine_url = urlencode($queue_url);

				// Try downloading from Collamine servers
				$response = $client->request('GET', $this::COLLAMINE_DOWNLOAD_URL . $collamine_url);
				$status_code = $client->getResponse()->getStatus();
				$content = $client->getResponse()->getContent();

				// If the client cannot connect to Collamine servers or response is 'not found'
				if ($status_code !== 200 || $content == 'not found') {
					// Get the content from original website
					$response = $client->request('GET', $queue_url);
					$content = $client->getResponse()->getContent();
					$source = 'original';
				}	
				else
					$source = 'collamine';
				
				$this->comment('fetched from ' . $source);

				// Insert new record only if it does not exists before
				if (Document::where('url', '=', $queue_url)->count() > 0)
					$this->comment('exists');
				else {
					Document::create(array('url' => $queue_url,
						                   'domain' => $domain,
					                       'source' => $source,
					                       'content' => base64_encode($response->html()),
					                       'crawled_date' => $now));
					$this->comment('saved');
				}
		
				// Upload content to Collamine server if source is original
				if ($source == 'original') {
					$mem_size = 10 * 1024 * 1024;
					$file = fopen("php://temp/maxmemory:$mem_size", 'r+');
					fputs($file, $content);
					rewind($file);

					$name = substr($queue_url, strrpos($queue_url, '/') + 1);
					$filename = "/tmp/" . $name;
		
					$this->info('Uploading to Collamine: ' . $queue_url);
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $this::COLLAMINE_UPLOAD_URL);
					curl_setopt($ch, CURLOPT_HEADER, array('Content-Type' => 'multipart/form-data'));
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($ch, CURLOPT_POST, true); // Set method to POST
					curl_setopt($ch, CURLOPT_POSTFIELDS, array('domain' => $domain, 'url' => $queue_url, 'crawltime' => time(), 'contributor' => 'belson', 'document' => curl_file_create($filename, 'text/html', $filename)));
					curl_exec($ch);
					curl_close($ch);
				}

				// Get all the links from current page & assign to $urls
				$urls = $response->filter('a')->each(function (Symfony\Component\DomCrawler\Crawler $node, $i) {
					return $node->link()->getUri();
				});

				foreach ($urls as $url) {
					// remove named anchors
					if (strpos($url, "#"))
						$url = substr($url, 0, strpos($url, "#"));
					// remove queries
					if (strpos($url, "?"))
						$url = substr($url, 0, strpos($url, "?"));
					// ignore if external url
					$link = parse_url($url);
					if (empty($link['host']) || $link['host'] !== $domain || $link['scheme'] !== 'http')
						continue;
					// ignore if url is not the ones we are interested in
					if ($pattern && !preg_match($pattern, $url))
						continue;
					// ignore if url has been visited or already in queue
					if (in_array($url, $visited_urls) || in_array($url, $queue))
						continue;
					// queue url
					array_push($queue, $url);
				}
			}
			unset($queue[$i]);
			$i++;
  		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('url', InputArgument::REQUIRED, 'A starting URL to crawl.'),
			//array('pattern', InputArgument::OPTIONAL, 'A regex pattern for URL to match.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('pattern', null, InputOption::VALUE_OPTIONAL, 'A regex pattern for URL to match.', null),
		);
	}





}