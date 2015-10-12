<?php

/**
 * @author vee
 * @copyright http://www.okvee.net
 */


if ( file_exists(dirname(__FILE__)."/mime-upload.php") ) {
	require(dirname(__FILE__)."/mime-upload.php");
} else {
	die("Required file mime-upload.php was not found!");
}


/**
 * class upload สำหรับผู้เขียนด้วย php ใช้ในการอัปโหลด
 * ไฟล์นี้ต้องการไฟล์ mime-upload.php
 */
class vfileup {
	
	
	public $allowed_types;// allowed file type eg. jpg|jpeg|gif|png (refer from mime-upload.php)
	public $encrypt_name = false;
	public $error_msg;
	public $filename;// set file name
	public $max_size;
	public $overwrite = false;
	public $remove_space = true;
	public $upload_path = ".";
	
	
	// about $_FILES['..']
	private $file_name;
	private $file_name_only;
	private $file_ext;
	private $file_tmp_name;
	private $file_type;
	private $file_size;// in byte
	
	
	// data for upload completed
	private $new_file_name;// file+.ext
	private $new_file_name_only;
	private $new_file_path;// path of file not file name.ext
	private $new_full_path;// path to file name.ext
	private $orig_name;// original new name before encrypt


	function __construct($inputfile = '') {
		$this->error_msg = "";
		if ( !isset($inputfile['name']) || (isset($inputfile['name']) && $inputfile['name'] == null) ) {
			$this->error_msg = "โปรดเลือกไฟล์ที่จะอัพโหลด.";
		} else {
			$this->file_name = $inputfile['name'];
			$find_ext = explode(".", $this->file_name);
			$this->file_ext = $find_ext[count($find_ext)-1];
			$this->file_name_only = mb_substr ($this->file_name, 0, (mb_strlen($this->file_name)-1)-mb_strlen($find_ext[(count($find_ext)-1)]) );
			$this->file_tmp_name = $inputfile['tmp_name'];
			$this->file_type = $inputfile['type'];
			$this->file_size = $inputfile['size'];
		}
	}// __construct
	
	
	function __destruct() {
		
	}// __destruct
	
	
	function check_allowed_types() {
		global $mimes;
		if ( $this->allowed_types != null ) {
			$exp_at = explode("|", $this->allowed_types);
			if ( is_array($exp_at) ) {
				if ( in_array($this->file_ext, $exp_at) ) {// file_ext จาก upload อยู่ใน array allowed_types
					if ( isset($mimes[$this->file_ext]) ) {// file_ext จาก upload อยู่ใน array mimes.
						if ( in_array($this->file_type, $mimes[$this->file_ext]) ) {
							return true;
						} else {
							$this->error_msg = "ประเภทไฟล์ที่คุณกำลังอัพโหลดนั้น มี mime type ไม่ถูกต้อง.";
							return false;
						}
					} elseif ( !isset($mimes[$this->file_ext]) ) {
						$this->error_msg = "ประเภทไฟล์ที่คุณกำลังอัพโหลดนั้น ไม่มีระบุอยู่ใน mime type ที่กำหนดไว้";
						return false;
					}
				} else {
					$this->error_msg = "คุณกำลังอัพโหลดไฟล์ประเภทที่ไม่ได้อนุญาตไว้ โปรดอัพโหลดไฟล์เฉพาะประเภทที่อนุญาตเท่านั้น";
					return false;
				}
			} else {
				$this->error_msg = "คุณระบุค่าประเภทไฟล์ที่อนุญาตไม่ถูกต้อง. ตัวอย่างที่ถูกต้อง: jpg|gif|png|txt";
				return false;
			}
		} elseif ( $this->allowed_types == null ) {
			if ( isset($mimes[$this->file_ext]) ) {
				if ( in_array($this->file_type, $mimes[$this->file_ext]) ) {
					return true;
				} else {
					$this->error_msg = "ประเภทไฟล์ที่คุณกำลังอัพโหลดนั้น มี mime type ไม่ถูกต้อง.";
					return false;
				}
			} elseif ( !isset($mimes[$this->file_ext]) ) {
				$this->error_msg = "ประเภทไฟล์ที่คุณกำลังอัพโหลดนั้น ไม่มีระบุอยู่ใน mime type ที่กำหนดไว้";
				return false;
			}
		}
	}// check_allowed_types
	
	
	/**
	 * check_dup_file
	 * หาไฟล์ซ้ำ
	 * ก่อนจะมา method นี้ให้ทำการ set file name ซะก่อน
	 * @param type $new_file_name 
	 */
	function check_dup_file($new_file_name = '') {
		if ( $new_file_name == null ) {return $new_file_name;}
		if ( $this->overwrite == true ) {return $new_file_name;}
		// check dup file
		$dup_file = true;
		$count = 1;
		$check_file_name = $new_file_name;
		do {
			if ( file_exists($this->upload_path."/".$check_file_name . "." . $this->file_ext) ) {
				$check_file_name = $new_file_name . "($count)";
				$count++;
			} else {
				$dup_file = false;
			}
		} while ( $dup_file == true );
		return $check_file_name;
	}// check_dup_file
	
	
	function check_file_size() {
		if ( $this->max_size == null ) {return true;}
		if ( ($this->file_size) > ($this->max_size) ) {
			$this->error_msg = "ขนาดไฟล์ของคุณใหญ่กว่าที่กำหนด " . $this->file_name;
			return false;
		} else {
			return true;
		}
	}// check_file_size
	
	
	function data() {
		$output['file_name'] = $this->new_file_name;
		$output['file_name_only'] = $this->new_file_name_only;
		$output['file_type'] = $this->file_type;
		$output['file_path'] = $this->new_file_path;
		$output['full_path'] = $this->new_full_path;
		$output['orig_name'] = $this->orig_name;
		$output['client_name'] = $this->file_name;
		$output['file_ext'] = $this->file_ext;
		$output['file_size'] = $this->file_size;// in byte.
		return $output;
	}//data
	
	
	/**
	 * do_upload
	 * 1. หาค่าที่อนุญาต ถ้ามีก็เช็คไป ถ้าไม่มีก็หาว่าอยู่ในประเภทของ mime หรือเปล่า. ถ้าผ่านทำต่อไป ถ้าไม่ผ่าน return false
	 * 2. หาค่าขนาดที่ระบุ ถ้าไม่กำหนด หรือกำหนดแต่ไม่เกินก็ทำต่อไป ถ้ากำหนดแต่เกินก็ return false
	 * 3. ทำการ set filename/remove space/enctype/ ตามลำดับ
	 * 4. หาไฟล์ซ้ำ หากกำหนด overwrite = false ก็หาไฟล์ซ้ำ หากไม่กำหนดก็ไม่ต้องหา
	 * 5. ผ่านหมดแล้ว อัปโหลดได้
	 * 6. ส่งค่าที่สำเร็จกลับไป
	 * 7. return true;
	 */
	function do_upload() {
		if ( $this->error_msg != null ) {return false;}
		if ( $this->check_allowed_types() === false ) {return false;}// 1.
		if ( $this->check_file_size() === false ) {return false;}// 2.
		$new_file_name = $this->set_file_name();// 3.
		$new_file_name = $this->check_dup_file($new_file_name);// 4.
		// 5.
		if ( file_exists($this->upload_path."/") ) {
			if ( is_writable($this->upload_path) ) {
				$move = @move_uploaded_file($this->file_tmp_name, $this->upload_path."/".$new_file_name . "." . $this->file_ext);
				if ( !$move ) {
					$this->error_msg = "path ที่กำหนดไม่สามารถเขียนไฟล์ได้ หรือชื่อไฟล์มีอักขระที่ไม่อนุญาตสำหรับระบบอยู่ เช่น \\ / ?";
					return false;
				}
			} else {
				$this->error_msg = "path ที่กำหนดไม่สามารถเขียนไฟล์ได้";
				return false;
			}
		} else {
			$this->error_msg = "ไม่พบตำแหน่ง path ที่จะอัพโหลดไฟล์";
			return false;
		}
		// 6.
		$this->set_data($new_file_name);
		return true;
	}// do_upload
	
	
	function safe_file_name($file_name = '') {
		// rename for safe upload
		$file_name = str_replace(array("\\", "/", "*", ":", "?", "\"", "<", ">", "|"), "_", $file_name);
		return $file_name;
	}// safe_file_name
	
	
	private function set_data($new_file_name_only = '') {
		if ( $new_file_name_only == null ) {$this->error_msg = "Error at set_data(). missing attribute."; return false;}
		$this->new_file_name = $new_file_name_only . "." . $this->file_ext;
		$this->new_file_name_only = $new_file_name_only;
		$this->new_file_path = realpath($this->upload_path."/".$this->new_file_name);
		$this->new_full_path = realpath($this->upload_path."/");
		return true;
	}// set_data
	
	
	private function set_file_name() {
		$file_name = $this->safe_file_name($this->file_name_only);
		$this->orig_name = $file_name . "." . $this->file_ext;
		//set new name
		if ( $this->filename != null ) {
			$file_name = $this->safe_file_name($this->filename);
			$this->orig_name = $file_name . "." . $this->file_ext;
		}
		// remove space
		if ( $this->remove_space === true ) {
			$file_name = str_replace(array("   ", "  ", " "), "-", $file_name);
			$this->orig_name = $file_name . "." . $this->file_ext;
		}
		// encrypt name
		if ( $this->encrypt_name === true ) {
			$file_name = (function_exists('sha1') ? sha1($file_name) : md5($file_name));
		}
		return $file_name;
	}// set_file_name
	
	
}

?>