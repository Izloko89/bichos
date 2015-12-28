<?php session_start();
//script para guardar articulos desde la tabla de articulos en eventos_articulos
include("datos.php");

header("Content-type: application/json");


$folio=$_POST["folio"]; //evento


//pendiente.- mover del almacén nuevos elementos
try{
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
		
	$sql="SELECT SUM(total) as total FROM presupuesto_articulos where folio = $folio;";
	$res = $bd->query($sql);
	$res  = $res->fetch(PDO::FETCH_ASSOC);
	$r["total"] = $res["total"];
	

	
	$r["continuar"]=true;
	
}catch(PDOException $err){
	$r["continuar"]=false;
	$r["info"]="Error encontrado: ".$err->getMessage()." $sql";
}
//0084609

echo json_encode($r);

?>