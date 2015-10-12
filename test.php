<?php

/*if ( isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != null ) {
	$file = $_FILES['file'];
	print_r($file);
	unlink($file['tmp_name']);
}*/ // ใช้สำหรับตรวจหา mime type ใหม่ๆที่จะเพิ่มเข้าไปใน mime-upload


if ( isset($_FILES['file']['name']) && $_FILES['file']['name'] != null ) {// ตรวจว่ามีการเลือกไฟล์หรือยัง ถ้ายังไม่ต้องเสียเวลาทำ.
	include(dirname(__FILE__)."/vfileup.php");
	
	$vfileup = new vfileup($_FILES['file']);
	// config upload
	$vfileup->allowed_types = "jpg|gif|png|txt";
	$vfileup->encrypt_name = true;
	$vfileup->filename = '';// กำหนดชื่อเฉยๆ ไม่ต้องมี .ext
	$vfileup->max_size = "";// size in byte
	$vfileup->overwrite = false;
	$vfileup->remove_space = true;
	$vfileup->upload_path = dirname(__FILE__);
	// upload
	$upload_result = $vfileup->do_upload();
	if ( $upload_result !== true ) {
		$form_status = "<div class=\"block-error\">" . $vfileup->error_msg . "</div>";
	} else {
		$form_status = "<div class=\"block-success\">success</div>";
		echo "debug:<br />\n<pre>\n";
		print_r($vfileup->data());
		echo "</pre>\n";
	}
}


?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>vfileup : PHP vee's file upload class.</title>
		<style type="text/css">
			.block-error {background: lightpink; border: 3px solid red; font-weight: bold;}
			.block-success {background: lightgreen; border: 3px solid darkgreen; font-weight: bold;}
		</style>
	</head>
	<body>
		<div style="border: 1px solid #eee; margin: 150px auto 0 auto; overflow: hidden; padding: 10px; width: 500px;">
			<h1 style="font-size: 20px;">vfileup : PHP vee's file upload class.</h1>
			<?php if ( isset($form_status) ) {echo $form_status;} ?> 
			<form method="post" action="test.php" enctype="multipart/form-data">
				<input type="hidden" name="MAX_FILE_SIZE" value="2097152" /><!--2097152 byte = 2MB -->
				file: <input type="file" name="file" value="" /><br />
				<button type="submit" name="btn">Upload</button>
			</form>
			<span style="color:#aaa; font-size: 11px;">&copy; <a href="http://www.okvee.net" style="color:#aaa;">okvee.net</a></span>
		</div>
	</body>
</html>
