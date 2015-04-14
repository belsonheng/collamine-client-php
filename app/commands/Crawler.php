<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Crawler extends Command {

	const COLLAMINE_DOWNLOAD_URL = 'http://127.0.0.1:9001/download/html/';
	const COLLAMINE_UPLOAD_URL = 'http://127.0.0.1:9001/upload/html/multipart/';

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
	public function fire()
	{
          // php artisan crawl http://forums.hardwarezone.com.sg/hwm-publishing-magazine-210
          $url = $this->argument('url');
          $domain = parse_url($url, PHP_URL_HOST);

          $this->info('Begin crawling: ' . $url);
          $client = new Goutte\Client();

          // try downloading from Collamine servers
          $response = $client->request('GET', $this::COLLAMINE_DOWNLOAD_URL . $url);
          $status_code = $client->getResponse()->getStatus();

          // if the client cannot connect to Collamine servers or response is 'not found'
          if ($status_code !== 200 || $response->text() == 'not found') {
              // get the content from original website
              $response = $client->request('GET', $url);
	      }

          // get all the links from the current page
          $links = $response->filter('a')->each(function (Symfony\Component\DomCrawler\Crawler $node, $i) {
              return $node->link()->getUri();
          });

          // echo "=== External Links =================\n";
	    
	    // remove external links
	    foreach ($links as $index => $link) {
	        $this->output->writeln('Link: ' . $link);
	        $linkParts = parse_url($link);
	        if (empty($linkParts['host']) || $linkParts['host'] !== $domain || $linkParts['scheme'] !== 'http') {
	            unset($links[$index]);
	        }
	    }

	    // echo "=== Internal Links =================\n";

	    foreach ($links as $link) {
	    	$this->output->writeln('Link: ' . $link);
	    }

	    // remove links that we are not interested in 
	    $pattern = '/^(http:\\/\\/forums\\.hardwarezone\\.com\\.sg\\/money-mind-210\\/)(.*?)\\.html$/i';
	    foreach ($links as $key=>$link) {
	    	if (!preg_match($pattern, $link)) {
	    		unset($links[$key]);
	    	}
	    }

	    echo "=== Interested Links =================\n";

	    foreach ($links as $link) {
	    	$this->output->writeln('Link: ' . $link);
	    }

	    // echo "=== Response Body =================\n";

	    // $this->output->writeln($response->html());

		// $mem_size = 10 * 1024 * 1024;
        // $file = fopen("php://temp/maxmemory:$mem_size", 'r+');
        // fputs($file, $response->html());
        // rewind($file);

		// $filename = tempnam('/tmp', substr($url, strrpos($url, '/') + 1));
		// $file = fopen($filename, 'w');
		// fwrite($file, $response->html());
		// fclose($file);

		// $url = "qwe";
		// upload the content to Collamine servers
	    // $parameters = array('domain' => 'github.com', 'url' => $url, 'crawltime' => '0', 'contributor' => 'belson');
		// $parameters['document'] = unpack('C*', $response->html());
		// $response = $client->request('POST', $this::COLLAMINE_UPLOAD_URL, array('Content-Type => multipart/form-data'), array(), array(), $parameters);
				
		// echo $response->html();
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
			// array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}