<?php include("partes/header.php");
//include("scripts/permisos.php");
setlocale(LC_ALL,"");
setlocale(LC_TIME,"es_MX");
include("scripts/func_form.php");
?>

<script src="js/presupuesto.js"></script>
<script src="js/formularios.js"></script>
<style>
/* estilos para formularios */
.flota_der{
	position:absolute;
	bottom:0px;
	right:10px;
}
	.flota_der2{
		position:absolute;
		bottom:0px;
		right:80px;
	}
.alejar_izq{
	margin-left:10px;
}
.clave{
	text-align:right;
}
.campo_form{
	margin:4px 0;
	text-align:center;
}
.text_corto{
	width:80px;
}
.text_mediano{
	width:150px;
}
.text_largo{
	width:400px;
}
.text_full_width{
	width:100%;
}
.text_half_width{
	width:50%;
}
.label_width{
	width:175px;
}
.borrar_fecha{
	cursor:pointer;
	display:none;
}
.table{
	margin:0 auto;
}
.guardar_articulo{
	background: white url('img/check.png') left center no-repeat;
	background-size:contain;
	cursor:pointer;
	width:20px;
	height:20px;
	display:inline-block;
	margin-right:10px;
}
.eliminar_articulo{
	background: white url('img/cruz.png') left center no-repeat;
	background-size:contain;
	cursor:pointer;
	width:20px;
	height:20px;
	display:inline-block;
	margin-right:10px;
}
.crearevento{
	background-color:#070;
	color:#FFF;
	font-weight:bold;
	border:none;
	cursor:pointer;
	padding: 2px 10px;
}
.crearevento:active{
	background-color:#FFF;
	color:#070;
}
#hacer .precio{
	/*display:none;*/
	width:50px;
}
td{
	
}
.divplazos, .divbancos{
	display:inline-block;
}
#cuenta .campo_form{
	text-align:left;
}
#cuenta label{
	display:inline-block;
	width:100px;
	margin-right:5px;
}
#observaciones{
	width:50%;
	height:100px;
}
li{
	list-style:none;
}
</style>

<div id="contenido">
<div id="tabs">
  <ul>
    <li class="hacer"><a href="#hacer">Presupuesto</a></li>
    <li class="mias"><a href="#mias">Mis presupuestos</a></li>
  </ul>
  <div id="hacer">
    <form id='eventos' class='formularios'>
	<h3 class='titulo_form'>Presupuesto</h3>
      <div class="tabla">
    
        <input type="text" name="id_usuario" class="id_usuario" value="<?php echo $_SESSION["id_usuario"]; ?>" style="display:none;" />
        <input type="hidden" name="id_cliente" class="id_cliente" value="" />
        <input type="hidden" name="id_eve" class="id_eve" value="" />
        <?php
			$bd=new PDO($dsnw, $userw, $passw, $optPDO);
			$sql = "select MAX(folio) as id from presupuesto";
			$res = $bd->query($sql);
			$res = $res->fetchAll(PDO::FETCH_ASSOC);
		?>
        <div class="campo_form celda">
			<label class="">FOLIO</label>
				 <input type="text" name="clave" class="label folio requerido mayuscula text_corto" id="folio" data-nueva="" value="<?php echo $res[0]["id"] + 1;?>" />

          </div>
		  <!--
        <div class="campo_form celda fondo_azul" align="center">
        	<label>Salón</label><input class="eventosalon salonr" type="radio" name="quitar" value="salon" />
            <label>Evento</label><input class="eventosalon eventor" type="radio" name="quitar" value="evento" />
            <input type="hidden" class="eventosalon_h" name="eventosalon" />
        </div>
        <div class="campo_form salones celda" style="width:292px;">
			<label>Salones</label>
			<select name="salon" class="salon">
            	<option selected disabled>Elige un salón</option>
            	<?php //salonesOpt();	?>
            </select>
		</div>
        <div class="campo_form celda" style="">
			<label>Tipo de evento</label>
			<select name="id_tipo" class="id_tipo">
            	<option selected disabled value="">Elige un tipo</option>
            	<?php //tipoEventosOpt();	?>
            </select>
		</div>-->
      </div>
      <div class="tabla">
        <div class="celda" style=" width:600px;">
		<div class="campo_form">
          	<label>Nombre de Evento</label>
			<input type="hidden" id="id_eve" value=""/>
            <input class="cliente_evento text_largo" id="evento" type="text" onkeyup="eve_autocompletar();"/>
          </div>
		<div class="campo_form">
          	<label>Nombre del Salon:</label>
            <input class="cliente_evento text_largo" id="salon" type="text" />
		</div>
          
          <div class="campo_form">
          	<label>Niños</label>
            <input class="cliente_evento text_largo" id="ninos" type="text" value=""/>
          </div>
          <div class="campo_form">
          	<label>Adultos</label>
            <input class="cliente_evento text_largo" id="adultos" type="text" value=""/>
          </div>
          <div class="campo_form">
          	<label>Paquete Basico</label>
            <input class="cliente_evento text_largo" id="paquete" type="text" value=""/>
          </div>
		  
		  <!--
		  
		  
          <div class="campo_form">
            <label class="">Nombre del evento</label>
			<input type="text" name="nombre" class="nombre text_largo requerido" />
          </div>-->
		</div>
        <div class="celda" style="">
          <div class="campo_form">
            <label class="align_right" style="width:120px;">Fecha Solicitud</label>
        	<abbr title=""><input placeholder="Click para elegir" class="fecha alejar_izq requerido fechapresupuesto" type="text" name="fechaevento"  readonly/></abbr><!--
            --><img class="borrar_fecha" data-class="fechaevento" src="img/cruz.png" width="15" />
          </div>
          <div class="campo_form">
            <label class="align_right" style="width:120px;">Fecha Evento</label>
        	<abbr title=""><input placeholder="Click para elegir" class="fecha alejar_izq requerido fechaevento" type="text" name="fechamontaje" readonly/></abbr><!--
            --><img class="borrar_fecha" data-class="fechamontaje" src="img/cruz.png" width="15" />
          </div>
          
		</div>
      </div>
        <div align="right">
            <input type="button" class="modificar_pres" value="MODIFICAR" data-wrap="#hacer" style="display:none;" />
            <input type="button" class="guardar_pres" value="CREAR" data-wrap="#hacer" data-accion="guardar" data-m="pivote" />
            <input type="button" class="nueva" value="NUEVA"  />
        </div>
	</form>
    <div class='formularios'>
	<h3 class='titulo_form'>Presupuesto</h3>
    <table id="articulos" class="table">
      <tr>
      	<th class="agregar_articulo"><img src="img/mas.png" height="25" /></th>
        <th width="100">Proveedor.</th>
        <th width="100">Cant.</th>
        <th width="250">Articulo</th>
        <th width="100">precio unitario</th>
        <th width="100">total</th>
        <th width="150">Acciones</th>
      </tr>       
	  <?php
	  
			// $bd=new PDO($dsnw, $userw, $passw, $optPDO);
			// $sql = "SELECT cantidad, precio, total, nombre
