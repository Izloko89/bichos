<?php session_start();
include("datos.php");
include("func_guardar.php");
header("Content-type: application/json");
$evento=$_POST["evento"];
$fechasol = $_POST["fechasol"];
$paq = $_POST["paq"];
$folio = $_POST["folio"];
$fechasol = fixFecha($fechasol);
try{
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
	$bd->query("INSERT INTO presupuesto (id_evento,folio,fecha_solicitud,paq_basico) values ($evento,$folio,'$fechasol',$paq );");
	$r["continuar"]=true;
	
}catch(PDOException $err){
	echo json_encode($err);
}
echo json_encode($r);
?>