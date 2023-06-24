<?php
class Schedule_model extends CI_Model
{

	public $id;
	public $user_id;
	public $title;
	public $description;
	public $dateto;
	public $updated_at;
	public $created_at;

	public function getByUserId($userId)
	{
		return	$this->db->get_where('schedule', ['user_id' => $userId])->result_array();
	}
	public function get($id, $opt)
	{
		$w['id'] =  $id;
		if (isset($opt['user_id'])) $w['user_id'] = $opt['user_id'];
		return	$this->db->get_where('schedule', $w)->row_array();
	}

	public function delete_entry($id)
	{
		$this->db->where('id', $id);
		$this->db->delete('schedule');
		return $this->db->affected_rows();
	}

	public function schedule_today($user_id)
	{
		$this->db->where('user_id', $user_id);
		$this->db->where('DATE(dateto)', date("Y-m-d"));
		return $this->db->get('schedule')->result_array();
	}

	public function insert_entry($u = null)
	{
		if ($u) {
			foreach ($u as $k => $v) {
				try {
					$this->$k = $v;
				} catch (\Throwable $th) {
					//throw $th;
				}
			}
		}
		$this->created_at = date("Y-m-d H:i:s");
		$this->updated_at = date("Y-m-d H:i:s");
		$this->db->insert('schedule', $this);
		return $this->db->insert_id();
	}

	public function update_entry($u = null)
	{

		if ($u) {
			foreach ($u as $k => $v) {
				try {
					$this->$k = $v;
				} catch (\Throwable $th) {
					//throw $th;
				}
			}
		}
		$this->updated_at = date("Y-m-d H:i:s");
		$this->db->update('schedule', $this, array('id' => $this->id));
		return $this->db->affected_rows();
	}
}
