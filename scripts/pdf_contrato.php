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
			t1.salon,
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
		//$salon=$evento["salon"];
		
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
				WHERE id_paquete=$paq ;";
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

		//para los salones
		$sql="SELECT salon FROM eventos;";
		$res=$bd->query($sql);
		$res=$res->fetchAll(PDO::FETCH_ASSOC);
		//$salon=$res[0]["salon"];
		
		
	}catch(PDOException $err){
		$error= $err->getMessage();
	}
}

//------    CONVERTIR NUMEROS A LETRAS         ---------------
    //------    Máxima cifra soportada: 18 dígitos con 2 decimales
    //------    999,999,999,999,999,999.99
    // NOVECIENTOS NOVENTA Y NUEVE MIL NOVECIENTOS NOVENTA Y NUEVE BILLONES
    // NOVECIENTOS NOVENTA Y NUEVE MIL NOVECIENTOS NOVENTA Y NUEVE MILLONES
    // NOVECIENTOS NOVENTA Y NUEVE MIL NOVECIENTOS NOVENTA Y NUEVE PESOS 99/100 M.N.
    //------    Creada por:                        ---------------
    //------             Cristian Arellano    ---------------
    //------    03 de Noviembre de 2015. Monterrey, N.L.  ---------------
    function numtoletras($xcifra)
{
    $xarray = array(0 => "Cero",
                    1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE",
                    "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE",
                    "VEINTI", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA",
                    100 => "CIENTO", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS"
                   );
    //
    $xcifra = trim($xcifra);
    $xlength = strlen($xcifra);
    $xpos_punto = strpos($xcifra, ".");
    $xaux_int = $xcifra;
    $xdecimales = "00";
    if (!($xpos_punto === false)) {
        if ($xpos_punto == 0) {
            $xcifra = "0" . $xcifra;
            $xpos_punto = strpos($xcifra, ".");
        }
        $xaux_int = substr($xcifra, 0, $xpos_punto); // obtengo el entero de la cifra a covertir
        $xdecimales = substr($xcifra . "00", $xpos_punto + 1, 2); // obtengo los valores decimales
    }

    $XAUX = str_pad($xaux_int, 18, " ", STR_PAD_LEFT); // ajusto la longitud de la cifra, para que sea divisible por centenas de miles (grupos de 6)
    $xcadena = "";
    for ($xz = 0; $xz < 3; $xz++) {
        $xaux = substr($XAUX, $xz * 6, 6);
        $xi = 0;
        $xlimite = 6; // inicializo el contador de centenas xi y establezco el límite a 6 dígitos en la parte entera
        $xexit = true; // bandera para controlar el ciclo del While
        while ($xexit) {
            if ($xi == $xlimite) { // si ya llegó al límite máximo de enteros
                break; // termina el ciclo
            }

            $x3digitos = ($xlimite - $xi) * -1; // comienzo con los tres primeros digitos de la cifra, comenzando por la izquierda
            $xaux = substr($xaux, $x3digitos, abs($x3digitos)); // obtengo la centena (los tres dígitos)
            for ($xy = 1; $xy < 4; $xy++) { // ciclo para revisar centenas, decenas y unidades, en ese orden
                switch ($xy) {
                    case 1: // checa las centenas
                        if (substr($xaux, 0, 3) < 100) { // si el grupo de tres dígitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas

                        } else {
                            $key = (int) substr($xaux, 0, 3);
                            if (TRUE === array_key_exists($key, $xarray)){  // busco si la centena es número redondo (100, 200, 300, 400, etc..)
                                $xseek = $xarray[$key];
                                $xsub = subfijo($xaux); // devuelve el subfijo correspondiente (Millón, Millones, Mil o nada)
                                if (substr($xaux, 0, 3) == 100)
                                    $xcadena = " " . $xcadena . " CIEN " . $xsub;
                                else
                                    $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
                            }
                            else { // entra aquí si la centena no fue numero redondo (101, 253, 120, 980, etc.)
                                $key = (int) substr($xaux, 0, 1) * 100;
                                $xseek = $xarray[$key]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
                                $xcadena = " " . $xcadena . " " . $xseek;
                            } // ENDIF ($xseek)
                        } // ENDIF (substr($xaux, 0, 3) < 100)
                        break;
                    case 2: // checa las decenas (con la misma lógica que las centenas)
                        if (substr($xaux, 1, 2) < 10) {

                        } else {
                            $key = (int) substr($xaux, 1, 2);
                            if (TRUE === array_key_exists($key, $xarray)) {
                                $xseek = $xarray[$key];
                                $xsub = subfijo($xaux);
                                if (substr($xaux, 1, 2) == 20)
                                    $xcadena = " " . $xcadena . " VEINTE " . $xsub;
                                else
                                    $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                $xy = 3;
                            }
                            else {
                                $key = (int) substr($xaux, 1, 1) * 10;
                                $xseek = $xarray[$key];
                                if (20 == substr($xaux, 1, 1) * 10)
                                    $xcadena = " " . $xcadena . " " . $xseek;
                                else
                                    $xcadena = " " . $xcadena . " " . $xseek . " Y ";
                            } // ENDIF ($xseek)
                        } // ENDIF (substr($xaux, 1, 2) < 10)
                        break;
                    case 3: // checa las unidades
                        if (substr($xaux, 2, 1) < 1) { // si la unidad es cero, ya no hace nada

                        } else {
                            $key = (int) substr($xaux, 2, 1);
                            $xseek = $xarray[$key]; // obtengo directamente el valor de la unidad (del uno al nueve)
                            $xsub = subfijo($xaux);
                            $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                        } // ENDIF (substr($xaux, 2, 1) < 1)
                        break;
                } // END SWITCH
            } // END FOR
            $xi = $xi + 3;
        } // ENDDO

        if (substr(trim($xcadena), -5, 5) == "ILLON") // si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
            $xcadena.= " DE";

        if (substr(trim($xcadena), -7, 7) == "ILLONES") // si la cadena obtenida en MILLONES o BILLONES, entoncea le agrega al final la conjuncion DE
            $xcadena.= " DE";

        // ----------- esta línea la puedes cambiar de acuerdo a tus necesidades o a tu país -------
        if (trim($xaux) != "") {
            switch ($xz) {
                case 0:
                    if (trim(substr($XAUX, $xz * 6, 6)) == "1")
                        $xcadena.= "UN BILLON ";
                    else
                        $xcadena.= " BILLONES ";
                    break;
                case 1:
                    if (trim(substr($XAUX, $xz * 6, 6)) == "1")
                        $xcadena.= "UN MILLON ";
                    else
                        $xcadena.= " MILLONES ";
                    break;
                case 2:
                    if ($xcifra < 1) {
                        $xcadena = "CERO PESOS $xdecimales/100 M.N.";
                    }
                    if ($xcifra >= 1 && $xcifra < 2) {
                        $xcadena = "UN PESO $xdecimales/100 M.N. ";
                    }
                    if ($xcifra >= 2) {
                        $xcadena.= " PESOS $xdecimales/100 M.N. "; //
                    }
                    break;
            } // endswitch ($xz)
        } // ENDIF (trim($xaux) != "")
        // ------------------      en este caso, para México se usa esta leyenda     ----------------
        $xcadena = str_replace("VEINTI ", "VEINTI", $xcadena); // quito el espacio para el VEINTI, para que quede: VEINTICUATRO, VEINTIUN, VEINTIDOS, etc
        $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
        $xcadena = str_replace("UN UN", "UN", $xcadena); // quito la duplicidad
        $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
        $xcadena = str_replace("BILLON DE MILLONES", "BILLON DE", $xcadena); // corrigo la leyenda
        $xcadena = str_replace("BILLONES DE MILLONES", "BILLONES DE", $xcadena); // corrigo la leyenda
        $xcadena = str_replace("DE UN", "UN", $xcadena); // corrigo la leyenda
    } // ENDFOR ($xz)
    return trim($xcadena);
}

