<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {


	public function index()
	{
		$this->template->load('template', 'view_order');
	}
}
