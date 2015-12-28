// JavaScript Document
$(document).ready(function(e) {

	//alerta("info","Vista de gastos NO DISPONIBLE todavía");
	$("#tabs").tabs({
		heightstyle:"content"
	});// Controla las pestañas que cambian la pagina
	
	//para ver el formulario de pago
	$(".agregarpago").click(function(e) {
        $("#nuevopago").slideToggle(200);
    });
	
	//para ver historial de pago
	$(".historial").click(function(e) {
        $("#historial").slideToggle(200);
    });
	
	//para añadir pago
	$(".anadir").click(function(e) {
		eve=$("#id_eve").get(0).value;
		//alert(eve);
		monto=$(".importe").val();
		//alert(monto);
		resto=$("#restGas").val();
		fecha=$(".fechapago").val();
		//alert(fecha);
		cliente=$(".id_cliente").val();
		//alert($(".id_cliente").val());
		metodo=$(".metodo").val();
		//alert(metodo);
		//var banco=document.getElementById("bancos");
		banco = $(".bancos").val();
		idbanco = 0;
		
		if(monto <= resto)
		{
			$.ajax({
				url:'scripts/s_pagar_gastos.php',
				cache:false,
				type:'POST',
				data:{
					'eve':eve,
					'monto':monto,
					'fecha':fecha,
					'cliente':cliente,
					'metodo':metodo,
					'banco':idbanco
				},
				success: function(r){
					if(r.continuar){
						alerta("info","Pago añadido exitosamente");
						checarTotalEve(eve);
						historial(evento);
						$("#nuevopago input[type=text]").val('');
					}else{
						alerta("error",r.info);
					}
				}
			});
		}else{
			if(resto>0){
				alerta("info", "El monto a pagar es mayor a la deuda.");
			}else{
				alerta("info", "El monto total ya ha sido pagado.");
			}
		}
		checarTotalGas(eve);
		historial(eve);
	});
	
	$(".metodo").change(function(e) {
		$(".divplazos").hide();
		$(".divbancos").hide();
        if($(this).find("option:selected").val()=="A crédito"){
			$(".divplazos").show();
		}else if($(this).find("option:selected").val()=="Transferencia" || $(this).find("option:selected").val()=="Cheque" || $(this).find("option:selected").val()=="Tarjeta de credito" || $(this).find("option:selected").val()=="Tarjeta de débito"){
			$(".divbancos").show();
		}
    });
	

	$(".guardar_pres").click(function (){
		evento = document.getElementById("id_eve").value;
		paq = document.getElementById("paquete").value;
		fechasol = $(".fechapresupuesto").val();
		folio = $('.folio').val();
		if(!evento){
			alerta("error","Seleccione un evento");
			return false;
		}
		$.ajax({
			url:'scripts/s_guardar_presupuesto.php',
			cache:false,
			async:false,
			type:'POST',
			data:{ 
				'evento':evento,
				'fechasol':fechasol,
				'paq':paq,
				'folio':folio
			},
			success: function(r){
				if(r.continuar){
					alerta("info","Presupuesto agregado");
					console.log(r.fecha);
				$(".nueva").hide();
				$(".guardar").hide();
				$(".modificar_pres").show();
				}else{
					alerta("error","Ocurrio un error al guardar");
				}//
			}
		});
	});
		$( ".clave_cotizacion" ).keyup(function(){//-------------------------------
		_this=$(this);
		if(typeof timer=="undefined"){
			timer=setTimeout(function(){
				buscarClaveGet()
			},300);
		}else{
			clearTimeout(timer);
			timer=setTimeout(function(){
				buscarClaveGet();
			},300);
		}
    }); //termina buscador de cotizacion
	$(".modificar_pres").click(function(e) {
		//procesamiento de datos
		folio = $('.folio').val();
		paq_basico = $('#paquete').val();
		fechapresupuesto = $('.fechapresupuesto').val();
		$.ajax({
			url:'scripts/modificar_presupuesto.php',
			cache:false,
			async:false,
			type:'POST',
			data:{ 
				'folio':folio,
				'paq_basico':paq_basico,
				'fechapresupuesto':fechapresupuesto,
			},
			success: function(r){
				console.log(r);
				if(r){
					alerta("info","Presupuesto Modificado Correctamente");
				}else{
					alerta("error","Ocurrio un error al modificar");
				}//
			}
		});
    });

	//$(".agregar_proveedor").click(function(){
	//	id_evento=$(".id_evento").get(0).value;
	//	id=$(."lista_proveedores").length+1;
	//	$("#proveedores").append();
	//	$.each($(".lista_proveedores"),function(i,v){
	//		$(this).find(".id_evento").val(id_evento);
	//	});
	//
	//});

	$(".agregar_articulo").click(function(){
		id_evento=$(".id_evento").get(0).value;
		id=$(".lista_articulos").length+1;
		$("#articulos").append('<tr id="'+id+'" class="lista_articulos"><td style="background-color:#FFF;"><input type="hidden" class="id_item" value="" /><input type="hidden" class="id_evento" value="" /><input type="hidden" class="id_articulo" /><input type="hidden" class="id_paquete" /></td><td> <input class="proveedor" type="text" size="10" onkeyup="prov_completar()"></td><td><input class="cantidad" type="text" size="7" onkeyup="cambiar_cant('+id+')" /></td><td><input class="articulo_nombre text_full_width" onkeyup="art_autocompletar('+id+');" /></td><td>$<input type="text" class="precio" onkeyup="darprecio(this)" /></td><td>$<span class="total"></span></td><td><span class="guardar_articulo" onclick="guardar_art('+id+')"></span><span class="eliminar_articulo" onclick="eliminar_art('+id+')"></span></td></tr>');
		$.each($(".lista_articulos"),function(i,v){
			$(this).find(".id_evento").val(id_evento);
		});
		$(".cantidad").numeric();
	});
    $(".volver").click(function(e) {
		ingresar=true;
    	$("#formularios_modulo").hide("slide",{direction:'right'},rapidez,function(){
			$("#botones_modulo").fadeIn(rapidez);
		});
    });
});

