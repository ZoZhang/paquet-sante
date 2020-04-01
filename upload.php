<?php

/**
 * 中国驻法国　- 祖国母亲健康包登记表核对平台
 *
 * @author Zhao ZHANG <zo.zhang@gmail.com>
 * @github http://github.com/zozhang/paquet-sante
 */

define('PS', PATH_SEPARATOR);
define('DS', DIRECTORY_SEPARATOR);
define('CP', dirname(__FILE__));
define('ROOT_PATH', realpath(CP));
define('FILE_PATH', ROOT_PATH . DS . 'files');

class Amour {

	protected static $_settings = [];

	public static function run()
	{
		$response = [
		    'error' => true,
		    'message' => '服务器内部错误，请稍后再试!'
		];

		if (!isset($_FILES['table']) || !count($_FILES['table'])) {
		    print_r(json_encode($response));
		    exit;
		}


		$fileName = $_FILES['table']['name'];
		$dirName = explode('-', $fileName);


		if (empty($dirName)) {
			$response['message'] = '请按规定格式重命名csv文件';
			print_r(json_encode($response));
			exit;
		}

		self::$_settings['new_path'] = FILE_PATH . DS . $dirName{0};
		if (!is_dir(self::$_settings['new_path'])) {
			@mkdir(self::$_settings['new_path']);
		}

		if (!is_dir(self::$_settings['new_path'])) {
			$response['message'] = '儿豁，系统异常，请联系小召召.';
			print_r(json_encode($response));
			exit;
		}

		$tmp_upload_file = $_FILES['table']['tmp_name'];
		if (!file_exists($tmp_upload_file)) {
			$response['message'] = '莫挨老子！Gun~';
			print_r(json_encode($response));
			exit;
		}

	   if (!stripos($fileName, 'csv')) {
	       $response['message'] = '只能上传csv文件.';
	       print_r(json_encode($response));
		   exit;
	   }

	   $newfile = self::$_settings['new_path'] . DS . $fileName;

	   if(!move_uploaded_file($tmp_upload_file, $newfile)) {
	       $response['message'] = '请稍后再试一次.';
	       print_r(json_encode($response));
		   exit;
		} 
	}

	public static function find()
	{
	    $cdir = scandir(self::$_settings['new_path']);

	    if (count($cdir) != 4) 
	    {
	   	 return false;
	    }

	    $files = [];
	    foreach ($cdir as $key => $file)
	    {
		    if (in_array($file, array(".","..")))
		    {
		      	continue;
		    }

		    $files[] = $file;
		}

		self::$_settings['files'] = $files;
	}

	public static function lock()
	{
		if (count(self::$_settings['files']) != 2) {
			$response['message'] = '请稍后再试一次.';
	        print_r(json_encode($response));
		    exit;
		}

		$loop = 0;
		foreach (self::$_settings['files'] as $file)
		{
			if (($handle = fopen(self::$_settings['new_path'] . DS . $file, "r")) === FALSE) {
				continue;
			}

			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

		    	$name = $data[0];
		    	$sexe = $data[1] == '女' ? 'F' : ($data[1] == '男' ? 'M' : $data[1]);
		    	$tel  = $data[2];
		    	$mail = $data[3];
		    	$addr = $data[4];
		    	$univ = $data[5];

		    	if (0 == $loop) {
					if (!empty($tel)) {
						$fieldKey = $tel;
			    	} elseif(!empty($mail)) {
						$fieldKey = $mail;
			    	} elseif (!empty($name) && !empty($sexe)) {
			    		$fieldKey = md5($name.$sexe);
			    	}

					self::$_settings['first_list'][$fieldKey] = [$name, $sexe, $tel, $mail, $addr, $univ];
		    	} else {

		    		if (!count(self::$_settings['first_list'])) {
						$response['message'] = '请稍后再试一次.';
				        print_r(json_encode($response));
					    exit;
		    		}

		    		foreach(self::$_settings['first_list'] as $key => $perso) {
		    			if (((!empty($tel) && !empty($perso) && isset($perso[2])) && $tel == $perso[2])) {
		    				$perso['status'] = true;
							self::$_settings['last_list'][$tel] = $perso;
							unset(self::$_settings['first_list'][$key]);
		    			    break;
		    			} elseif(((!empty($mail) && !empty($perso) && isset($perso[3])) && $mail == $perso[3])){
							$perso['status'] = true;
		    				self::$_settings['last_list'][$mail] = $perso;
							unset(self::$_settings['first_list'][$key]);
		    			    break;
		    			} elseif(((!empty($name) && !empty($sexe) && !empty($perso) && isset($perso[0]) && isset($perso[1])) 
		    				 && md5($name.$sexe) == md5($perso[0].$perso[1]))) {
							$perso['status'] = true;
							self::$_settings['last_list'][md5($name.$sexe)] = $perso;
							unset(self::$_settings['first_list'][$key]);
		    			    break;
		    			}
		    		}
		    	}
			  }

			  $loop++;
			  fclose($handle);
		 }

		 $notcounted = count(self::$_settings['first_list']);

		 if ($notcounted) {
		 	foreach(self::$_settings['first_list'] as $key => $perso) {
				$perso['status'] = false;
				self::$_settings['last_list'][] = $perso;
		 	}
		 }

		$total = count(self::$_settings['last_list']);

		$response['error'] = false;
	    $response['message'] = '';
	    $response['not_counted'] = $notcounted;
	    $response['total_counted'] = $total;
	    $response['resultat'] = self::$_settings['last_list'];
	    print_r(json_encode($response));
		exit;
	}
}

Amour::run();

Amour::find();

Amour::lock();


