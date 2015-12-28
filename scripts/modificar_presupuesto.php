<?php 
include("datos.php");
include("func_guardar.php");
	unset($r);
	$folio = $_POST["folio"];	
	$paq = $_POST["paq_basico"];
	$fecha = fixFecha($_POST["fechapresupuesto"]);
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
	try
	{
		$sqlAfs="UPDATE presupuesto SET paq_basico = $paq, fecha_solicitud = '$fecha'  WHERE folio = $folio";
		$res=$bd->query($sqlAfs);
		
		//$sqlAfs="DELETE FROM presupuesto_articulos WHERE id_presupuesto = $id";
		//$res=$bd->query($sqlAfs);

		$r["continuar"] = true;
		}
		catch(PDOException $err)
		{
			$r["continuar"]=false;
			$r["info"]="Error: ".$err->getMessage();
		}
		
	echo json_encode($r);
?>