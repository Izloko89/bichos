<?php session_start();
include("datos.php");
header("Content-type: application/json");
$term=$_GET["id"];
try{
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
	//sacar los campos para acerlo más autoámtico
	$campos=array();
	
	$res=$bd->query("DESCRIBE eventos;");
	foreach($res->fetchAll(PDO::FETCH_ASSOC) as $a=>$c){
		$campos[$a]=$c["Field"];
	}
		$res=$bd->query("SELECT * FROM presupuesto 
				INNER JOIN eventos e on e.id_evento = presupuesto.id_evento WHERE  id_presupuesto = $term;");
	foreach($res->fetchAll(PDO::FETCH_ASSOC) as $i=>$v){
		$r[$i]["label"]=$v["nombre"];
		$r[$i]['folio']=$v["folio"];
		$r[$i]["paq_basico"] = $v["paq_basico"];
		foreach($campos as $campo){
			$r[$i][$campo]=$v[$campo];
		}
		$r[$i]["fechaevento"]=date("d/m/Y h:i a",strtotime($v["fechaevento"]));
		$r[$i]["fecha_sol"] = date("d/m/Y h:i a",strtotime($v["fecha_solicitud"]));
	}
	
	
}catch(PDOException $err){
	echo json_encode($err);
}
echo json_encode($r);
?>