// END FUNCTION

function subfijo($xx)
{ // esta función regresa un subfijo para la cifra
    $xx = trim($xx);
    $xstrlen = strlen($xx);
    if ($xstrlen == 1 || $xstrlen == 2 || $xstrlen == 3)
        $xsub = "";
    //
    if ($xstrlen == 4 || $xstrlen == 5 || $xstrlen == 6)
        $xsub = "MIL";
    //
    return $xsub;
}

// END FUNCTION

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
<table style="width:100%;" cellpadding="0" cellspacing="0" border="">
    <tr>
	  <td valign="top" style=" text-align:left;">.</td>
      <td valign="top" style="width:20%; text-align:left;"></td>
      <td valign="top" style="width:60%; text-align:center; font-size:10px;"><?php 
      if ($salon==="HORMIGA"){ 
      	echo "<img src='../img/salon_hormiga.png' style='width:85%; margin-right:50px;' />"; 
      }
      if($salon==="CARACOL"){ 
      	echo "<img src='../img/salon_caracol.png' style='width:85%; margin-right:50px;' />"; 
      } 
      if($salon === "BICHOS TO GO"){
      	echo "<img src='../img/logo.png' style='width:85%; margin-right:50px;' />"; 
      }
    ?>
  </td>
      <td valign="top"><p>FOLIO NO. </p><p style="text-align:right; color:red;"><?php echo $eve; ?></p>
      </td>
    </tr>
