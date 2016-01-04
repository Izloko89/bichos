<?php session_start();
setlocale(LC_ALL,"");
setlocale(LC_ALL,"es_MX");
include_once("datos.php");
require_once('../clases/html2pdf.class.php');
include_once("func_form.php");
$emp=$_SESSION["id_empresa"];

//funciones para convertir px->mm
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

$error="";
if(isset($_GET["id_evento"])){
    $eve=$_GET["id_evento"];
try{
	$bd=new PDO($dsnw,$userw,$passw,$optPDO);
	// para saber los datos del presupuesto
	$sql="SELECT 
		t1.id_evento,
		t1.fecha_solicitud,
		t1.folio,
		t1.paq_basico,
		t2.salon,
		t2.no_personas		
	FROM presupuesto t1
	LEFT JOIN eventos t2 ON t1.id_evento=t2.id_evento
	WHERE t1.id_evento=$eve;";
	$res=$bd->query($sql);
	$res1=$res->fetchAll(PDO::FETCH_ASSOC);
	$evento=$res1[0];
	$idPresupuesto=$evento["id_presupuesto"];
	$fechaSoli=$evento["fecha_solicitud"];
	$Folio=$evento["folio"];
	$paqBasico=$evento["paq_basico"];
	$NoPersonas = $evento["no_personas"];
	$salon = $evento["salon"];
	
}catch(PDOException $err){
	echo $err->getMessage();
}
$bd=NULL;
}
?>
<?php if($error==""){ $html='
<page backbottom="15px">
    <page_footer>
        <table border="0" cellpadding="0" cellspacing="0" style="font-size:13px; width:100%; margin-top:30px; padding:0 20px;">
            <tr>
                <td style="width:100%;vertical-align:top; text-align:center; border-top:'.pxtomm(2).' solid #484848;">
                    <p style="width:100%; text-align:center; margin:5px auto; font-size:10px; color:#484848">Eulogio Parra 2714 Providencia. ID Nextel 52*168895*1/52*148605*1	Tel / fax 3642-0913/ 04
                        <br/>
                        www.bichos.net
                    
                    </p>
                </td>
            </tr>
        </table>
    </page_footer>
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
        @font-face {
            font-family: "NombreFont";
            src:url(../css/Century_Gothic.ttf) format("truetype");
        }
        .div{
            color: #000;
            font-family: "NombreFont";
            font-size:12px;
        }
    </style>
	
    <table style="width:100%;" cellpadding="0" cellspacing="0">
        <tr>
            <td valign="top" style="width:20%; text-align:left;">
			    <div style="width:80%; background-color:#E1E1E1; font-weight:bold; text-align:center; padding-top:5px; padding-bottom:5px; font-size:12px;">No. de Contrato:</div>
				<div style="width:90%; font-size:12px; color:#C00; text-align:center;">'. folio(4,$Folio).'</div>
            </td>
            <td valign="top" style="width:60%; text-align:center; font-size:10px;">
                <img src="../img/logo.png" style="width:50%;" />
            </td>
            <td valign="top" style="width:20%; text-align:left;"></td>
        </tr>        
    </table>
	<br/>
	<table style="width:100%; font-size:10px" cellpadding="0" cellspacing="0" border="0.2" >
        <tr>
            <td valign="top" style="width:30%; text-align:left;">NOMBRE FESTEJADO:</td>
			<td valign="top" style="width:70%; text-align:left;"></td>
		</tr>
		<tr>
            <td valign="top" style="width:30%; ">PAQUETE BASICO:</td>
			<td valign="top" style="width:70%; "></td>
		</tr>
		<tr>
            <td valign="top" style="width:30%; text-align:left;">NIÑOS:</td>
			<td valign="top" style="width:70%; text-align:left;"></td>
        </tr>
		<tr>
            <td valign="top" style="width:30%; text-align:left;">FECHA:</td>
			<td valign="top" style="width:70%; text-align:left;">'.$fechaSoli.'</td>
        </tr>
		<tr>
            <td valign="top" style="width:30%; text-align:left;">REV.</td>
			<td valign="top" style="width:70%; text-align:left;"></td>
        </tr>        
    </table>
	
       <br/>
	   <table align="center" border="0.3" cellspacing="0" cellpadding="0" style="width:100%;font-size:10px;margin-top:5px; padding:5 30px; text-align:center">
                <tr align="center">
					<td style="width:10%;"><strong>CANTIDAD</strong></td>
                    <td style="width:30%;"><strong>ARTICULO</strong></td>					
					<td style="width:10%;"><strong>P.UNITARIO</strong></td>
					<td style="width:15%;"><strong>TOTAL</strong></td>
                </tr>';
				$total=0;
            foreach($articulos as $id=>$d){ 
            $total+=$d["total"];
            $html.='
                <tr>
				<td style="width:15%;">'.$d["nombre"].'</td>
                    <td style="width:10%;">'.$d["cantidad"].'</td>					
                    <td style="width:40%;text-align:center;">'. number_format($d["precio"],2).'</td>
                    <td style="width:10%;text-align:right;">'. number_format($d["total"],2).'</td>
                </tr>';
            } 
            $html.= '
                <tr>
                    <td style="width:15%;text-align:center;"></td>
                    <td style="width:10%;"></td>
                    <td style="width:40%;text-align:right;">
                        <strong>Total:</strong>
                    </td>
                    <td style="width:10%;text-align:right;">
                        <strong>'.number_format($total,2).'</strong>
                    </td>
                </tr>
            </table>
			
        <table border="0" cellpadding="0" cellspacing="0" style="font-size:13px; width:100%; margin-top:30px; padding:0 20px;">
            <tr>
                <td style="width:100%;vertical-align:top; text-align:center;">
                Atentamente                    
                    <br />______________________                    
                    <br />'.$_SESSION["usuario"].'    
                </td>
            </tr>
        </table>
    </page>';
	 }
	 else
	 {
		 echo $error;
		 }

$path='../docs/';
$filename="generador.pdf";
$orientar="portrait";

$topdf=new HTML2PDF($orientar,array($mmCartaW,$mmCartaH),'es');
$topdf->writeHTML($html);
$topdf->Output();

?>