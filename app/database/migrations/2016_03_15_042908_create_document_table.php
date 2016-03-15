<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('document', function(Blueprint $table){
			$table->increments('id');
			$table->string('url')->unique();
			$table->string('domain');
			$table->string('source');
			$table->longText('content');
			$table->string('crawled_date');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('document');
	}

}