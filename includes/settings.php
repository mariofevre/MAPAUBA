<?php

class ApplicationSettings	{
	
	var $DATABASE_HOST;
	var $DATABASE_NAME;
	var $DATABASE_USERNAME;
	var $DATABASE_PASSWORD;	
	

	function ApplicationSettings()
	{
		$this->DATABASE_HOST = '192.168.0.244';
		$this->DATABASE_NAME = 'MAPAUBA';
		$this->DATABASE_USERNAME = 'UBA';
		$this->DATABASE_PASSWORD = 'GeoubA';
	}
}
?>
