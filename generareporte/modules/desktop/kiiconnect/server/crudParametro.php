<?php
require_once '../../../../server/os.php';

$os = new os();
if(!$os->session_exists()){
	die('No existe sesión!');
}

function selectParametro() {
	global $os;
	$sql = "SELECT * FROM parametro";
	$result = $os->db->conn->query($sql);
	$data= array();
    while($row = $result->fetch(PDO::FETCH_ASSOC)){
        $data[] = $row;
    }
	echo json_encode(array(
		"success"=>true, 
		"data"=>$data)
	);
}

function insertParametro() {
	global $os;
	$data = json_decode(stripslashes($_POST["data"]));
	$sql = "INSERT INTO parametro(parametro,valor,valorcadena) values('$data->parametro','$data->valor',
	'$data->valorcadena');";
	$sql = $os->db->conn->prepare($sql);
	$sql->execute();
	echo json_encode(array(
		"success" => $sql->errorCode() == 0,
		"msg" => $sql->errorCode() == 0?"Parametro insertado exitosamente":$sql->errorCode(),
		"data" => array(
			array(
				"idparametro" => $os->db->conn->lastInsertId(),
				"parametro"	=> $data->parametro,
				"valor" => $data->valor,
				"valorcadena" => $data->valorcadena
            )
		)
	));
}

function updateParametro() {
	global $os;
	$data = json_decode(stripslashes($_POST["data"]));
	$sql = "UPDATE parametro SET parametro='$data->parametro',valor='$data->valor',valorcadena='$data->valorcadena'";
	$sql = $os->db->conn->prepare($sql);
	$sql->execute();
	echo json_encode(array(
		"success" => $sql->errorCode() == 0,
		"msg"	=> $sql->errorCode() == 0?"Nota de entrega actualizado exitosamente":$sql->errorCode()
	));
}

function deleteParametro() {
	global $os;
	$id = json_decode(stripslashes($_POST["data"]));
	$sql = "DELETE FROM parametro WHERE idparametro=$id";
	$sql = $os->db->conn->prepare($sql);
	$sql->execute();
	echo json_encode(array(
		"success" => $sql->errorCode() == 0,
		"msg"	=> $sql->errorCode() == 0?"Nota de entrega eliminado exitosamente":$sql->errorCode()
	));
}

switch ($_GET['operation']) {
	case 'select' : 
		selectParametro();
		break;
	case 'insert' :	
		insertParametro();
		break;	
	case 'update' :
		updateParametro();
		break;
	case 'delete' :	
		deleteParametro();
		break;
}
