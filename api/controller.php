<?php

//! Base controller
class Controller {

	protected
		$framework,
		$db;

	//! HTTP route pre-processor
	function beforeroute() {
		$f3=$this->framework;
		$db=$this->db;
	}

	//! Instantiate class
	function __construct() {
		$f3=Base::instance();
		
		// Save frequently used variables
		$this->framework=$f3;
		$this->db=$db;
	}

}