function historial(eve){
	$.ajax({
		url:'scripts/s_historial_gastos_pagos.php',
		cache:false,
		type:'POST',
		data:{
			'eve':eve
		},
		success: function(r){
			$("#historial .mostrar").html(r);
		}
	});
	//funcion para ver el historial de pagos del evento
}

function checarTotal(tabla,id){
	var total;
	$.ajax({
		url:'scripts/s_check_total_gastos.php',
		cache:false,
		async:false,
		type:'POST',
		data:{
			'tabla':tabla,
			'id':id
		},
		success: function(r){
			if(r){
				var total = "<tr><td colspan=4></td><td ><span>" + r.total + "</span></td></tr>";
				//$("#articulos").append(total);
			}else{
				//alerta("error",r.info);
			}
		}
	});
}
function checarTotalPres(id){
	var total;
	$.ajax({
		url:'scripts/s_check_total_pres.php',
		cache:false,
		async:false,
		type:'POST',
		data:{
			'folio':id
		},
		success: function(r){
			$("#totalGas").val(r.total);
		}
	});
}
function get_items_pres(id){
	$(".lista_articulos").remove();
	$.ajax({
		url:'scripts/get_items_pres.php',
		cache:false,
		async:false,
		data:{
			'id_presupuesto':id
		},
		success: function(r){
			$("#articulos").append(r);
		}
	});
}
function requerido(){
	selector=".requerido";
	continuar=true;
	$.each($(selector).parent().find(".requerido"),function(i,v){
		if($(this).val()==""){
			$(this).addClass("falta_llenar");
			continuar=false;
		}
	});
	return continuar;
}
function darprecio(e){
	precio=$(e).val();
	$(e).parent().parent().removeClass("verde_ok");
	cant=$(e).parent().parent().find(".cantidad").val();
	$(e).siblings(".precio").html(precio);
	total=(precio*1)*(cant*1);
	$(e).parent().parent().find(".total").html(total);
}

