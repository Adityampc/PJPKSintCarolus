<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Schedule extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (!$this->session->userdata('user_id')) {
			header("Location: " . base_url('signin'));
			die;
		}
		$this->load->model('Schedule_model', 'Schedule');
	}
	public function index()
	{
		$data['page_title'] = "Life Care - Schedule";
		$data['schedules'] = $this->Schedule->getByUserId($this->session->userdata('user_id'));
		$this->load->view('schedule/index', $data);
	}
	public function detail($id)
	{
		$data['schedule'] = $this->Schedule->get($id, $this->session->userdata('user_id'));
		if (!$data['schedule']) {
			$this->session->set_flashdata('errMsg', "Schedule not found");
			redirect('schedule');
		}
		$data['page_title'] = "Schedule - " . $data['schedule']['title'];
		$this->load->view('schedule/detail', $data);
	}
	public function edit($id)
	{
		$data['schedule'] = $this->Schedule->get($id, $this->session->userdata('user_id'));
		if (!$data['schedule']) {
			$this->session->set_flashdata('errMsg', "Schedule not found");
			redirect('schedule');
		}
		$data['page_title'] = "Schedule - " . $data['schedule']['title'];
		$this->load->view('schedule/edit', $data);
	}
	public function delete($id)
	{
		$data['schedule'] = $this->Schedule->get($id, $this->session->userdata('user_id'));
		if (!$data['schedule']) $this->session->set_flashdata('errMsg', "Schedule not found");
		elseif ($this->Schedule->delete_entry($id)) $this->session->set_flashdata('succMsg', "Schedule deleted successfully");
		else  $this->session->set_flashdata('errMsg', "Failed to delete Schedule");
		redirect('schedule');
	}
	public function editProcess($id)
	{
		$schedule = $this->Schedule->get($id, $this->session->userdata('user_id'));
		if (!$schedule) {
			$this->session->set_flashdata('errMsg', "Schedule not found");
			redirect('schedule');
		}

		$this->form_validation->set_rules(
			'title',
			'Title',
			'required',
			[
				'reduired' => "Title Required"
			],
		);
		$this->form_validation->set_rules(
			'dateto',
			'Date',
			'required',
			[
				'required' => 'Date Required'
			],
		);
		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('errMsg', validation_errors());
			redirect('schedule/edit/' . $schedule['id']);
		}

		$schedule['title'] = $this->input->post('title');
		$schedule['description'] = htmlentities($this->input->post('description'));
		$schedule['dateto'] = $this->input->post('dateto');
		$result = $this->Schedule->update_entry($schedule);
		if ($result) $this->session->set_flashdata('succMsg', "Schedule updated successfully");
		else $this->session->set_flashdata('errMsg', "Failed to update");
		redirect('schedule/edit/' . $schedule['id']);
	}
	public function add()
	{
		$data['page_title'] = "Schedule - Add";
		$this->load->view('schedule/add', $data);
	}
	public function addProcess()
	{

		$this->form_validation->set_rules(
			'title',
			'Title',
			'required',
			[
				'reduired' => "Title Required"
			],
		);
		$this->form_validation->set_rules(
			'dateto',
			'Date',
			'required',
			[
				'required' => 'Date Required'
			],
		);
		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('errMsg', validation_errors());
			redirect('schedule/add');
		}

		$schedule['user_id'] = $this->session->userdata('user_id');
		$schedule['title'] = $this->input->post('title');
		$schedule['description'] = htmlentities($this->input->post('description'));
		$schedule['dateto'] = $this->input->post('dateto');
		$result = $this->Schedule->insert_entry($schedule);
		if ($result) {
			$this->session->set_flashdata('succMsg', "Schedule added successfully");
			redirect('schedule/edit/' . $result);
		} else $this->session->set_flashdata('errMsg', "Failed to add");
		redirect('schedule/add');
	}
}
