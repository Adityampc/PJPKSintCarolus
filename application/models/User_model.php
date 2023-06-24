<?php
class User_model extends CI_Model
{

	public $id;
	public $name;
	public $email;
	public $password;
	public $created_at;
	public $updated_at;
	public $forgot_hash = null;

	public function getByEmail($email)
	{
		return	$this->db->get_where('users', ['email' => $email])->row_array();
	}

	public function getByForgotHash($forgot_hash)
	{
		return $this->db->get_where('users', ['forgot_hash' => $forgot_hash])->row_array();
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
		$this->db->insert('users', $this);
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
		$this->db->update('users', $this, array('id' => $this->id));
		return $this->db->affected_rows();
	}
}
