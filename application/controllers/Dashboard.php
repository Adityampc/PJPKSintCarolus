<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
	public function index()
	{
		if (!$this->session->userdata('user_id')) return redirect("signin");
		$this->load->model('Schedule_model', 'Schedule');
		$data['schedules'] = $this->Schedule->schedule_today($this->session->userdata('user_id'));
		$data['page_title'] = "Life Care - Dashboard";
		$this->load->view('dashboard', $data);
	}
}
