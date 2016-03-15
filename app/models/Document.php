<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class Document extends Eloquent {
	public $timestamps = false;
	protected $table ='collamine';
	protected $fillable = array('url', 'domain', 'source', 'content', 'crawled_date');
}