function prov_completar(){
	console.log('test');
		//busca de proveedores
	$(".proveedor").autocomplete({
      source: "scripts/busca_proveedores1.php",
      minLength: 2,
      select: function( event, ui ){        
      }
    });
}
function art_autocompletar(id){
	padre=$("#"+id);
	cantidad=padre.find(".cantidad").val()*1;
	id_articulo=padre.find(".id_articulo");
	id_paquete=padre.find(".id_paquete");
	precio=padre.find(".precio").parent();
	total=padre.find(".total");
	$( "#"+id+" .articulo_nombre").autocomplete({
	  source: "scripts/busca_articulos.php",
	  minLength: 1,
	  select: function( event, ui ) {
		  total.parent().parent().removeClass("verde_ok");
		  id_articulo.val(ui.item.id_articulo);
		  id_paquete.val(ui.item.id_paquete);
		  precio.html(ui.item.precio);
		  totalca=cantidad*ui.item.precio;
		  total.html(totalca);
	  }
	});
}
function eve_autocompletar(){
	$( "#evento").autocomplete({
	  source: "scripts/busca_evento_nombre.php",
	  minLength: 2,
	  select: function( event, ui ) {
	  	console.log(ui);
	  	
		$('#salon').val(ui.item.salon);
  		$('#ninos').val(ui.item.no_ninos);
  		$('#adultos').val(ui.item.no_adultos);
  		$('.fechaevento').val(ui.item.fechaevento);
  		$('.fechapresupuesto').val(ui.item.fecha_sol);
  		$('#id_eve').val(ui.item.id_evento);

	  }
	});
}
	function eliminar_presupuesto(elemento, id_item){
		$.ajax({
			url:'scripts/eliminar_presupuesto.php',
			cache:false,
			type:'POST',
			data:{
				'id':elemento
			},
			success: function(r){
			  if(r){
				document.getElementById("tableEve").deleteRow(elemento);
					alerta("info","<strong>Presupuesto</strong> Eliminado");
			  }else{
				alerta("error", r);
			  }
			}
		});
	}
	function editar(id)
	{
		$.get('scripts/busca_presupuesto.php',
		{
			'id':id
		}
		).done(function(data){
			$.each(data, function (index, ui) {
   				$('.folio').val(ui.folio);
   				$('#salon').val(ui.salon);
   				$('#evento').val(ui.nombre);
		  		$('#festejado').val(ui.nombre);
		  		$('#ninos').val(ui.no_ninos);
		  		$('#adultos').val(ui.no_adultos);
		  		$('.fechaevento').val(ui.fechaevento);
		  		$('.fechapresupuesto').val(ui.fecha_sol);
		  		$('#id_eve').val(ui.id_evento);
		  		$('#paquete').val(ui.paq_basico);
		  		$('.guardar_pres').hide();
		  		$('.modificar_pres').show();
		  		$(".hacer a")[0].click();
		  		get_items_pres(ui.folio);
		  		checarTotalPres(ui.folio);
			});
			
		});
		
	}

function cambiar_cant(id){
	padre=$("#"+id);
	cantidad=padre.find(".cantidad").val()*1;
	precio=padre.find(".precio").val()*1;
	total=cantidad*precio;
	padre.find(".total").html(total);
	padre.removeClass("verde_ok");
}

function eliminar_art(elemento){
	
	folio=$(".folio").val();
	id_item=$("#"+elemento+" .id_item").val();

	if(id_item!=0){
		$.ajax({
			url:'scripts/quita_art_pres.php',
			cache:false,
			type:'POST',
			data:{
				'id_item':id_item,
			},
			success: function(r){
			  if(r.continuar){
				alerta("info","Se elimino correctamente el articulo ");
				$("#"+elemento).remove();
				checarTotalPres(folio);
			  }else{
				alerta("error",r.info);
			  }
			}
		});
	}else{
		$("#"+elemento).remove();
	}
}


function guardar_art(elemento){
	row=$("#"+elemento);
	padre=$("#"+elemento).parent();
	
	//mostrar que se esta procesando
	//procesando("mostrar",0);
	
	//checa si se modificó el total
	actTotal=true;
	if(row.hasClass("verde_ok")){
		actTotal=false;
	}
	
	id_item=$("#"+elemento+" .id_item").val();
	id_articulo=$("#"+elemento+" .id_articulo").val();
	id_paquete=$("#"+elemento+" .id_paquete").val();
	folio=$(".folio").val();
	cantidad=$("#"+elemento+" .cantidad").val();
	precio=$("#"+elemento+" .precios").val();
	proveedor = $('.proveedor').val();
	total=$("#"+elemento+" .total").html();
	$.ajax({
		url:'scripts/guarda_art_pres.php',
		cache:false,
		type:'POST',
		data:{
			'id_item':id_item,
			'id_paquete':id_paquete,
			'id_articulo':id_articulo,
			'folio':folio,
			'cantidad':cantidad,
			'precio':precio,
			'proveedor':proveedor,
			'total':total,
			boolTotal:actTotal
		},
		success: function(r){
			if(r.continuar){
				$("#"+elemento+" .id_item").val(r.id_item);
				//padre.find(".id_evento").val(id_evento);
				alerta("info","Fue agregado exitosamente");
				row.addClass("verde_ok");
				checarTotalPres(folio);
				//setTimeout(function(){checarTotal('eventos',id_evento);},500);
			  }else{
			  	console.log('error');
				alerta("error",r.info);
			  }
		}
	});
}