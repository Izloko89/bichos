<?php session_start();
include("datos.php");
header("Content-type: application/json");
$term=$_GET["term"];
try{
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
	//sacar los campos para acerlo más autoámtico
	$campos=array();
	
	$res=$bd->query("DESCRIBE eventos;");
	foreach($res->fetchAll(PDO::FETCH_ASSOC) as $a=>$c){
		$campos[$a]=$c["Field"];
	}
		$res=$bd->query("SELECT * FROM presupuesto 
				INNER JOIN eventos e on e.id_evento = presupuesto.id_evento WHERE  nombre like '%$term%';");
	foreach($res->fetchAll(PDO::FETCH_ASSOC) as $i=>$v){
		$r[$i]["label"]=$v["nombre"];
		$r[$i]["id_presupuesto"] = $v["id_presupuesto"];
	}
	
	
}catch(PDOException $err){
	echo json_encode($err);
}
echo json_encode($r);
?>