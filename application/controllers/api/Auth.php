<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Auth extends RestController
{

	function __construct()
	{
		// Construct the parent class
		parent::__construct();

		$this->load->model("auth_models", "auth");
	}

	public function login_post()
	{
		$email 		= $this->input->post("email", TRUE);
		$password	= $this->input->post("password");

		if(!$email || !$password ){
			$this->response(['status' => 0, 'msg' => "Data tidak boleh kosong!"], 400);
		} else if(!$this->auth->check_login($email, $password)){
			$this->response(['status' => 0, 'msg' => "Email atau password tidak ditemukain!"], 404);
		} else {
			$this->auth->refresh_apikey($email);
			$user = $this->auth->get_user($email);
			$this->response(['status' => 1, 'msg' => "Berhasil mendapatkan data!", "data" => $user], 200);
		}
	}

	public function register_post()
	{
		$nama 		= $this->input->post("nama", TRUE);
		$email 		= $this->input->post("email", TRUE);
		$nohp 		= $this->input->post("nohp", TRUE);
		$password	= $this->input->post("password");
		$foto		= $this->input->post("foto");

		if(!$nama || !$email || !$nohp || !$password || !$foto){
			$this->response(['status' => 0, 'msg' => "Data tidak boleh kosong!"], 400);
		} else if(strlen($nohp) <= 5){
			$this->response(['status' => 0, 'msg' => "No HP tidak valid!"], 400);
		} else if(strlen($password) <= 5){
			$this->response(['status' => 0, 'msg' => "Password terlalu pendek!"], 400);
		} else if($this->auth->cek_email($email) > 0){
			$this->response(['status' => 0, 'msg' => "Email sudah terdaftar!"], 409);
		} else {
			$namafoto = time() . '-' . rand(0, 99999) . ".jpg";
			$path     = "assets/images/users/" . $namafoto;
			file_put_contents($path, base64_decode($foto));

			$req = array(
				'nama' => $nama,
				'email' => $email,
				'nohp' => $nohp,
				'password' => password_hash($password, PASSWORD_BCRYPT),
				'foto' => $namafoto
			);
		
			$insert = $this->auth->create_acc($req);
			if($insert){
				$user = $this->auth->get_user($email);
				$this->response(['status' => 1, 'msg' => "Berhasil membuat akun!","data" => $user], 200);
			} else {
				$this->response(['status' => 0, 'msg' => "Gagal memasukan data kedala database!"], 409);
			}
		}
	}

	public function forget_post()
	{
		$email 		= $this->input->post("email", TRUE);
		if(!$email){
			$this->response(['status' => 0, 'msg' => "Email tidak boleh kosong!"], 400);
		} else if($this->auth->cek_email($email) < 1){
			$this->response(['status' => 0, 'msg' => "Email tidak ditemukan!"], 404);
		} else {
			$token = $this->auth->create_token($email);
			if($token){
				$this->response(['status' => 1, 'msg' => "Token sudah dikirim, silakan cek Email anda."], 200);
			} else {
				$this->response(['status' => 0, 'msg' => "Gagal memasukan data kedala database!"], 409);
			}
		}
	}
	
}
