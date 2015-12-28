<?php 
include("datos.php");
	unset($r);
	$id = $_POST["id"];	
	$bd=new PDO($dsnw, $userw, $passw, $optPDO);
	try
	{
		$sqlAfs="DELETE FROM presupuesto WHERE id_presupuesto = $id";
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