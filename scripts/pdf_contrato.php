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
if(isset($_GET["id_evento"])){
	$id=$_GET["id_evento"];
	$salon=$_GET["salon"];
	
	
	$varpath = "../img/logo.png";
	
	
	 if($salon=="CARACOL")
		{ 
		$varpath = "../img/caracol.png";
		} 
		 if($salon=="HORMIGA") { 
		$varpath = "../img/hormiga.png";
		}
		
	
	
	
}else
{
echo "no trae datos" . $id;
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
	try{
		$bd=new PDO($dsnw,$userw,$passw,$optPDO);
		// para saber los datos del cliente
		$sql="SELECT
			t1.id_evento,
			t1.fechaevento,
			t1.fechamontaje,
			t1.fechadesmont,
			t1.id_cliente,
			t1.nombre As nombreEvento,
			t1.edad,
			t1.personaje,
			t1.medio,
			t1.no_personas,
			t1.no_ninos,
			t1.no_adultos,
			t1.no_ninos_menu,
			t1.no_adultos_menu,
			t1.guarnicion,
			t1.botana,
			t1.pastel,
			t1.pinata,
			t1.centro_mesa,
			t1.invitaciones,
			t1.refrescos,
			t1.aguas,
			t1.promocion,
			t1.color_mantel,
			t1.servicios_extra,
			t2.nombre,
			t3.direccion,
			t3.colonia,
			t3.ciudad,
			t3.estado,
			t3.cp,
			t3.telefono,
			t3.celular,
			t3.email
		FROM eventos t1
		LEFT JOIN clientes t2 ON t1.id_cliente=t2.id_cliente
		LEFT JOIN clientes_contacto t3 ON t1.id_cliente=t3.id_cliente
		WHERE t1.id_evento=$eve;";
		$res=$bd->query($sql);
		$res=$res->fetchAll(PDO::FETCH_ASSOC);
		$evento=$res[0];
		$id_evento = $evento["id_evento"];
		$cliente=$evento["nombre"];
		$telCliente=$evento["telefono"];
		$celular=$evento["celular"];
		$email=$evento["email"];
		$domicilio=$evento["direccion"]." ".$evento["colonia"]." ".$evento["ciudad"]." ".$evento["estado"]." ".$evento["cp"];
		$fechaEve=$evento["fechaevento"];
		$fechaDesmont=$evento["fechadesmont"];
		$nombreEve=$evento["nombreEvento"];
		$edad=$evento["edad"];
		$personaje=$evento["personaje"];
		$medio=$evento["medio"];
		$no_invitados=$evento["no_personas"];
		$no_ninos=$evento["no_ninos"];
		$no_adultos=$evento["no_adultos"];
		$no_ninos_menu=$evento["no_ninos_menu"];
		$no_adultos_menu=$evento["no_adultos_menu"];
		$guarnicion=$evento["guarnicion"];
		$botana=$evento["botana"];
		$pastel=$evento["pastel"];
		$pinata=$evento["pinata"];
		$centro_mesa=$evento["centro_mesa"];
		$invitaciones=$evento["invitaciones"];
		$refrescos=$evento["refrescos"];
		$aguas=$evento["aguas"];
		$promocion=$evento["promocion"];
		$color_mantel=$evento["color_mantel"];
		$servicios_extra=$evento["servicios_extra"];
		
		$ano = substr($fechaEve,0,4);
		$mes= substr($fechaEve,5,2);
		$dia= substr($fechaEve,8,2);
		
		

		//para saber los articulos y paquetes
		$sql="SELECT
			t1.*,
			t2.nombre
		FROM eventos_articulos t1
		LEFT JOIN articulos t2 ON t1.id_articulo=t2.id_articulo
		WHERE t1.id_evento=$id_evento;";
		$res=$bd->query($sql);
		$articulos=array();
		foreach($res->fetchAll(PDO::FETCH_ASSOC) as $d){
			if($d["id_articulo"]!=""){
				$art=$d["id_item"];
				unset($d["id_item"]);
				$articulos[$art]=$d;
			}else{
				$art=$d["id_item"];
				unset($d["id_item"]);
				$articulos[$art]=$d;
				$paq=$d["id_paquete"];

				//nombre del paquete
				$sql="SELECT nombre FROM paquetes WHERE id_paquete=$paq;";
				$res3=$bd->query($sql);
				$res3=$res3->fetchAll(PDO::FETCH_ASSOC);
				$articulos[$art]["nombre"]="PAQ. ".$res3[0]["nombre"];

				$sql="SELECT
					t1.cantidad,
					t2.nombre
				FROM paquetes_articulos t1
				INNER JOIN articulos t2 ON t1.id_articulo=t2.id_articulo
				WHERE id_paquete=$paq AND t2.perece=0;";
				$res2=$bd->query($sql);

				foreach($res2->fetchAll(PDO::FETCH_ASSOC) as $dd){
					$dd["precio"]="";
					$dd["total"]="";
					$dd["nombre"]=$dd["cantidad"]." ".$dd["nombre"];
					$dd["cantidad"]="";
					$articulos[]=$dd;
				}
			}
		}
		//para saber el anticipo
		$emp_eve=$emp."_".$eve;
		$sql="SELECT SUM(cantidad) as pagado FROM eventos_pagos WHERE id_evento='$emp_eve';";
		$res=$bd->query($sql);
		$res=$res->fetchAll(PDO::FETCH_ASSOC);
		$pagado=$res[0]["pagado"];
		
		
		$sql="SELECT total FROM eventos_total WHERE id_evento=  '1_$id_evento';";
		$res=$bd->query($sql);
		$res=$res->fetchAll(PDO::FETCH_ASSOC);
		$porpagar=$res[0]["total"];
		
		
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
</style>
<table style="width:70%;border-bottom:<?php echo pxtomm(2); ?> solid #000;" cellpadding="0" cellspacing="0" border="">
    <tr>
	  <td valign="top" style=" text-align:left;">.</td>
      <td valign="top" style=";"><img src="../img/logo.png" style="width:60%;" />
      </td>
      <td valign="top"><img src="../img/salon_caracol.png" style="width:65%;" /> 
      </td>
      <td valign="top"><p>FOLIO NO. </p><p style="text-align:right; color:red;"><?php echo $eve; ?></p>
      </td>
    </tr>
</table>
<div style="width:100%; padding:5 20px; font-size:12px;">
	<table cellspacing="0" cellpadding="0" border="">
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Nombre del festejado:</strong> <?php echo $nombreEve ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;">
				<strong>Edad que cumple:</strong> <?php echo $edad ?> años
			</td>
			<td style="padding-bottom:10px; width:280px;">
				<strong>Personaje de la fiesta:</strong> <?php echo $personaje ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Nombre de papa o mama:</strong> <?php echo $cliente ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;">
				<strong>Tel&eacute;fono:</strong> <?php echo $telCliente ?>
			</td>
			<td style="padding-bottom:10px; width:280px;">
				<strong>Celular:</strong> <?php echo $celular ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Direcci&oacute;n:</strong> <?php echo $domicilio ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>E-mail:</strong> <?php echo $email ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Medio de publicidad:</strong> <?php echo $medio ?>
			</td>
		</tr>
	</table>
	<table cellspacing="0" cellpadding="0" border="">
		<tr>
			<td style="padding-bottom:20px; padding-top:10px;" colspan="3" align="center">
				<strong>Datos del Evento</strong>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:185px;">
				<strong>Num. de personas:</strong> <?php echo $no_invitados ?>
			</td>
			<td style="padding-bottom:10px; width:185px;">
				<strong>Niños:</strong> <?php echo $no_ninos ?>
			</td>
			<td style="padding-bottom:10px; width:185px;">
				<strong>Adultos:</strong> <?php echo $no_adultos ?>
			</td>
		</tr>
	</table>
	<table cellspacing="0" cellpadding="0" border="">
		<tr>
			<td style="padding-bottom:10px;" colspan="2" align="center">
				<strong>Men&uacute;</strong>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;">
				<strong>Niños:</strong> <?php echo $no_ninos_menu ?>
			</td>
			<td style="padding-bottom:10px; width:280px;">
				<strong>Adultos:</strong> <?php echo $no_adultos_menu ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Guarnici&oacute;n:</strong> <?php echo $guarnicion ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Botana:</strong> <?php echo $botana ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px;" colspan="2" align="center">
				<strong>Itinerario</strong>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Hora del Evento:</strong> <?php echo varHoraAbr($fechaEve) ?>
			</td>
		</tr>
		<tr>
			<td style="padding-top:30px; width:280px;" rowspan="5">
				<strong>Hora de cena:</strong> <?php echo varHoraAbr($hora_cena) ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:5px; width:280px;">
				<strong>Pastel:</strong> <?php echo $pastel ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:5px; width:280px;">
				<strong>Piñanta:</strong> <?php echo $pinata ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:5px; width:280px;">
				<strong>Centro de mesa:</strong> <?php echo $centro_mesa ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;">
				<strong>Invitaciones:</strong> <?php echo $invitaciones ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px;" colspan="2" align="center">
				<strong>Bebidas</strong>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Refrescos:</strong> <?php echo $refrescos ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Aguas frescas:</strong> <?php echo $aguas ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Promoci&oacute;n:</strong> <?php echo $promocion ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Color del mantel:</strong> <?php echo $color_mantel ?>
			</td>
		</tr>
		<tr>
			<td style="padding-bottom:10px; width:280px;" colspan="2">
				<strong>Servicios extras:</strong> <?php echo $servicios_extra ?>
			</td>
		</tr>
	</table>
	<br>
<div style="width:95%; padding 20px; font-size:12px; ">
	<P STYLE="margin-left: 1cm;"> TOTAL DEL EVENTO:</P>
	</DIV>
	<div style="width:95%; padding 20px; font-size:12px;  margin-left: 1cm;">
			<table align="center" border="0.3" cellspacing="0" cellpadding="0" style="width:100%;font-size:10px;margin-top:5px; padding:5 30px; text-align:center">
                <tr align="center">
                    <td style="width:15%;"><strong>CANT.</strong></td>
                    <td style="width:55%;"><strong>CONCEPTO</strong></td>
                    <td style="width:15%;"><strong>P.U.</strong></td>
                    <td style="width:15%;"><strong>IMPORTE</strong></td>
                </tr><?php //;
            $total=0;
            foreach($articulos as $id=>$d){ 
            $total+=$d["total"];
            //$html.= ?>
                <tr>
                    <td style="width:15%;text-align:center;"><?php echo $d["cantidad"] ?></td>
                    <td style="width:55%;"><?php echo $d["nombre"] ?></td>
                    <td style="width:15%;text-align:center;"><?php echo number_format($d["precio"],2) ?></td>
                    <td style="width:15%;text-align:right;"><?php echo number_format($d["total"],2) ?></td>
                </tr><?php //;
            } 
            //$html.= ?>
                <tr>
                    <td style="width:15%;text-align:center;"></td>
                    <td style="width:55%;"></td>
                    <td style="width:15%;text-align:right;">
                        <strong>Total:</strong>
                    </td>
                    <td style="width:15%;text-align:right;">
                        <strong><? echo number_format($total,2) ?></strong>
                    </td>
                </tr>
            </table>
	</div><br><br><br><br>
</div>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify;">
Contrato de arrendamiento que celebran por una parte <strong>BICHOS FIESTA S.A. DE CV.</strong> quien en lo sucesivo se le denominará arrendador,
quien señala como su domicilio en Blvd. de los caminos No.135 en la ciudad de Torreón,Coahuila y por la parte de la
Sr(a) <strong><?php echo  $cliente ?></strong> quien tiene su domicilio en <strong><?php echo  $domicilio ?></strong> y a quien en lo sucesivo se le denominará arrendatario, que se sujetan al tenor de las siguientes declaraciones y claúsulas.
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify;">
<strong>CLÁUSULAS</strong>
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify;">
<strong>1.- </strong>El arrendador da en arrendamiento al arrendatario, el salón de nombre BICHOS.
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify;">
<strong>2.- </strong>El arrendatario recibe el salón y el mobiliario sin ningún deterioro y a su entera satisfacción, obligandose para con el arrendador
a pagar y/o reponer en su caso por cualquier pérdida, robo o daño, que por motivo de la realización del evento pudiera ocurrirle al
equipo y/o al inmueble en su mención.
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify;">
<strong>3.- </strong>El término del presente contrato será de 4 (cuatro) horas, a partir de : <strong><?php echo varHoraAbr($fechaEve) ?></strong> a <strong><?php echo varHoraAbr($fechaDesmont) ?></strong> mismas que si se prorrogan
tendrán un costo de ________ cada hora o fracción.
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify;">
<strong>4.- </strong>La fecha de realización del evento es para el <?php echo varFechaAbr($fechaEve) ?>.
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify;">
<strong>5.- </strong>El precio que como contraperstación al uso y goce del salón "BICHOS" y que deberá pagar al arrendatario o el arrendador será
la cantidad de <strong><?php echo  $porpagar ?></strong> son:(______________________________) por salón,
pagaderos de la siguiente manera 50% anticipo por separacion y el 50% restante 15 dias hábiles antes de la realización del evento
en caso de incumplimiento, el arrendatario se sujetará a la pena establecida a la claúsula 6 bichos entregará al arrendatario el
recibo correspondiente a los anticipos.
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify;">
<strong>6.- </strong>En caso de que el arrendatario cambie la fecha de evento se le cobrará una penalización de $500 y por su cancelacion de evento
será de $1,500 la penalización. Cantidades que serán retenidas por daños y prejuicios.
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify;">
<strong>7.- </strong>
El arrendador podrá cancelar en cualquier momento el evento social por causa de fuerza mayor, para lo cual se obliga a devolver
al arrendatario el 100% del pago ya efectuado al día de la cancelación; o bien ofrecerá a este una nueva fecha disponible para
realizar en un futuro el evento en cuestión.
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify; align:center;">
<strong>OTROS SERVICIOS</strong>
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify; align:center;">
______________________________________________________________________________________
______________________________________________________________________________________
______________________________________________________________________________________
______________________________________________________________________________________
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify; align:center;">
<strong>8.-</strong>No se permite chicle en las instalaciónes.
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify; align:center;">
<strong>9.-</strong>El evento debe de estar cubierto en su totalidad 15 dias antes del evento.
</div>
<br>
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

$topdf=new HTML2PDF($orientar,array($mmCartaW,$mmCartaH),'es');
$topdf->writeHTML($html);
$topdf->Output();
//$path.$filename,'F'

//echo "http://".$_SERVER['HTTP_HOST']."/docs/".$filename;

?>