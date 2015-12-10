<?php session_start();
include("datos.php");
header("Content-type: application/json");
$term=$_GET["term"];
try{
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
	$sql="SELECT 
		eventos.nombre as label,
		id_evento,
		eventos.id_empresa,
		eventos.id_usuario,
		eventos.id_cliente,
		eventos.personaje,
		eventos.edad,
		eventos.no_personas,
		eventos.no_ninos,
		eventos.no_adultos,
		eventos.medio,
		clientes.nombre as cliente_evento,
		eventos.clave,
		eventos.salon,
		eventos.eventosalon,
		eventos.nombre,
		tipo_evento.id_tipo,
		eventos.personaje,
		eventos.promocion,
		eventos.color_mantel,
		eventos.pinata,
		eventos.centro_mesa,
		eventos.invitaciones,
		eventos.servicios_extra,
		eventos.no_ninos_menu,
		eventos.no_adultos_menu,
		eventos.promocion,
		eventos.guarnicion,
		eventos.botana,
		eventos.aguas,
		eventos.refrescos,
		eventos.hora_cena,
		eventos.pastel,
		fechaevento,
		fechamontaje,
		fechadesmont,
		personaje
	FROM eventos
	INNER JOIN tipo_evento ON tipo_evento.id_tipo=eventos.id_tipo
	INNER JOIN clientes ON eventos.id_cliente=clientes.id_cliente 
	WHERE eventos.id_evento='$term';";
	
	$res=$bd->query($sql);
	$filas=$res->rowCount();
	$res=$res->fetchAll(PDO::FETCH_ASSOC);
	
	if($filas>0){
		//arreglar fechas
		if($res[0]["fechaevento"]!=0){
			$res[0]["fechaevento"]=date("d/m/Y h:i a",strtotime($res[0]["fechaevento"]));
		}
		if($res[0]["fechamontaje"]!=0){
			$res[0]["fechamontaje"]=date("d/m/Y h:i a",strtotime($res[0]["fechamontaje"]));
		}
		if($res[0]["fechadesmont"]!=0){
			$res[0]["fechadesmont"]=date("d/m/Y h:i a",strtotime($res[0]["fechadesmont"]));
		}
		//se escribe el row obtenido
		$res[0]["bool"]=true;
		echo json_encode($res[0]);
	}else{
		$res=array(
			"bool"=>false,
			"id_empresa"=>$_SESSION["id_empresa"],
			"id_usuario"=>$_SESSION["id_usuario"]
		);
		echo json_encode($res);
	}
	
}catch(PDOException $err){
	echo json_encode($err);
}

?>