<?php session_start();
//script para guardar articulos desde la tabla de articulos en eventos_articulos
include("datos.php");
include("funciones.php");
include("s_check_inv_compra.php");
header("Content-type: application/json");

$emp=$_SESSION["id_empresa"];
$id_item=$_POST["id_item"];
$cant=$_POST["cantidad"]; //cantidad
$precio=$_POST["precio"]; //precio
$total=$cant*$precio; //total
$folio=$_POST["folio"]; //evento
$art=$_POST["id_articulo"]; //articulo id
$paq=$_POST["id_paquete"]; //paquete id
$prov = $_POST["proveedor"];

//pendiente.- mover del almacén nuevos elementos
try{
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
	
	$sql="INSERT INTO 
				presupuesto_articulos (folio, id_articulo, cantidad, precio, total, proveedor)
			VALUES ($folio, $art, $cant, $precio, $total, '$prov');";
			$bd->query($sql);
			$id_item=$bd->lastInsertId();

	$r["id_item"]=$id_item;			
	$r["continuar"]=true;
	
}catch(PDOException $err){
	$r["continuar"]=false;
	$r["info"]="Error encontrado: ".$err->getMessage()." $sql";
}
//0084609

echo json_encode($r);

?>