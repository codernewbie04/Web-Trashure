<?php

class Auth_models extends CI_MODEL
{
	function cek_email($email)
	{
		return $this->db->get_where('users', ['email' => $email])->num_rows();
	}

	function create_acc($data)
	{
		$insert = $this->db->insert('users', $data);
		$user_id = $this->db->insert_id();

		$this->load->helper('string');
		$apikey = random_string('sha1');
		$this->db->insert('keys', ['user_id' => $user_id, 'key' => $apikey, 'level' => 1, 'date_created' => time()]);
		return $insert;
	}

	function check_login($email, $password)
	{
		$user = $this->db->get_where('users', ['email' => $email]);
		if($user->num_rows() > 0){
			$user = $user->last_row('array');
			$password_check = $user['password'];
			return password_verify($password, $password_check);
		} else {
			return false;
		}
	}

	function refresh_apikey($email)
	{
		$get = $this->db->get_where('users', ['email' => $email])->last_row("array");
		$id = $get['id'];
		$this->db->delete("keys", ['user_id' => $id]);

		$this->load->helper('string');
		$apikey = random_string('sha1');
		$this->db->insert('keys', ['user_id' => $id, 'key' => $apikey, 'level' => 1, 'date_created' => time()]);
	}

	function get_user($email)
	{
		$this->db->select("users.id, users.email, users.nama, users.nohp, users.saldo, users.level, users.foto, keys.key as apikey, level.nama as level");
		$this->db->from("users");
		$this->db->join('keys', 'keys.user_id = users.id');
		$this->db->join('level', 'level.id = users.level');
		$this->db->where('users.email', $email);
		return $this->db->get()->last_row("array");
	}

	function create_token($email)
	{
		$get = $this->db->get_where('users', ['email' => $email])->last_row("array");
		$id = $get['id'];

		$this->load->helper('string');
		$token = random_string('sha1');
		return $this->db->insert('forget_token', ['user_id' => $id, 'token' => $token]);
	}
}