// FROM gastos_art
// INNER JOIN gastos ON gastos.id_gasto = gastos_art.id_gasto
// WHERE id_gEve =2";
			// $res = $bd->query($sql);
			// foreach($res->fetchAll(PDO::FETCH_ASSOC) as $d)
			// {
				// echo '<tr><th></th><th>' . $d["cantidad"] . '</th><th>' . $d["nombre"] . '</th><th>' . $d["precio"] . '</th><th>' . $d["total"] . '</th></tr>';
			// }
		?>
    </table>
    </div>
	
	<div id="cuenta" class="formularios" align="left">
    <h3 class='titulo_form'>Cuenta</h3>
        <div class="campo_form">
            <label class="">Total de gastos</label>
            <input type="text" class="totalgasto numerico" id="totalGas" readonly="readonly" />
        </div>
        <!--<div class="campo_form">
            <label class="">Restante:</label>
            <input type="text" class="restante numerico" id="restGas" readonly="readonly" />
        </div>
        <div align="right">
            <input type="button" class="historial" value="Ver historial de pagos" />
            <input type="button" class="agregarpago" value="Agregar Pago" />
        </div>-->
		
        <div id="historial" class="formularios" style="display:none;">
        	<h3 class='titulo_form'>Historial de pagos</h3>
            <div class="mostrar"></div>
        </div>
        <div id="nuevopago" class="formularios" style="display:none;">
        	<h3 class='titulo_form'>Nuevo Pago</h3>
            <input type="hidden" class="id_emp_eve" value="" />
             <div class="campo_form">
                <label class="">Importe:</label>
                <input type="text" class="importe numerico" />
            </div>      
            <div class="campo_form">
                <label class="">Fecha del pago:</label>
                <input type="text" class="fechasql fechapago numerico" />
            </div>
			<div class="campo_form">
            <label class="">Metodo de pago</label>
            <select class="metodo">
            	<option value="Efectivo">Efectivo</option>
                <option value="Cheque">Cheque</option>
                <option value="Transferencia">Transferencia</option>
            <option value="Tarjeta de credito">Tarjeta de credito</option>
            <option value="Tarjeta de débito">Tarjeta de débito</option>
            </select>
            <div class="divplazos" style="display:none;">
                <label class="">Plazos:</label>
                <input type="text" class="plazos numerico" size="4" value="1" />
            </div>
            <div class="divbancos" style="display:none;" id="bancos">
                <label class="">Bancos:</label>
				<?php 
					$bd=new PDO($dsnw,$userw,$passw,$optPDO);
					$sql = "select nombre, id_banco from bancos";
					$res = $bd->query($sql);
				?>
                <select class="bancos"><option value="0">Elige un banco</option>
				<?php 
					foreach($res->fetchAll(PDO::FETCH_ASSOC) as $datos)
					{
						$id = $datos["id_banco"];
						$nombre = $datos["nombre"];
						echo "<option value=$id>$nombre</option>";
					}
				?>
				</select>
            </div>
        </div>
            <div align="right">
                <input type="button" class="anadir" value="Añadir pago" />
            </div>
        </div>
    </div>
	
    <div align="left" class="formularios">
    <h3 class='titulo_form'>Observaciones</h3>
      <form action="scripts/pdf_presupuesto.php" method="get" target="_blank">
	  <table class="">
		  <tr>
			  <td>
					<label class="">Elaborado por:</label>
			  </td>
			  <td>
					<input type="text" name="elaborado" id="elaborado"/>
			  </td>
			  <td rowspan="5"><textarea name="obs" id="obs" placeholder="Notas" cols="70" rows="5" style="resize:none;"></textarea></td>
		  </tr>
		  
	  </table>
        

        <input type="hidden" name="id_evento" class="id_evento" value="" />
        <input type="hidden" name="total_cot" class="total_cot" value="" />
        <input type="hidden" name="id_Folio" class="id_Folio" value="" />	  <!--
        <input type="submit" onclick="this.form.action='scripts/nota_venta_pdf.php'" value="Hoja de bulto" class="flota_der2" />-->
		  <input type="submit" value="Imprimir" class="flota_der" />
      </form>
    </div>
  </div>
  <div id="mias">
  <style>
  	#mias table{
		font-size:0.85em;
	}
	#mias th{
		font-size:1.05em;
		margin:2px;
	}
	#mias td{
		margin:2px;
		padding:5px 2px;
	}
	#mias .filtro{
		width:100%;
	}
	.accion{
		margin:0 5px;
		cursor:pointer;
	}
  </style>
  <table cellpadding="0" cellspacing="2" border="0" width="100%" class="listado" id="tablaEve">
  <tr>
  	<th>Clave<br />Folio</th>
    <th style="width:200px;">Nombre del evento</th>
    <th>Fecha<br />evento</th>
    <th>acciones</th>
  </tr>
  <tr class="barra_accion">
    <td style="width:34px;"><input class="filtro" data-c="bfolio" /></td>
    <td><input class="filtro" data-c="bnombre" /></td>
    <td><input class="filtro filtrofecha" data-c="bfechaevento" /></td>
    <td><a href="#" class="pdf" onclick="return false;" data-nombre="evento" data-orientar="L">generar pdf</a></td>
  </tr>
  	<?php 
	try{
		$bd=new PDO($dsnw,$userw, $passw, $optPDO);
			
		$sql="SELECT * 
		FROM presupuesto
		INNER JOIN eventos ON presupuesto.id_evento = eventos.id_evento;";
		
		$res=$bd->query($sql);
		
		
		//correlacionar los subarrays al array principal de evento
		
		$cot = $res->fetchAll(PDO::FETCH_ASSOC);
		$cont = 2;
		//escribimos la tabla
		foreach($cot as $folio=>$d){
			echo '<tr class="cot'.$d["folio"].'">';
			echo '<td class="bfolio">'.$d["folio"]. '</td>';
			echo '<td class="bnombre">'.$d["nombre"].'</td>';
			echo '<td class="bfechaevento">'.varFechaAbrNorm($d["fecha_solicitud"]).'</td>';
			echo '<td><img class="accion" src="img/edit.png" data-cve="'.$d["id_presupuesto"].'" onclick="editar('.$d["id_presupuesto"].');" height="20" /><img class="accion eliminar" src="img/cruz.png" data-cve="'.$d["id_presupuesto"].'" height="20" onclick="eliminar_presupuesto(' . $d["id_presupuesto"] . ',' . $cont . ')"/></td>';
			echo '</tr>';
			$cont++;
		}
		$bd=NULL;
	}catch(PDOException $err){
		echo "Error encontrado: ".$err->getMessage();
	}
	?>
  	</table>
  </div>
</div>
</div>

<?php include("partes/footer.php"); ?>