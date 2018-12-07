<?php
 
Class fileUpload {
	private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
	private $table = DB_TABLE;

    private $dbh;
    private $error = array();
	private $info = array();
	private $ids = array();
	private $obj;

    private $stmt;

	private $mtype;

	private $folder = F_PATH;
	private $htaccess = H_FILE;


	/* Set up a PDO instance */
	public function __construct(){
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT    => true,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
        );
        // Create a new PDO instance
        try{
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        }
        // Catch any errors
        catch(PDOException $e){
            array_push($this->error, $e->getMessage());
			$this->obj->error = $this->error;
			return $this->obj;
        }

		$this->obj = new StdClass;
    }

	/* Custom bindParam function */
	private function bind($param, $value, $type = null){
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

	/* Checks if the table already exists. If not, creates one */
	private function createTable(){
		// Check if table already exists
		$this->stmt = $this->dbh->prepare("SHOW TABLES LIKE '". DB_TABLE ."'");
		
		try{
			$this->stmt->execute();
		}
        catch(PDOException $e){
            array_push($this->error, $e->getMessage());
			return false;
        }

		$cnt = $this->stmt->rowCount();

		if($cnt > 0){
			return true;
		} else {
			// Create table
			$this->stmt = $this->dbh->prepare("
				CREATE TABLE `". DB_TABLE ."` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`name` VARCHAR(64) NOT NULL,
					`original_name` VARCHAR(64) NOT NULL,
					`mime_type` VARCHAR(20) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;");
			try{
				$this->stmt->execute();
				return true;
			}
			catch(PDOException $e){
				array_push($this->error, $e->getMessage());
				return false;
			}
		}
	}

	/* Checks if the htaccess file exists. If not, creates one */
	private function createHtaccess(){
		if (!file_exists($this->folder."/.htaccess")){
			try {
				$file = fopen($this->folder."/.htaccess","w");
				$txt = "order deny,allow\n";
				$txt .= "deny from all\n";
				$txt .="allow from 127.0.0.1";
				fwrite($file, $txt);
				fclose($file);
				return true;
			} catch (Exception $e) {
				return false;
			}
		} else {
			return true;
		}
	}

	/* Checks if required PHP extensions are loaded. Tries to load them if not */
	private function check_phpExt(){
		if (!extension_loaded('fileinfo')) {
			// dl() is disabled in the PHP-FPM since php7 so we check if it's available first
			if(function_exists('dl')){
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					if (!dl('fileinfo.dll')) {
						return false;
					} else {
						return true;
					}
				} else {
					if (!dl('fileinfo.so')) {
						return false;
					} else {
						return true;
					}
				}
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	/* Creates a file with a random name */
	private function tempnam_sfx($path, $suffix){
		do {
            $file = $path."/".mt_rand().$suffix;
            $fp = @fopen($file, 'x');
        }
        while(!$fp);

        fclose($fp);
        return $file;
	}

	/* Checks the true mime type of the given file */
	private function check_img_mime($tmpname){
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$mtype = finfo_file( $finfo, $tmpname );
		$this->mtype = $mtype;
		if(strpos($mtype, 'image/') === 0){
			return true;
		} else {
			return true;
		}
		finfo_close( $finfo );
	}

	/* Checks if the file isn't to large */
	private function check_img_size($tmpname, $username){

		require_once 'db_connect.php';
		$db = db_connect();
		$query = "SELECT * FROM space_limit WHERE type='item'";
		$results = mysqli_query($db, $query);
		  	
		if (mysqli_num_rows($results) == 1) {
			$row = mysqli_fetch_assoc($results);
			$F_SIZE = $row["size"]; 
		} else {
			$msg = 'item/file size can not be fetched';
			echo '<script type="text/javascript">alert("' . $msg . '")</script>';
			//exit(0);
		}


		$size_conf = substr($F_SIZE, -1);
		$max_size = (int)substr($F_SIZE, 0, -1);

		switch($size_conf){
			case 'k':
			case 'K':
				$max_size *= 1024;
				break;
			case 'm':
			case 'M':
				$max_size *= 1024;
				$max_size *= 1024;
				break;
			default:
				$max_size = 1024000;
		}

		if(filesize($tmpname) > $max_size){
			return 0;
		} else { //check max. limit per user
			$db = db_connect();
			$query = "SELECT perUser FROM (SELECT i_creator, sum(filesize) AS perUser FROM `items` group by i_creator) as getUserLimit WHERE i_creator='".$username."'";
			$results = mysqli_query($db, $query);
			if (mysqli_num_rows($results) == 1) {
				$row = mysqli_fetch_assoc($results);
				$UserUsage = (double)$row['perUser'] + filesize($tmpname); 

				$query = "SELECT * FROM space_limit WHERE type='user'";
				$results = mysqli_query($db, $query);
				if (mysqli_num_rows($results) == 1) {
					$row = mysqli_fetch_assoc($results);
					$U_SIZE = $row["size"]; 

					$size_conf = substr($U_SIZE, -1);
					$max_size = (int)substr($U_SIZE, 0, -1);

					switch($size_conf){
						case 'k':
						case 'K':
							$max_size *= 1024;
							break;
						case 'm':
						case 'M':
							$max_size *= 1024;
							$max_size *= 1024;
							break;
						default:
							$max_size = 1024000;
					}
					if($UserUsage > $max_size){
						return 1;
					} else { //check max. limit per group
						$db = db_connect();
						$sql = "SELECT `groupname` FROM `users` WHERE username='".$username."'";
						$result = mysqli_query($db, $sql);
						if (mysqli_num_rows($result) > 0) {
							$row = mysqli_fetch_assoc($result);
						}
						$query = "SELECT groupname, sum(filesize) AS perGroup FROM ( (SELECT * FROM `items`, (SELECT `username`, `groupname` FROM `users` WHERE `groupname`='".$row['groupname']."' AND NOT role='1') AS GetUser WHERE items.i_creator = GetUser.username ORDER by id) AS tmpTBL)";
						$results = mysqli_query($db, $query);
						if (mysqli_num_rows($results) == 1) {
							$row = mysqli_fetch_assoc($results);
							$GroupUsage = (double)$row['perGroup'] + filesize($tmpname); 

							$query = "SELECT * FROM space_limit WHERE type='group'";
							$results = mysqli_query($db, $query);
							if (mysqli_num_rows($results) == 1) {
								$row = mysqli_fetch_assoc($results);
								$G_SIZE = $row["size"]; 

								$size_conf = substr($G_SIZE, -1);
								$max_size = (int)substr($G_SIZE, 0, -1);

								switch($size_conf){
									case 'k':
									case 'K':
										$max_size *= 1024;
										break;
									case 'm':
									case 'M':
										$max_size *= 1024;
										$max_size *= 1024;
										break;
									default:
										$max_size = 1024000;
								}
								if($GroupUsage > $max_size){
									return 2;
								} else {
									return 3;
								}
							}
						}
					}
				} else {
					$msg = 'item/file size can not be fetched';
					echo '<script type="text/javascript">alert("' . $msg . '")</script>';
					//exit(0);
				}

			} else {
				$msg = 'Total usage size per user can not be fetched';
				echo '<script type="text/javascript">alert("' . $msg . '")</script>';
				//exit(0);
			}
			
			return true;
		}
	}

	/* Re-arranges the $_FILES array */
	private function reArrayFiles($files){
		$file_ary = array();
		$file_count = count($files['name']);
		$file_keys = array_keys($files);

		for ($i=0; $i<$file_count; $i++) {
			foreach ($file_keys as $key) {
				$file_ary[$i][$key] = $files[$key][$i];
			}
		}

		return $file_ary;
	}

	/* Handles the uploading of images */
	public function uploadImages($files, $username){
		// Checks if the required PHP extension(s) are loaded
		if($this->check_phpExt()){
			// Checks if db table exists. Creates it if nessesary
			if($this->createTable()){
				// Checks if a htaccess file should be created and creates one if needed
				if($this->htaccess){
					if(!$this->createHtaccess()){
						array_push($this->error, "Unable to create htaccess file.");
						$this->obj->error = $this->error;
						return $this->obj;
					}
				}
				
				// Re-arranges the $_FILES array
				$files = $this->reArrayFiles($files);
				foreach($files as $file){
					// Checks if $file['tmp_name'] is empty. This occurs when a file is bigger than allowed by the 'post_max_size' and/or 'upload_max_filesize' settings in php.ini
					if(!empty($file['tmp_name'])){
						// Checks the true MIME type of the file
						if($this->check_img_mime($file['tmp_name'])){
							// Checks the size of the the image
							if($this->check_img_size($file['tmp_name'], $username)==3){
								// Creates a file in the upload directory with a random name
								$uploadfile = $this->tempnam_sfx($this->folder, ".tmp");
								
								// Moves the image to the created file
								if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
									// Inserts the file data into the db
									$this->stmt = $this->dbh->prepare("INSERT INTO ". DB_TABLE ." (name, original_name, mime_type, filesize, i_name, i_creator, i_description, i_created, i_accessed) VALUES (:name, :oriname, :mime,".filesize($file['tmp_name']).", '', '".$username."', '', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
									
									$this->bind(':name', basename($uploadfile));
									$this->bind(':oriname', basename($file['name']));
									$this->bind(':mime', $this->mtype);

									try{
										$this->stmt->execute();
									}
									catch(PDOException $e){
										array_push($this->error, $e->getMessage());
										$this->obj->error = $this->error;
										return $this->obj;
									}
									
									array_push($this->ids, $this->dbh->lastInsertId());
									array_push($this->info, "File: ". $file['name'] ." was succesfully uploaded!");

									continue;
								} else {
									unlink($file['tmp_name']);
									array_push($this->info, "Unable to move file: ". $file['name'] ." to target folder. The file is removed!");
								}
							} else {
								if($this->check_img_size($file['tmp_name'], $username)==0) {
									array_push($this->info, "File: '". $file['name'] ."' exceeds the maximum file size limit. The file is removed!");
								} else {
									if($this->check_img_size($file['tmp_name'], $username)==1) {
										array_push($this->info, "File: '". $file['name'] ."' exceeds the maximum size per User. The file is removed!");
									} else {
										array_push($this->info, "File: '". $file['name'] ."' exceeds the maximum size per Group. The file is removed!");
									}
								}
							}
						/*} else {
							unlink($file['tmp_name']);
							array_push($this->info, "File: ". $file['name'] ." is not an image. The file is removed!");
							*/
						} 
					} else {
						array_push($this->info, "No files have been selected!");
					}
				}
				// Checks if the error array is empty
				foreach ($this->error as $key => $value) {
					if (empty($value)) {
					   unset($this->error[$key]);
					}
				}
				if (empty($this->error)) {

					$this->obj->info = $this->info;
					$this->obj->ids = $this->ids;
					
					return $this->obj;
				} else {
					$this->error = array_unique($this->error);
					$this->obj->error = $this->error;
					return $this->obj;
				}
			} else {
				if($this->error !== NULL){
					$this->obj->error = $this->error;
					return $this->obj;
				} else {
					// This should never happen, but it's here just in case
					array_push($this->error, "Unknown error! Failed to load ImageUpload class!");
					$this->obj->error = $this->error;
					return $this->obj;
				}
			}
		} else {
			array_push($this->error, "The PHP fileinfo extension isn't loaded and ImageUpload was unable to load it for you.");
			$this->obj->error = $this->error;
			return $this->obj;
		}
	}

	/* Show the image in the browser */
	public function showImage($id){
		$this->stmt = $this->dbh->prepare("SELECT name, original_name, mime_type FROM ". DB_TABLE ." WHERE id=:id");

		$this->bind(':id', $id);

		try{
			$this->stmt->execute();
			$result = $this->stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch(PDOException $e){
			array_push($this->error, $e->getMessage());
			$this->obj->error = $this->error;
			return $this->obj;
		}
		$msg = ''.$result['name'];
		//echo '<script type="text/javascript">alert("' . $msg . '")</script>';
		//$newfile = $result['original_name'];

		/* Send headers and file to user for display */
		header("Content-Type: " . $result['mime_type']);
		readfile(F_PATH.'/'.$result['name']);
		

	}

	/* Force a download of the image */
	public function downloadImage($id){
		$this->stmt = $this->dbh->prepare("SELECT name, original_name, mime_type FROM ". DB_TABLE ." WHERE id=:id");

		$this->bind(':id', $id);

		try{
			$this->stmt->execute();
			$result = $this->stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch(PDOException $e){
			array_push($this->error, $e->getMessage());
			$this->obj->error = $this->error;
			return $this->obj;
		}

		$newfile = $result['original_name'];

		/* Send headers and file to visitor for download */
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename='.basename($newfile));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize(F_PATH.'/'.$result['name']));
		header("Content-Type: " . $result['mime_type']);
		readfile(F_PATH.'/'.$result['name']);
	}

	/* Delete an image */
	public function deleteImage($id){
		$this->stmt = $this->dbh->prepare("SELECT name, original_name, FROM ". DB_TABLE ." WHERE id=:id");

		$this->bind(':id', $id);

		try{
			$this->stmt->execute();
			$result = $this->stmt->fetch(PDO::FETCH_ASSOC);
		}
		catch(PDOException $e){
			array_push($this->error, $e->getMessage());
			$this->obj->error = $this->error;
			return $this->obj;
		}

		unlink(F_PATH.'/'.$result['name']);

		$this->stmt = $this->dbh->prepare("DELETE FROM ". DB_TABLE ." WHERE id=:id");

		$this->bind(':id', $id);

		try{
			$this->stmt->execute();
		}
		catch(PDOException $e){
			array_push($this->error, $e->getMessage());
			$this->obj->error = $this->error;
			return $this->obj;
		}

		array_push($this->info, "File: ". $result['original_name'] ." successfully deleted.");
		$this->obj->info = $this->info;
		return $this->obj;
	}
}

?>
