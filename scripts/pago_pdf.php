<?php
setlocale(LC_ALL,"");
setlocale(LC_ALL,"es_MX");
include_once("datos.php");
require_once('../clases/html2pdf.class.php');
include_once("func_form.php");
$id = 0;
if(isset($_GET["idPagoPdf"])){
	$id=$_GET["idPagoPdf"];
}
$idEve = 0;
if(isset($_GET["idEve"])){
	$idEve=$_GET["idEve"];
	
}
$evento = $idEve; 
$idEve = substr($idEve,2,4); // solo un digito 1_9 -> 9
$pago = $_GET["pago"];
if(isset($_GET["pago"])){
	$pago=$_GET["pago"];
}

$cosas = "";
//funciones para convertir px->mm
function mmtopx($d){
	$fc=96/25;
	$n=$d*$fc;
	return $n."px";
}
function pxtomm($d){
	$fc=96/25;
	$n=$d/$fc;
	return $n."mm";
}
function checkmark(){
	$url="http://".$_SERVER["HTTP_HOST"]."/img/checkmark.png";
	$s='<img src="'.$url.'" style="height:10px;" />';
	return $s;
}

try{
	$sql="SELECT logo FROM empresas WHERE id_empresa=1;";
	$bd=new PDO($dsnw,$userw,$passw,$optPDO);
	$res=$bd->query($sql);
	$res=$res->fetchAll(PDO::FETCH_ASSOC);
	$logo='<img src="../'.$res[0]["logo"].'" width="189" />';
}catch(PDOException $err){
	echo "Error: ".$err->getMessage();
}

try{
	//id_evento id_cliente plazo fecha cantidad
	$sql="SELECT eventos_pagos.id_pago, clientes.nombre as cliente, clientes.id_cliente, eventos_pagos.id_cliente, eventos_pagos.plazo, eventos_pagos.fecha, eventos_pagos.cantidad, eventos_pagos.modo_pago, bancos.nombre as banco, usuarios.nombre as user FROM eventos_pagos	
	INNER JOIN clientes ON eventos_pagos.id_cliente = clientes.id_cliente	
	INNER JOIN bancos ON eventos_pagos.id_banco = bancos.id_banco	
	INNER JOIN usuarios ON eventos_pagos.id_cliente
	WHERE eventos_pagos.id_pago=$id;";
	$res=$bd->query($sql);
	$cosas=$res->fetchAll(PDO::FETCH_ASSOC);
	if(count($cosas) < 1)
	{ 
		$sql1="SELECT eventos_pagos.id_pago, clientes.nombre as cliente, clientes.id_cliente, eventos_pagos.id_cliente, eventos_pagos.plazo, eventos_pagos.fecha, eventos_pagos.cantidad, eventos_pagos.modo_pago, bancos.nombre as banco, usuarios.nombre as user FROM eventos_pagos	
	INNER JOIN clientes ON eventos_pagos.id_cliente = clientes.id_cliente	
	INNER JOIN bancos ON eventos_pagos.id_banco = bancos.id_banco	
	INNER JOIN usuarios ON eventos_pagos.id_cliente
	WHERE eventos_pagos.id_pago=$id;";
		$cosas=$res->fetchAll(PDO::FETCH_ASSOC);
	}
	//info eventos
	$sql2="SELECT t1.nombre, t1.fechaevento, t2.nombre AS tipo
	FROM eventos t1
	INNER JOIN tipo_evento t2 ON t1.id_evento =$idEve && t2.id_tipo = t1.id_tipo;";
	$res1=$bd->query($sql2);
	$info=$res1->fetchAll(PDO::FETCH_ASSOC);
	
	//info del cliente
	$idCliente = $cosas[0]["id_cliente"];
	$sql3="SELECT direccion FROM clientes_contacto WHERE id_cliente = $idCliente";
	$res2=$bd->query($sql3);
	$info_cliente=$res2->fetchAll(PDO::FETCH_ASSOC);
	
	//info total
	$sql4="SELECT total from eventos_total 
	WHERE id_evento = '$evento';";	
	$res3=$bd->query($sql4);
	$info_total=$res3->fetchAll(PDO::FETCH_ASSOC);
	
	$sql5="SELECT * from eventos_pagos
	WHERE id_evento = '$evento';";	
	$res4=$bd->query($sql5);
	foreach($res4->fetchAll(PDO::FETCH_ASSOC) as $d) {
		if($d["id_pago"]!=""){
			$depo=$d["id_evento"];
			unset($d["id_evento"]);
			$depositos[$depo] = $d;
		}
	}
	$suma_depositos = 0;
	foreach($depositos as $id => $d)
    	{
    		$suna_depositos+=$d["cantidad"]; 
    	}
    	$restante = $info_total[0]["total"] - $suma_depositos;
	
}catch(PDOException $err){
	echo "Error: ".$err->getMessage();
}
function folio($digitos,$folio){
	$usado=strlen($folio);
	$salida="";
	for($i=0;$i<($digitos-$usado);$i++){
		$salida.="0";
	}
	$salida.=$folio;
	return $salida;
}
//tamaño carta alto:279.4 ancho:215.9
$heightCarta=850;
$widthCarta=600;
$celdas=12;
$widthCell=$widthCarta/$celdas;
$mmCartaH=pxtomm($heightCarta);
$mmCartaW=pxtomm($widthCarta);

$hoy = getdate(); 
$dia_actual = date("j");

ob_start();
?>
	<style>
span{
	display:inline-block;
	padding:10px;
}
h1{
	font-size:20px;
}
.spacer{
	display:inline-block;
	height:1px;
}
</style>

