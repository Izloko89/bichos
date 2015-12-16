<?php session_start();
header("content-type: application/json");
include("datos.php");

$paq=$_POST["paq"];
$art=$_POST["art"];
try{
	$bd=new PDO($dsnw,$userw,$passw,$optPDO);
	$sql="DELETE FROM paquetes_articulos WHERE id_articulo=$art AND id_paquete=$paq;";
	
	$bd->query($sql);
	$r["continuar"]=true;
}catch(PDOException $err){
	$r["continuar"]=false;
	$r["info"]="Error: ".$err->getMessage();
}

$bd=NULL;
echo json_encode($r);
?>