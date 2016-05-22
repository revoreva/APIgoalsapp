<?php
	header( 'Access-Control-Allow-Origin: *');
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST))
    	$_POST = (array)json_decode(file_get_contents('php://input'), true);

	function base64_to_jpeg($base64_string, $output_file) {
	    $ifp = fopen($output_file, "wb"); 

	    $data = explode(',', $base64_string);

	    fwrite($ifp, base64_decode($data[1])); 
	    fclose($ifp);

	    return $output_file; 
	}
	
	if(!empty($_POST['TYPE'])){
		// koneksi database
	    $host = "localhost";        // lokasi database
	    $user = "revoreva_goalsap";             // nama username
	    $pass = "g04l54pp";                 // password database
	    $dbname = "revoreva_goalsapp";       // nama database yang digunakan
	    $cn = mysqli_connect( $host, $user, $pass, $dbname );
	    
	    //url/login/email/password
	    if ( $_POST['TYPE'] == "login" ) {
	    	$query = 	"SELECT
	    					AKUN_ID, EMAIL, USERNAME, REKENING, FOTO
		                from
		                 	AKUN
		                where
		                  AKUN.EMAIL = \"" . $_POST['EMAIL'] . "\" AND AKUN.PASSWORD = \"" . $_POST['PASSWORD'] . "\"
		                limit 1";
	    	
		    $result = mysqli_query( $cn, $query );
		    $var = array();
		    $ada = 0;
		    while ( $obj = mysqli_fetch_object( $result ) ) {
		    	$var[] = $obj;
		    	$ada = 1;
		    }
		    if($ada == 1)
		    	echo '{"status":200, "message":"login sukses", "data":' . json_encode( $var ) . '}';
		    else
		    	echo '{"status":300, "message":"login gagal"}';
	    } else if($_POST['TYPE'] == 'register'){
	    	//register/username/email/password/foto
	    	$foto = $_POST['FOTO'];
			$nama = uniqid().'.jpg';
			$img = base64_to_jpeg($foto, $nama);
			rename(''.$nama, 'uploads/'.$nama);
			$path = '/uploads/'.$img;
	    	$query = 	"INSERT INTO AKUN(USERNAME, EMAIL, PASSWORD, FOTO)
	    				VALUES (\"" . $_POST['USERNAME'] . "\",\"" . $_POST['EMAIL'] . "\",\"" . $_POST['PASSWORD'] . "\",\"" . "http://goalsapp.heliohost.org/" . $path . "\")";
        	
		    $result = mysqli_query( $cn, $query );
		    
		    if($result){
		    	$query = 	"SELECT
	    					AKUN_ID, EMAIL, USERNAME, REKENING, FOTO
		                from
		                 	AKUN
		                where
		                  AKUN.EMAIL = \"" . $_POST['EMAIL'] . "\"
		                limit 1";
	    	
			    $result = mysqli_query( $cn, $query );
			    $var = array();
			    $ada = 0;
			    while ( $obj = mysqli_fetch_object( $result ) ) {
			    	$var[] = $obj;
			    }
			    echo '{"status":200, "message":"registrasi sukses", "data":' . json_encode( $var ) . '}';
		    }
		    else
		    	echo '{"status":300, "message":"registrasi gagal"}';
	    } else if($_POST['TYPE'] == 'budget'){

	    	$query = "UPDATE AKUN SET REKENING = \"" . $_POST['REKENING'] . "\" WHERE AKUN_ID = " . $_POST['AKUN_ID'];
        	
		    $result = mysqli_query( $cn, $query );
		    
		    if($result){
			    echo '{"status":200, "message":"penambahan data keuangan sukses"}';
		    }
		    else
		    	echo '{"status":300, "message":"penambahan data keuangan gagal"}';
	    } else if($_POST['TYPE'] == 'list_target'){
	    	//list_target/AKUN_ID
	    	
	    	$query = 	"SELECT
	    					TARGET_ID, NAMA, FOTO, SALDO, HARGA, DUE_DATE
		                FROM
		                 	TARGET
		                WHERE
		                  	AKUN_ID = " . $_POST['AKUN_ID'];
	    	
		    $result = mysqli_query( $cn, $query );
		    $var = array();
		    $ada = 0;
		    while ( $obj = mysqli_fetch_object( $result ) ) {
		    	$var[] = $obj;
		    	$ada = 1;
		    }
		    if($ada == 1)
		    	echo '{"status":200, "message":"get list target sukses", "data":' . json_encode( $var ) . '}';
		    else
		    	echo '{"status":300, "message":"get list target gagal"}';
	    } else if($_POST['TYPE'] == 'add_goal'){
	    	//register/username/email/password/foto
	    	$foto = $_POST['FOTO'];
			$nama = uniqid().'.jpg';
			$img = base64_to_jpeg($foto, $nama);
			rename(''.$nama, 'uploads/'.$nama);
			$path = '/uploads/'.$img;
	    	$query = "INSERT INTO TARGET(AKUN_ID, NAMA, HARGA, DUE_DATE, FOTO) VALUES (" . $_POST['AKUN_ID'] . ",\"" . $_POST['NAMA'] . "\",\"" . $_POST['HARGA'] . "\",\"" . $_POST['DUE_DATE'] . "\",\"" . "http://goalsapp.heliohost.org/" . $path . "\")";
        	
		    $result = mysqli_query( $cn, $query );
		    
		    if($result){
		    	$query = 	"SELECT
	    					TARGET_ID, FOTO, SALDO, HARGA, DUE_DATE
		                FROM
		                 	TARGET
		                WHERE
		                  	AKUN_ID = " . $_POST['AKUN_ID'];
	    	
			    $result = mysqli_query( $cn, $query );
			    $var = array();
			    $ada = 0;
			    while ( $obj = mysqli_fetch_object( $result ) ) {
			    	$var[] = $obj;
			    }
			    echo '{"status":200, "message":"penambahan goal sukses", "data":' . json_encode( $var ) . '}';
		    }
		    else
		    	echo '{"status":300, "message":"penambahan goal gagal"}';
	    } else if($_POST['TYPE'] == 'add_nabung'){
	    	//ngurangi rekening akun, nambah saldo di target, 
	    	//INSERT INTO log_transaksi: akun_id, target_id, kategori_transaksi_id = 8, nama, jumlah, jenis = 1 (out), tanggal
	    	//UPDATE AKUN SET rekening = new amount
	    	//UPDATE TARGET SET SALDO = new amount
	    	$query = "INSERT INTO LOG_TRANSAKSI(AKUN_ID, TARGET_ID, KATEGORI_TRANSAKSI_ID, NAMA, JUMLAH, JENIS, TANGGAL) VALUES (" . $_POST['AKUN_ID'] . "," . $_POST['TARGET_ID'] . "," . "8" . ",\"" . $_POST['NAMA_BARANG'] . "\",\"" . $_POST['JUMLAH_NABUNG'] . "\"," . "1" . ",\"" . $_POST['TANGGAL'] . "\")";
        	
		    $result = mysqli_query( $cn, $query );
		    
		    if($result){
		    	$INITIAL_REKENING = $_POST['REKENING'];
		    	$NEW_AMOUNT = intval($_POST['REKENING']) - intval($_POST['JUMLAH_NABUNG']);
		    	$NEW_REKENING = $NEW_AMOUNT;
		    	$query = "UPDATE AKUN SET REKENING = \"" . $NEW_AMOUNT . "\" WHERE AKUN_ID = " . $_POST['AKUN_ID'];
    	
			    $result = mysqli_query( $cn, $query );
			    
			    if($result){
			    	$NEW_AMOUNT = intval($_POST['SALDO_AWAL']) + intval($_POST['JUMLAH_NABUNG']);
				    $query = "UPDATE TARGET SET SALDO = \"" . $NEW_AMOUNT . "\" WHERE TARGET_ID = " . $_POST['TARGET_ID'];
    	
				    $result = mysqli_query( $cn, $query );
				    
				    if($result){
					    $query = 	"SELECT
				    					TARGET_ID, FOTO, SALDO, HARGA, DUE_DATE
					                FROM
					                 	TARGET
					                WHERE
					                  	AKUN_ID = " . $_POST['AKUN_ID'];
				    	
						    $result = mysqli_query( $cn, $query );
						    $var = array();
						    $ada = 0;
						    while ( $obj = mysqli_fetch_object( $result ) ) {
						    	$var[] = $obj;
						    }
						    echo '{"status":200, "message":"menabung sukses", "rekening": "' . $NEW_REKENING . '", "data":' . json_encode( $var ) . '}';
				    }
				    else{
				    	$query = "UPDATE AKUN SET REKENING = \"" . $INITIAL_REKENING . "\" WHERE AKUN_ID = " . $_POST['AKUN_ID'];
			    		$result = mysqli_query( $cn, $query );
				    	echo '{"status":300, "message":"menabung gagal", "rekening":"' . $INITIAL_REKENING . '"}';
				    }
			    }
			    else
			    	echo '{"status":300, "message":"menabung gagal", "rekening":"' . $INITIAL_REKENING . '"}';
		    	// echo '{"status":200, "message":"get list target sukses", "data":' . json_encode( $var ) . '}';
		    }
		    else
		    	echo '{"status":300, "message":"menabung gagal", "rekening":"' . $INITIAL_REKENING . '"}';
	    } else if($_POST['TYPE'] == 'add_transaksi'){
	    	//ngurangi rekening akun, nambah saldo di target, 
	    	//INSERT INTO log_transaksi: akun_id, kategori_transaksi_id, nama, jumlah, jenis, tanggal
	    	//UPDATE AKUN SET rekening = new amount
	    	$query = "INSERT INTO LOG_TRANSAKSI(AKUN_ID, KATEGORI_TRANSAKSI_ID, NAMA, JUMLAH, JENIS, TANGGAL) VALUES (" . $_POST['AKUN_ID'] . "," . $_POST['KATEGORI_TRANSAKSI_ID'] . ",\"" . $_POST['NAMA'] . "\",\"" . $_POST['JUMLAH'] . "\"," . $_POST['JENIS'] . ",\"" . $_POST['TANGGAL'] . "\")";
        	
		    $result = mysqli_query( $cn, $query );
		    
		    if($result){
		    	$INITIAL_REKENING = $_POST['REKENING'];
		    	if($_POST['JENIS'] == '1' || ($_POST['JENIS'] == '0' && $_POST['KATEGORI_TRANSAKSI_ID'] == '15'))
		    		$NEW_AMOUNT = intval($_POST['REKENING']) - intval($_POST['JUMLAH']);
		    	else
		    		$NEW_AMOUNT = intval($_POST['REKENING']) + intval($_POST['JUMLAH']);
		    	$NEW_REKENING = $NEW_AMOUNT;
		    	$query = "UPDATE AKUN SET REKENING = \"" . $NEW_AMOUNT . "\" WHERE AKUN_ID = " . $_POST['AKUN_ID'];
    	
			    $result = mysqli_query( $cn, $query );
			    
			    if($result){
			    	echo '{"status":200, "message":"tambah data transaksi sukses", "rekening": "' . $NEW_REKENING . '"}';
			    }
			    else
			    	echo '{"status":300, "message":"tambah data transaksi gagal", "rekening":"' . $INITIAL_REKENING . '"}';
		    	// echo '{"status":200, "message":"get list target sukses", "data":' . json_encode( $var ) . '}';
		    }
		    else
		    	echo '{"status":300, "message":"tambah data transaksi gagal", "rekening":"' . $INITIAL_REKENING . '"}';
	    }
	} else {
		echo '{"status":404, "message":"get data gagal"}';
	}
?>
