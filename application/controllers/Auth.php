<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model', 'User');
	}
	public function signIn()
	{
		if ($this->session->userdata('user_id')) 	return redirect("dashboard");
		$data['page_title'] = "Sign In";
		$this->load->view('auth/signIn', $data);
	}
	public function signInCheck()
	{

		if ($this->session->userdata('user_id')) {
			echo json_encode(['message' => "Already signed in", 'redirect' => base_url('dashboard')]);
			return;
		}

		$this->form_validation->set_rules(
			'email',
			'Email',
			'required|valid_email',
			['reduired' => "Email Required", 'valid_email' => "Invalid email"],
		);
		$this->form_validation->set_rules(
			'password',
			'Password',
			'required',
			['required' => 'Password Required'],
		);
		if ($this->form_validation->run() == FALSE) {
			header('HTTP/1.1 400 Validation Errors', true, 400);
			echo json_encode(['message' => validation_errors()]);
			return;
		}
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$user = $this->User->getByEmail($email);
		if (!$user) {
			header('HTTP/1.1 400 Validation Errors', true, 400);
			echo json_encode(['message' => "Invalid email address or password"]);
			return;
		}
		if (!password_verify($password, $user['password'])) {
			header('HTTP/1.1 400 Validation Errors', true, 400);
			echo json_encode(['message' => "Invalid email address or password"]);
			return;
		}
		$this->session->set_userdata('user_id', $user['id']);
		$this->session->set_userdata('user', $user);
		echo json_encode(['message' => "Sign In Successfully", 'redirect' => base_url('dashboard')]);
		return;
	}
	public function signUp()
	{
		if ($this->session->userdata('user_id')) 	return redirect("dashboard");
		$data['page_title'] = "Sign Up";
		$this->load->view('auth/signUp', $data);
	}
	public function signUpProcess()
	{

		if ($this->session->userdata('user_id')) {
			echo json_encode(['message' => "Already signed in", 'redirect' => base_url('dashboard')]);
			return;
		}
		$this->form_validation->set_rules(
			'name',
			'Name',
			'required',
			[
				'reduired' => "Name Required"
			],
		);
		$this->form_validation->set_rules(
			'email',
			'Email',
			'required|valid_email|is_unique[users.email]',
			[
				'reduired' => "Email Required",
				'valid_email' => "Invalid email",
				'is_unique' => "Email already exists"
			],
		);
		$this->form_validation->set_rules(
			'password',
			'Password',
			'required|min_length[6]',
			[
				'required' => 'Password Required',
				'min_length' => 'Password must be at least 6 characters',
			],
		);
		$this->form_validation->set_rules(
			'passconf',
			'Password Confirmation',
			'required|matches[password]',
			[
				'required' => 'Password Confirmation Required',
				'matches' => 'Password Confirmation Not Matched',
			]
		);
		if ($this->form_validation->run() == FALSE) {
			header('HTTP/1.1 400 Validation Errors', true, 400);
			echo json_encode(['message' => validation_errors()]);
			return;
		}
		$this->User->email = $this->input->post('email');
		$this->User->password = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
		$this->User->name = $this->input->post('name');
		$this->User->created_at = date("Y-m-d H:i:s");
		$this->User->updated_at = date("Y-m-d H:i:s");
		$result = $this->User->insert_entry();
		if (!$result) {
			header("HTTP/1.1 500 Internal Server Error");
			echo json_encode(['message' => "Internal Server Error"]);
		} else echo json_encode(['message' => "Sign Up Successfully", 'redirect' => base_url('signin')]);
		return;
	}
	public function forgot()
	{
		if ($this->session->userdata('user_id')) 	return redirect("dashboard");
		$data['page_title'] = "Forgot Password";
		$this->load->view('auth/forgot', $data);
	}
	public function forgotCheck()
	{

		if ($this->session->userdata('user_id')) {
			echo json_encode(['message' => "Already signed in", 'redirect' => base_url('dashboard')]);
			return;
		}
		$hash = sha1(time());

		$this->form_validation->set_rules(
			'email',
			'Email',
			'required|valid_email',
			[
				'reduired' => "Email Required",
				'valid_email' => "Invalid email",
			],
		);

		if ($this->form_validation->run() == FALSE) {
			header('HTTP/1.1 400 Validation Errors', true, 400);
			echo json_encode(['message' => validation_errors()]);
			return;
		}

		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$user = $this->User->getByEmail($email);

		if (!$user) {
			header('HTTP/1.1 400 Validation Errors', true, 400);
			echo json_encode(['message' => "Invalid email address"]);
			return;
		}
		$user['forgot_hash'] = $hash;
		$result = $this->User->update_entry($user);
		if (!$result) {
			header("HTTP/1.1 500 Internal Server Error");
			echo json_encode(['message' => "Internal Server Error"]);
		} else echo json_encode(['message' => "Forgot Password Successfully", 'link' => base_url("reset/$hash")]);
		return;
	}

	public function reset($hash)
	{
		if ($this->session->userdata('user_id')) return redirect("dashboard");
		$user = $this->User->getByForgotHash($hash);
		if (!$user) 	return redirect("signin");
		$data['page_title'] = "Reset Password";
		$this->load->view('auth/reset', $data);
	}
	public function resetProcess($hash)
	{
		if ($this->session->userdata('user_id')) {
			echo json_encode(['message' => "Already signed in", "redirect" => base_url('dashboard')]);
			return;
		}

		$user = $this->User->getByForgotHash($hash);
		if (!$user) {
			echo json_encode(['message' => "Already signed in", "redirect" => base_url('dashboard')]);
			return;
		}
		$this->form_validation->set_rules(
			'password',
			'Password',
			'required|min_length[6]',
			[
				'required' => 'Password Required',
				'min_length' => 'Password must be at least 6 characters',
			],
		);
		if ($this->form_validation->run() == FALSE) {
			header('HTTP/1.1 400 Validation Errors', true, 400);
			echo json_encode(['message' => validation_errors()]);
			return;
		}

		$user['forgot_hash'] = null;
		$user['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
		$result = $this->User->update_entry($user);
		if (!$result) {
			header("HTTP/1.1 500 Internal Server Error");
			echo json_encode(['message' => "Internal Server Error"]);
		} else echo json_encode(['message' => "Reset Password Successfully", 'redirect' => base_url("signin")]);
		return;
	}

	public function signOut()
	{
		session_destroy();
		return redirect("signin");
	}
}