<table style="width:100%;" cellpadding="0" cellspacing="0" >
    <tr>		 
      <td style="width:30%; text-align:left;">
       	   <p style="width:100%; padding:4px; margin:0; font-size:7px; text-align:center;">Blvd. de los caminos No.135<br/>Torreón,Coahuila<br/></p>
            
         </td>
         <td style="width:55%; text-align:center;"><img src="../img/logo.png" width="76%" height="60" /></td>
      <td style="width:15%; text-align:left; font-size:7px;">
         	<div style="width:100%; text-align:center; ">Recibo </div>
            <div style="width:100%; color:#C00; text-align:center;font-size:14px"><strong>N&ordm; &nbsp;<?php echo $id;?></strong></div>
         </td>
    </tr>
</table>

<table style="width:100%; margin-top:5px;">
<tr>
  <td valign="top" style="width:100%;">
    <table cellpadding="0" cellspacing="0" style=" font-size:9px;width:100%; padding:5px; padding-top:5px; padding-bottom:5px;border:0.5px solid #000; border-radius:20px;">
        <tr>
            <td height="10" style="width:100%; margin-left:5px; border-bottom:0.5px solid #000;"><strong>• Fecha</strong>  &nbsp;<?php echo $dia_actual."-".$hoy[mon]."-".$hoy[year]; ?></td>
        </tr><tr>
            <td height="10" style="width:100%; margin-left:5px; border-bottom:0.5px solid #000;"><strong>• Nombre</strong> &nbsp; <?php echo $cosas[0]["cliente"];?></td>            
        </tr><tr>
            <td height="10" style="width:100%; margin-left:5px; border-bottom:0.5px solid #000;"><strong>• Dirección</strong> &nbsp; <?php echo $info_cliente[0]["direccion"]; ?></td>
            </tr><tr>
            <td height="10" style="width:100%; "><strong>• Fecha del evento</strong> &nbsp; <?php echo substr($info[0]["fechaevento"],0,10); ?></td>
        </tr>
    </table>
</td>
</tr>
</table>
<table style="font-size:9px;width:100%; padding:10px; padding-top:5px; padding-bottom:5px; border:0.5px solid #000; border-radius:20px;" cellpadding="0" cellspacing="0" >
<tr>
		<td style="width:50%"><div style="width:100%; text-align:justify; padding-top:5px; padding-bottom:5px; font-size:10px;">• Por concepto de:&nbsp;<?php echo $info[0]["nombre"]; ?></div></td>
		<td style="width:50%"><div style="width:100%; font-size:9px; text-align:center">
			<?php echo $pago == 0 ? "Anticipo":"Pago $pago" ?></div>			
		</td>
	</tr>
	<tr>
		<td style="width:50%"><div style="width:100%; text-align:justify; padding-top:5px; padding-bottom:5px; font-size:10px;">• Tipo:&nbsp;<?php echo $info[0]["tipo"]; ?></div></td>
		<td style="width:50%"><div style="width:100%; font-size:9px; text-align:center">
			&nbsp;</div>			
		</td>
	</tr>
	<tr>
		<td style="width:50%"><div style="width:100%; text-align:justify; padding-top:5px; padding-bottom:5px; font-size:10px;">• Total:&nbsp;<?php echo $info_total[0]["total"]; ?></div></td>
		<td style="width:50%"><div style="width:100%; font-size:9px; text-align:center">
			&nbsp;</div>			
		</td>
	</tr>
    <tr><td height="120" style="border-bottom:0.5px solid #000;">&nbsp;</td>
    <td style="width:50%; padding-bottom:5px;border-bottom:0.5px solid #000;">&nbsp;</td>    
    </tr>  
	<tr>		
		<td style="width:50%; padding-bottom:5px;border-bottom:0.5px solid #000;">• La cantidad de: $<?php echo number_format($cosas[0]["cantidad"],2);?></td>
		<td style="width:50%; padding-bottom:5px;border-bottom:0.5px solid #000;">• Restante: $<?php echo $restante; ?></td>
	</tr>	
	<tr>
		<td style="width:50%;">• Forma de pago:&nbsp;<?php echo $cosas[0]["modo_pago"];?></td>	
		<td style="width:50%"><div style="width:100%; font-size:9px; text-align:center">
			<input name="fpago" type="checkbox" value="anticipo"/>cheque <a>No. cheque</a>
			<input name="fpago" type="checkbox" value="Saldo"/>efectivo
			</div>			
		</td>	
	</tr>
	<?php if(isset($cosas[0]["banco"])){?>
	<tr>
		<!--<td style="width:20%"><div style="width:100%; background-color:#E1E1E1; font-weight:bold; text-align:center; padding-top:5px; padding-bottom:5px; font-size:12px;">Banco:</div></td>
		<td style="width:20%"><div style="width:100%; font-size:12px; text-align:center; border-bottom:1px solid #000;"><?php echo $cosas[0]["banco"];?></div></td>-->
	</tr>
	<?php }?>
</table>
<table border="0" cellpadding="0" cellspacing="0" style="font-size:9px; width:100%;">
	<tr>
	  <td style="width:50%; text-align:center;">
      <br/><br/><br/>
			__________________________
            <br />Nombre de quien recibe<br/><?php echo $cosas[0]["user"]; ?></td>
            <td style="width:50%; text-align:center;">
             <br/><br/><br/>
			_________________________
            <br />Firma
            </td>
	</tr>
</table>
<?php
$html=ob_get_clean();
$path='../docs/';
$filename="generador.pdf";
//$filename=$_POST["nombre"].".pdf";

//configurar la pagina
//$orientar=$_POST["orientar"];
$orientar="portrait";

$topdf=new HTML2PDF($orientar,array($mmCartaW,$mmCartaH),'es');
$topdf->writeHTML($html);
$topdf->Output();
//$path.$filename,'F'

//echo "http://".$_SERVER['HTTP_HOST']."/docs/".$filename;

?>