</table>
	<table cellpadding="0" cellspacing="0" style=" font-size:12px;width:100%; margin-top:10px; padding:0 20px;">
	<tr>
	<td style="width:100%;"><div style="width:100%; padding 20px; font-size:12px;text-align:justify;">
	<strong>NOMBRE DEL FESTEJADO: <?php echo $nombreEve ?> </strong><BR>
	<strong>EDAD QUE CUMPLE: </strong> <?php echo $edad ?>  <strong>  PERSONAJE DE LA FIESTA: </strong><?php echo $personaje ?><BR>
	<strong>NOMBRE DE PAPA O MAMA: </strong><?php echo $cliente ?> <BR>  
	<strong>TELEFONO: </strong><?php echo $telCliente ?>   <strong>  CELULAR: </strong> <?php echo $celular ?><BR>
	<strong>DIRECCION: </strong><?php echo $domicilio ?> <BR>  
	<!--<strong>FECHA DE CONTRATACION: </strong>'. varFechaAbr($fecha).' <BR>-->
	<strong>EMAIL: </strong><?php echo $email ?> <BR>
	<strong>MEDIO DE PUBLICIDAD: </strong><?php echo $medio ?> <BR>	
	</div></td>
    </tr>
</table>
	<BR>
<div align="center"><strong>DATOS DEL EVENTO</strong></div>
<BR>
<table cellpadding="0" cellspacing="0" style=" font-size:12px;width:100%; margin-top:10px; padding:0 20px;">
<TR>
<TD>
NUM. DE PERSONAS: <?php echo $no_invitados ?> </TD><TD>   NIÑOS: <?php echo $no_ninos ?></TD><TD>     ADULTOS: <?php echo $no_adultos ?></TD>
</TR>
<TR>
<!--<TD>
FECHA DE EVENTO: '. varFechaAbr($fechaEve).' </TD>--><br/><br/>
</TR>
<TR>
<TD>
<STRONG>MENU</STRONG></TD>
</TR>
<TR>

<TD>   NIÑOS: <?php echo $no_ninos_menu ?></TD><TD>     ADULTOS: <?php echo $no_adultos_menu ?></TD>
</TR>
<TR>
<TD>
   GUARNICION: <?php echo $guarnicion ?></TD>
</TR>
<TR>
<TD>
   BOTANA:  <?php echo $botana ?></TD>
</TR>
<TR>
<TD>
   ITINERARIO</TD>
</TR>
<TR>
<TD>
   HORA DEL EVENTO: <?php echo varHoraAbr($fechaEve) ?> </TD>
</TR>
<TR>
<TD>
   HORA DE COMIDA: <?php echo varHoraAbr($hora_cena) ?> </TD> <td><table><tr><td>
   
   PASTEL: <?php echo $pastel ?>
   </td></tr>
   <tr><td>
   
   PIÑATA: <?php echo $pinata ?>
   </td></tr><tr><td>
   
   CENTRO DE MESA: <?php echo $centro_mesa ?>
   </td></tr><tr><td>
   
   INVITACIONES:  <?php echo $invitaciones ?>
   </td></tr>
   
   
   </table></td>
</TR>
<TR>
<TD>
   BEBIDA</TD>
</TR>
<TR>
<TD>
   REFRESCOS:  <?php echo $refrescos ?></TD>
</TR>
<TR>
<TD>
   AGUAS FRESCAS: <?php echo $aguas ?></TD>
</TR>
<TR>
<TD>
   PROMOCION: <?php echo $promocion ?></TD>
</TR>
<TR>
<TD>
   COLOR DE MANTEL: <?php echo $color_mantel ?></TD>
</TR>
<TR>
<TD>
   </TD>
</TR>
<TR>
<TD>
  SERVICIOS EXTRA: <?php echo $servicios_extra ?></TD>
</TR>
<br/><br/>
</TABLE>
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
	</div><br><br><br><br><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
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
<strong>1.- </strong>El arrendador da en arrendamiento al arrendatario, el salón de nombre <?php echo $salon; ?>.
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
la cantidad de <strong>$<?php echo  $porpagar.".00" ?></strong> son:(<?php echo numtoletras($porpagar); ?>) por salón,
pagaderos de la siguiente manera 50% anticipo por separacion y el 50% restante 15 dias hábiles antes de la realización del evento
en caso de incumplimiento, el arrendatario se sujetará a la pena establecida a la claúsula 6 bichos entregará al arrendatario el
recibo correspondiente a los anticipos.
</div>
<br/>
<div style="width:100%; padding:5 20px; font-size:12px;text-align:justify;">
<strong>6.- </strong>En caso de que el arrendatario cambie la fecha de evento se le cobrará una penalización de $500.00 y por su cancelacion de evento
será de $1,500.00 la penalización. Cantidades que serán retenidas por daños y prejuicios.
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
<br /><br /><br />
<table border="0" cellpadding="0" cellspacing="0" style="font-size:11px; width:100%; margin-top:5px;">
  <tr>
    <td style="width:50%;vertical-align:top; text-align:center;">
    
    <br/>
    <br/>
    <br/>____________________________<br />
    ARRENDADOR<br />BICHOS FIESTA S.A. DE CV.
      <br /></td>
    <td style="width:50%;vertical-align:top; text-align:center;">
     
    <br/>
    <br/>
    <br/>____________________________<br />ARRENDATARIO<br/><?php echo $cliente; ?></td>
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