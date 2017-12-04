<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main_controller extends CI_Controller {

	protected $data = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->model('user');
		$this->load->model('Board_Model');
		$this->load->model('List_Model');
		$this->load->model('Card_Model');
		$this->load->library('session');
		$this->data['error'] = '';		
	}
	
	public function index() 
	{
		if( empty( $this->session->userdata('id') ) ) {

			$this->data['error'] = '';

			$this->load->view('header');
			$this->load->view('home', $this->data);
			$this->load->view('footer');
			return;
		}
		redirect(base_url('index.php/main_controller/board'));
	}

	public function login()
	{
		if( !isset($_POST['login']) ) {
			redirect('main_controller');
			return;
		}
		
		$this->load->helper('form');
		$this->load->library('form_validation');

		$this->form_validation->set_rules('username', 'Username', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if($this->form_validation->run() === FALSE ) {
			$this->data['error'] = "";			
			$this->load->view('header');
			$this->load->view('home', $this->data);
			$this->load->view('footer');

		} else {
			$user = $this->input->post('username');
			$pass = $this->input->post('password');
		
			$response = $this->user->login($user, $pass);
			if( $response == false ) {
	
				$this->index();
				$this->data['error'] = "*Sorry there is problem encounterd";
				$this->load->view('header');
				$this->load->view('home', $this->data);
				$this->load->view('footer');		
				return;
			}
	
			if( $response->num_rows() != 1 ) {
				
				$this->data['error'] =  "*Username or Password not found";
				$this->load->view('header');
				$this->load->view('home', $this->data);
				$this->load->view('footer');		
				return;
			}
	
			$user_data = $response->row();
			$sess_user = array (
				'id' => $user_data->id,
				'name' => $user_data->name,
				'username' => $user,
			);

			$this->session->set_userdata($sess_user);
			redirect(base_url('index.php/main_controller/board'));		
		}
	}

	public function signup()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');

		$this->form_validation->set_rules('name', 'Name', 
			'required|min_length[3]|max_length[80]', 
			array( 
				'required' => 'Required Name',
				'min_length' => 'Min 3 characters',
				'max_length' => 'Max 80 characters'
			)
		);
		$this->form_validation->set_rules(
			'username', 'Username', 
			'required|min_length[5]|max_length[12]|is_unique[user.username]',
			array(
				'required' => 'Required Username',
				'min_length' => 'Min 5 characters',
				'max_length' => 'Max 12 characters',
				'is_unique' => 'Uername has been used' 
			)
		);
		$this->form_validation->set_rules('password', 'Password',
			'required|min_length[6]|max_length[12]',
			array(
				'required' => 'You must provide a %s.',
				'min_length' => 'Min 6 characters',
				'max_length' => 'Max 12 characters'
			)
		);
		$this->form_validation->set_rules('passconf', 'Password Confirmation','required|matches[password]',
			array(
				'required' => 'Confirm your password',
				'matches' => 'Password not match'
			)
		);

		if ($this->form_validation->run() === FALSE) {

	        $this->load->view('templates/header_home_signup');
	        $this->load->view('signup');
	        $this->load->view('footer');
	    
	    } else {

	        $this->user->register();
   			$this->data['error'] = "*You can login now";
			$this->load->view('header');
			$this->load->view('home', $this->data);
			$this->load->view('footer');
			return;
		}
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect(base_url('index.php/main_controller/'));
	}
	
	public function board() {

		if( empty( $this->session->userdata('id') ) ) {
			redirect(base_url('index.php/main_controller/'));		
		}

		$this->load->model('Board_Model');
		$dataBoard = $this->Board_Model->getBoard();
		$data['dataBoard'] = $dataBoard;

		$this->load->view('templates/header_board');
		$this->load->view('board', $data);
		$this->load->view('footer');

	}

	public function createBoard()
	{
		$id_user = $this->session->userdata('id');
		$data = array(
			'id_user' => $id_user,
	        'board_name' => $this->input->post('boardName'),
			'board_desc' => $this->input->post('boardDesc')
		);
		$result = $this->Board_Model->insertBoard($data);
		if(!$result) {
			echo "Error Encounter";
			return;
		}
		redirect('main_controller/board');
	}

	public function getBoardById()
	{
		$id = $this->input->get('id');
		$boardData = $this->Board_Model->getBoardById($id);
		// var_dump($boardData); die();
		header('Content-Type: application/json');
		echo json_encode( $boardData);
	}

	public function updateBoard() {
		$boardName = $this->input->post('boardName');
		$boardDesc = $this->input->post('boardDesc');
		$id = $this->input->post('id-board');
		echo $id;
		$boardData = $this->Board_Model->updateBoard($id, $boardName, $boardDesc);
		// var_dump($boardData); die();
		redirect('main_controller/board');
	}

	public function deleteBoard() {
		// echo "delete board"; die();
		$id = $this->uri->segment(3);
		$result = $this->Board_Model->deleteBoard($id);
		// var_dump($result); die();
		redirect(('main_controller/board'));
	}

	public function boardList() 
	{
		if( empty( $this->session->userdata('id') ) ) {
			redirect('main_controller');
		}
		
		$id_board = $this->uri->segment(3);
		$dataList = $this->List_Model->getList($id_board);
		$data['dataList'] = $dataList;
		/*$data['dataCard'] = $this->showCardByIdList();
		var_dump($data['dataCard']->row());
		die()*/;
		
		$this->load->view('templates/header_list');
		$this->load->view('list', $data);
	}

	public function createList()
	{
		$id_board = $this->uri->segment(3);
		$this->load->model('List_Model');
		$this->List_Model->insertList($id_board);
		redirect('main_controller/boardList/'.$id_board);
	}

	public function createCard()
	{
		$id_list = $this->input->post("list_id");
		$id_board = $this->uri->segment(3);
		$this->load->model('Card_Model');
		$this->Card_Model->insertCard($id_list);
		redirect('main_controller/boardList/'.$id_board.'/'.$id_list);
	}

	public function showCardByIdList()
	{
		$id_list = $this->uri->segment(4);
		$card = $this->Card_Model->getCard($id_list);
		return $card;
		/*echo "<pre>";
		var_dump($card);
		die();
		echo "</pre>";*/
	}
}