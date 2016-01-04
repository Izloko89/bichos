<?php session_start();
setlocale(LC_ALL,"");
setlocale(LC_ALL,"es_MX");
include_once("datos.php");
require_once('../clases/html2pdf.class.php');
include_once("func_form.php");
$emp=$_SESSION["id_empresa"];

//funciones para usarse dentro de los pdfs
function mmtopx($d){
	$fc=96/25.4;
	$n=$d*$fc;
	return $n."px";
}
function pxtomm($d){
	$fc=96/25.4;
	$n=$d/$fc;
	return $n."mm";
}
function checkmark(){
	$url="http://".$_SERVER["HTTP_HOST"]."/img/checkmark.png";
	$s='<img src="'.$url.'" style="height:10px;" />';
	return $s;
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
$heightCarta=960;
$widthCarta=660;
$celdas=12;
$widthCell=$widthCarta/$celdas;
$mmCartaH=pxtomm($heightCarta);
$mmCartaW=pxtomm($widthCarta);
ob_start();

//sacar los datos del cliente
$error="";
if(isset($_GET["id_evento"])){
	$obs=$_GET["obs"];
	$eve=$_GET["id_evento"];
	$salon=$_GET["salon"];
	$ninos=$_GET["ninos"];
	$adultos=$_GET["adultos"];
	$paquete=$_GET["paquete"];
	$elaborado=$_GET["elaborado"];
	$folio=$_GET["folio"];

	try{
		$bd=new PDO($dsnw,$userw,$passw,$optPDO);
		// para saber los articulos
		$sql="SELECT t1.cantidad, t1.precio, t1.total, t2.nombre, t2.unidades FROM eventos_articulos t1 INNER JOIN articulos t2 ON t1.id_evento=$eve && t2.id_articulo = t1.id_articulo;";
		$res=$bd->query($sql);
		$articulos=array();
		foreach($res->fetchAll(PDO::FETCH_ASSOC) as $d){
			if($d["id_articulo"]!=""){
		                $art=$d["id_item"];
		                unset($d["id_item"]);
		                $articulos[$art]=$d; //pasa articulos a un array
		        }
		}
		
		// para saber los proveedores
		$sql1="SELECT proveedor from presupuesto_articulos where folio = $folio";
		$res1=$bd->query($sql1);
		$proveedores=array();		
		foreach($res1->fetchAll(PDO::FETCH_ASSOC) as $d) {
			if($d["proveedor"]!=""){
			
			}
		}
	}catch(PDOException $err){
		$error= $err->getMessage();
	}
}

?>
<?php if($error==""){ ?>
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
td{
	background-color:#FFF;
}
th{
	color:#FFF;
	text-align:center;
}
p {
	margin: 0;
	padding: 0;
}
.float {
	float: left;
}
</style>
<table style="width:70%;" cellpadding="0" cellspacing="0" border="">
    <tr>
	  <td valign="top" style="width:30% text-align:left;"><img src='../img/logo.png' style='width:55%; margin-right:30px;' /></td>
          <td valign="top" style="width:40%; text-align:center;">Salon <?php echo $salon ?><br/><br/><br/>PRESUPUESTO </td>
          <td valign="top" style="width:30%; text-align:left;">NUM. DE CONTRATO: <br/>NOMBRE DE FESTEJADO: <br/>PAQUETE BASICO: <?php echo $paquete?> <br/>NIÑOS: <?php echo $ninos; ?>&nbsp; ADULTOS: <?php echo $adultos; ?><br/>FECHA: </td>      
    </tr>
</table>
<br/><br/><br/>
<table style="width:70%;" cellpadding="0" cellspacing="0" border="1">
    <tr>
	<td style="text-align:center">PROVEEDOR</td>
	<td style="text-align:center">DESCRIPCION</td>
	<td style="text-align:center">UNIDAD</td>
	<td style="text-align:center">CANTIDAD</td>
	<td style="text-align:center">P.UNITARIO</td>
	<td style="text-align:center">P.TOTAL</td>
	<td style="text-align:center">OBSERVACIONES</td>   
    </tr> 
    <tr>
    	<td style="text-align:center;" colspan="7">SOLICITUD PRESUPUESTO PARA EVENTO</td>   
    </tr>  
    <tr>
    <?php 
    	$total=0;
    	$contador=1;
    	foreach($articulos as $id => $d)
    	{
    		$total+=$d["total"]; ?>
    <td style="text-align:center"><?php echo $contador; ?></td>
    <td style="text-align:center">&nbsp;</td>
    <td style="text-align:center"><?php echo $d["unidades"]; ?></td>
    <td style="text-align:center"><?php echo $d["cantidad"]; ?></td>
    <td style="text-align:center"><?php echo $d["precio"]; ?></td>
    <td style="text-align:center"><?php echo $d["total"]; ?></td>
    <td style="text-align:center">&nbsp;</td>
    <?php }   ?>    	
    </tr> 
</table>
<br/><br/><br/>
<table style="width:70%;" cellpadding="0" cellspacing="0" border="0">
    <tr>
	<td valign="top" style="width:25%; text-align:center;">
		<br/><br/><br/>____________________________<br/>ORGANIZADORA<br/>
	</td>
	<td valign="top" style="width:50%; text-align:center;">Elaborado <?php echo $elaborado; ?></td>
	<td valign="top" style="width:25%; text-align:center;">
		<br/><br/><br/>____________________________<br/>COCINERA<br/>
	</td>
    </tr>   
    <tr>
	<td valign="top" style="width:25%; text-align:center;">
		
	</td>
	<td valign="top" style="width:50%; text-align:center;">
		<br/><br/><br/>____________________________<br/>ADMINISTRADORA<br/>
	</td>
	<td valign="top" style="width:25%; text-align:center;"></td>
    </tr>   
     <tr>
	<td valign="top" style="width:25%; text-align:center;">
		
	</td>
	<td valign="top" style="width:50%; text-align:center;">
		<br/><br/><br/>____________________________<br/>AUTORIZO<br/>
	</td>
	<td valign="top" style="width:25%; text-align:center;"></td>
    </tr>   
</table>
<?php }else{
	echo $error;
}?>
<?php
$html=ob_get_clean();
$path='../docs/';
$filename="generador.pdf";
//$filename=$_POST["nombre"].".pdf";

//configurar la pagina
//$orientar=$_POST["orientar"];
$orientar="portrait";

//echo $html;
$topdf=new HTML2PDF($orientar,array($mmCartaW,$mmCartaH),'es');
$topdf->writeHTML($html);
$topdf->Output();
//$path.$filename,'F'

//echo "http://".$_SERVER['HTTP_HOST']."/docs/".$filename;

?>