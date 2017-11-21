<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main_controller extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index() {
		$this->load->view('header');
		$this->load->view('home');
		$this->load->view('footer');
	}

	public function board() {
		$this->load->view('header');
		$this->load->view('board');
		$this->load->view('footer');
	}

	public function list() 
	{
		$this->load->model('list_user');
        $data['daftar_list'] = $this->list_user->getlist();
		$this->load->view('templates/header_list');
		$this->load->view('list',$data);
	}

	public function signup()
	{
		$this->load->view('templates/header_home_signup');
		$this->load->view('signup');	
	}

	function simpan()
    {   
        $this->load->model('list_user');
        $this->list_user->simpan_list();
        $this->load->view('list');
    }
}