<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

<<<<<<< HEAD
	public function Index() 
	{
		$domains = Document::select('domain')->groupBy('domain')->get();
		return View::make('index', array('domains' => $domains));
	}

	public function RetrieveByCrawledDate()
	{
		if (Request::ajax()) {
			$prev_total = Input::get('total');
			$curr_total = Document::all()->count();
			$collamine = 0; $original = 0;
	        $difference = $curr_total - $prev_total;
			foreach (Document::select('source')->orderBy('crawled_date', 'desc')->get()->take($difference) as $document) {
				if ($document->source == 'collamine')
					$collamine++;
				if ($document->source == 'original')
					$original++;
			}
            return [$original, $collamine];
		}
	}

	public function RetrieveTotal()
	{
		if (Request::ajax())
		   	return [Document::where('source', '=', 'collamine')->count(), Document::where('source', '=', 'original')->count(), Document::all()->count()];
	}

	public function RetrieveDoc()
	{
		if (Request::ajax())
	        return Document::select('url')->orderBy('crawled_date', 'desc')->get()->take(10);
	}


	public function RetrieveDomains()
	{
		if (Request::ajax())
			return Document::select(DB::raw('distinct domain as ddom'));
=======
	public function showWelcome()
	{
		return View::make('hello');
>>>>>>> 84e743afa083d4ab37fda0623c4bea16bc5b53a3
	}

}