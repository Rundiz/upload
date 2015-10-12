<?php
/**
 * @author vee
 * @copyright http://www.okvee.net
 * 
 * ตัวระบุค่า ext => mime type
 * 
 * หากจะกรอกเพิ่มเติม!!
 * ควรตรวจดูประเภทไฟล์ให้แน่ใจด้วยว่าจะไม่เป็นอันตรายต่อ server ที่คุณใช้
 * เช่น ไม่ควรอนุญาต php, cgi, pl และอื่นๆที่น่าจะเป็นอันตรายต่อ server
 * ทั้งนี้เพราะ เมื่อมีการสั่ง upload โดยไม่ระบุประเภทไฟล์ที่อนุญาต ระบบจะทำการตรวจ mime ที่ตรงนี้ก่อน หากพบก็คืออัปโหลดได้
 * ดังนั้นต้องระวังในการเพิ่ม mime ตรงนี้ให้มาก.
 */

$mimes = array(
		'ai' => array('application/postscript'),
		'bmp' => array('image/bmp'),
		'css' => array('text/css'),
		'doc' => array('application/msword'),
		'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
		'exe' => array('application/octet-stream', 'application/x-msdownload'),
		"gif" => array("image/gif"),
		'gtar' => array('application/x-gtar'),
		'gz' => array('application/x-gzip'),
		"jpg" => array("image/jpeg", "image/pjpeg"),
		"jpeg" => array("image/jpeg", "image/pjpeg"),
		'js' => array('application/x-javascript'),
		'lzh' => array('application/octet-stream'),
		'mp3' => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
		'pdf' => array('application/pdf', 'application/x-download'),
		"png" => array("image/png"),
		'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
		'psd' => array('application/x-photoshop'),
		'swf' => array('application/x-shockwave-flash'),
		'tar' => array('application/x-tar'),
		'tiff' => array('image/tiff'),
		'tif' => array('image/tiff'),
		'tgz' => array('application/x-tar', 'application/x-gzip-compressed'),
		'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
);

?>