<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<?php
	$this->load->helper('html');
	$this->load->helper('url');

	echo link_tag($css_folder . '/tactilTodo.css?nc=' . time());

	echo '<script type="text/javascript" src="' . base_url('application/views/osk/jquery.min.js') . '"></script>';
	echo '<script type="text/javascript" src="' . base_url('application/views/osk/jquery.alerts.js') . '"></script>';
	echo link_tag(base_url('application/views/osk/jquery.alerts.css'));

	?>

    <title>Turnstat V3.2 - Keypad</title>
    <script type="text/javascript">

      function stopAndIndex() {
		  <?php
		  if ($withPdf417Reader){
		  ?>
        var temp = {};
        temp.id = 'stopBarcodeDocumentReader';
        socket.send(JSON.stringify(temp));
        setTimeout(function() {
          location.href = '<?php echo $firstPage; ?>?next_display=<?php echo $firstDisplay; ?>&printer_id=<?php echo $printer_id; ?>';
        }, 150);	//- Una pequeña demora para que el python alcance a responder un true ahí
		  <?php
		  }else{
		  ?>
        location.href = '<?php echo $firstPage; ?>?next_display=<?php echo $firstDisplay; ?>&printer_id=<?php echo $printer_id; ?>';
		  <?php
		  }    //- Cerramos el if que permite o no abrir el web socket hacia el pdfreader
		  ?>
      }

      //- Notese que stopAndIndex va a realizar un get y stopAndGoToNext va a realizar un post
      function stopAndGoToNext() {
		  <?php
		  if ($withPdf417Reader){
		  ?>
        var temp = {};
        temp.id = 'stopBarcodeDocumentReader';
        socket.send(JSON.stringify(temp));
        setTimeout(function() {
          document.getElementById('form_doc').submit();
        }, 150);	//- Una pequena demora para que el python alcance a responder un true ahi
		  <?php
		  }else{
		  ?>
        document.getElementById('form_doc').submit();
		  <?php
		  }    //- Cerramos el if que permite o no abrir el web socket haci el pdfreader
		  ?>
      }

	  <?php
	  if ($withPdf417Reader){
	  ?>
      //- Ponemos el localhost "hardcodeado" porque hasta donde me imagino
      //- la idea es buscar el lector que está debajo de esta pantalla táctil
      var host = 'ws://127.0.0.1:7998/ws';
      var socket = {};

      //* -------------------------------------------------
      //* ---------        Web Socket        --------------
      function socketIt() {

        socket = new WebSocket(host);

        socket.onopen = function() {
          console.log('WebSocket status: OPEN');
          var temp = {};
          temp.id = 'readBarcodeDocument';
          socket.send(JSON.stringify(temp));
        };

        socket.onmessage = function(msg) {
          if (typeof msg === 'object') {
            var data = JSON.parse(msg.data);
            if ('id' in data) {
              switch (data.id) {
                case 'readingBarcodeDocument':
                  if (data.success == true) {
                    if (data.warning == 'thread_already_running') {
                      //console.log("reading was running");
                    } else {
                      //console.log("reading has started");
                    }
                  }
                  break;
                case 'capturedBarcodeDocument':
                  doc = data.doc;
                  doc_type = data.doc_type;
                  apellido1 = data.apellido1;
                  apellido2 = data.apellido2;
                  nombre1 = data.nombre1;
                  nombre2 = data.nombre2;
                  sexo = data.sexo;

                  document.form_doc_name.doc.value = doc;
                  document.form_doc_name.apellido1.value = apellido1;
                  document.form_doc_name.apellido2.value = apellido2;
                  document.form_doc_name.nombre1.value = nombre1;
                  document.form_doc_name.nombre2.value = nombre2;
                  document.form_doc_name.sexo.value = sexo;

                  var radios = document.getElementsByName('doc_type');
                  for (var i = 0, length = radios.length; i < length; i++) {
                    if (doc_type == radios[i].value) {
                      radios[i].checked = true;
                      break;
                    }
                  }

                  if (data.doc_leido == 'matricula_vehiculo') {
                    document.form_doc_name.matricula_vehiculo.value = JSON.stringify({
                      'numero_chasis': data.numero_chasis,
                      'numero_matricula': data.numero_matricula,
                      'placa': data.placa
                    });
                  }

                  document.form_doc_name.doc_tool_used.value = 'doc_reader';

                  document.getElementById('form_doc').submit();

                  break;
                case 'stoppedBarcodeReader':
                  actionStatus = data.success;

                  //console.log(actionStatus);
                  //doSomethingElse();

                  break;

                default:
                  //console.log('default')
              }

            } else {
              //console.log('No ID found in received message.')
              //console.log(msg)
              //console.log(msg.data)
            }
          } else {
            //console.log('Not an object, type is: ' + typeof msg)
          }
        };

        socket.onclose = function() {
          console.log('WebSocket status: CLOSED');
          setTimeout(function() {
            socketIt();
          }, 3000);
        };
      }

      socketIt();

	  <?php
	  }    //- Cerramos el if que permite o no abrir el web socket haci el pdfreader
	  ?>

      window.onload = function() {
        timedRedirect();
        if (typeof document.body.onselectstart != 'undefined') {
          document.body.onselectstart = function() {
            return false;
          };
        } else if (typeof document.body.style.MozUserSelect != 'undefined') {
          document.body.style.MozUserSelect = 'none';
        } else {
          document.body.onmousedown = function() {
            return false;
          };
        }
      };

      function selDocType(clicked) {
        var radios = document.getElementsByName('doc_type');
        for (var i = 0, length = radios.length; i < length; i++) {
          if (clicked == radios[i].value) {
            radios[i].checked = true;
            break;
          }
        }
      }

      var tID = '';

      function numPad(arg1_num) {
        timedRedirect();
        switch (arg1_num) {
          case 'BKSP':
            document.getElementById('txt_doc').value = document.getElementById('txt_doc').value.slice(0, -1);
            document.getElementById('textarea').innerHTML = document.getElementById('txt_doc').value;
            document.getElementById('txt_doc').focus();
            break;
          case 'SEND':
            var radios = document.getElementsByName('doc_type');
            var oneChecked = 0;
            for (var i = 0, length = radios.length; i < length; i++) {
              if (radios[i].checked) {
                oneChecked = 1;
                break;
              }
            }

            if (oneChecked == 0) {
              //alert("Por favor seleccione un tipo de documento");
              jAlert('Por favor seleccione un tipo de documento', 'TurnStat');
              return;
            }
            $cedLength = document.getElementById('txt_doc').value.length;
            if ($cedLength <= 4) {
              //alert("Número de documento inválido, por favor verifique");
              jAlert('Número de documento inválido, por favor verifique', 'TurnStat');
              return;
            }

            stopAndGoToNext();

            break;
          case 'SEND_EMPTY':
            var radios = document.getElementsByName('doc_type');
            for (var i = 0, length = radios.length; i < length; i++) {
              radios[i].checked = true;
              if (radios[i].value == 'NA') {
                radios[i].checked = true;
                break;
              }
            }

            document.getElementsByName('user_has_no_doc')[0].value = '1';
            stopAndGoToNext();

            break;
          default:
            document.getElementById('txt_doc').value += arg1_num;
            document.getElementById('textarea').innerHTML = document.getElementById('txt_doc').value;
            document.getElementById('txt_doc').focus();
        }
      }

      tShowInicial = 0;

      function showKeypad() {
        $('#show_keypad_button').hide();
        $('div.put_doc_into_reader').hide();
        $('div.lector_doc_message_img').hide();
        $('#label_and_input').show();
        $('#keypad_cell').show();
        $('#i_have_no_doc').show();
        $('#separate_get_doc_volver_div').show();
        tShowInicial = setTimeout(showInicial, redirectTime);
      }

      function showInicial() {
        $('#show_keypad_button').show();
        $('div.put_doc_into_reader').show();
        $('div.lector_doc_message_img').show();
        $('#label_and_input').hide();
        $('#keypad_cell').hide();
        $('#i_have_no_doc').hide();
        $('#separate_get_doc_volver_div').hide();
        document.getElementById('txt_doc').value = '';
        var radios = document.getElementsByName('doc_type');
        for (var i = 0, length = radios.length; i < length; i++) {
          radios[i].checked = false;
        }
        clearTimeout(tShowInicial);
      }

      redirectTime = "<?php echo $keypad_display_redirect_time; ?>";
      redirectURL = "<?php echo $firstPage; ?>?next_display=<?php echo $firstDisplay; ?>&printer_id=<?php echo $printer_id; ?>";

      function timedRedirect() {
        clearTimeout(tID);
		  <?php
		  if ($firstPage != 'getDoc'){
		  ?>
        tID = setTimeout('location.href = redirectURL;', redirectTime);
		  <?php
		  }

		  if ($separate_get_doc_modes == 'true'){
		  ?>
        clearTimeout(tShowInicial);
        if (tShowInicial) {
          tShowInicial = setTimeout(showInicial, redirectTime);
        }
		  <?php
		  }
		  ?>
      }
    </script>

</head>

<body id="getDoc" class="getDoc">

<center>

    <form action="next" method="post" id="form_doc" name="form_doc_name">
        <input type="hidden" name="service_id" value='<?php echo $service_id; ?>'/>
        <input type="hidden" name="customer_is_pref" value='<?php echo $customer_is_pref; ?>'/>
        <input type="hidden" name="printer_id" value='<?php echo $printer_id; ?>'/>
        <input type="hidden" name="firstPage" value="<?php echo $firstPage; ?>"/>
        <input type="hidden" name="firstDisplay" value="<?php echo $firstDisplay; ?>"/>
        <input type="hidden" name="apellido1" value=""/>
        <input type="hidden" name="apellido2" value=""/>
        <input type="hidden" name="nombre1" value=""/>
        <input type="hidden" name="nombre2" value=""/>
        <input type="hidden" name="cellphone" value=""/>
        <input type="hidden" name="sexo" value="D"/>
        <input type="hidden" name="user_has_no_doc" value=""/>
        <input type="hidden" name="sent_from" value="get_doc"/>
        <input type="hidden" name="doc_tool_used" value="keypad"/>
        <!-- Por defecto vamos a poner siempre el "teclado en pantalla" -->
        <input type="hidden" name="matriculaVehiculosMaxSize" value=""/>
        <input type="hidden" name="matricula_vehiculo" value=""/>
        <input type="hidden" name="preference_type" value="<?php echo $preference_type; ?>"/>

        <!-- <input type="hidden" name="doc_type" value="CC"/> <!-- Valor por defecto en este menú -->

        <div id="label_and_input" <?php if ($separate_get_doc_modes == 'true')
		{
			echo 'style="display: none;"';
		} ?>>
            <font class="enter_your_doc_class">
				<?php
				if ($service_name)
				{
					echo "<font style=\"font-size:30px;\">Servicio seleccionado: </font><font style=\"color:black; font-size:33px;\">$service_name</font><br>";
				}
				?>
                Ingrese su número de identificación</font>
            <br>
            <div style='position: relative;'>
                <div id="textarea"
                     style="font-size:40px; border: solid 1px #bbbbbb; width: 482px; height: 45px; text-align: left; letter-spacing: 2px;"></div>
                <input type="text" name="doc" maxlength="11" size="19" id="txt_doc" value=""
                       style="font-size:40px; display: none;"/>
                <div style="position: absolute; top: 3px; left: 70%;">
                    <button class="keypad_num" type="button" onmousedown="numPad('BKSP')"
                            style="font-size: 26px; line-height: 0px; height:45px; width:140px; ">BORRAR
                    </button>
                </div>
            </div>
        </div>

        <script type="text/javascript">
          document.getElementById('txt_doc').focus();
        </script>

        <div id="doc_input">
            <table border="0">
                <tr align="center">
					<?php
					if ($withPdf417Reader)
					{
						?>
                        <td width="40%" height="100%">
                            <div class="put_doc_into_reader position1_put_doc_into_reader">
								<?php echo $strings["DOC_USE_ADVICE"]; ?>
                            </div>
                        </td>
                        <td width="10%" class="enter_manual_doc_message" <?php if ($separate_get_doc_modes == 'true')
						{
							echo 'style="display: none;"';
						} ?>>
							<?php echo $strings["DOC_USE_INSTEAD_ADVICE"]; ?>
                        </td>
						<?php
					}
					?>
                    <td id="keypad_cell" width="25%" <?php if ($separate_get_doc_modes == 'true')
					{
						echo 'style="display: none;"';
					} ?>>
                        <table cellpadding="5" border="0" height="100%">
                            <tr align="center">
                                <td>
                                    <button class="keypad_num" type="button" onmousedown="numPad('1')" style=" ">1
                                    </button>
                                </td>
                                <td>
                                    <button class="keypad_num" type="button" onmousedown="numPad('2')" style=" ">2
                                    </button>
                                </td>
                                <td>
                                    <button class="keypad_num" type="button" onmousedown="numPad('3')" style=" ">3
                                    </button>
                                </td>

                                <td rowspan="4" style="padding: 2px 0px 2px 10px">
                                    <table class="doc_type_sel" height="100%" border="0" cellpadding="2">
                                        <tr id="cc_select">
                                            <td><input class="doc_type_sel_radio" type="radio" name="doc_type"
                                                       value="CC" <?php if ($keypad_preselected_doc_type == 'CC')
												{
													echo "checked";
												} ?>></td>
                                            <td onclick=selDocType('CC')>Cédula</td>
                                        </tr>
                                        <tr id="ti_select">
                                            <td><input class="doc_type_sel_radio" type="radio" name="doc_type"
                                                       value="TI" <?php if ($keypad_preselected_doc_type == 'TI')
												{
													echo "checked";
												} ?>></td>
                                            <td onclick=selDocType('TI')>Tarjeta de Identidad</td>
                                        </tr>
                                        <tr id="rc_select">
                                            <td><input class="doc_type_sel_radio" type="radio" name="doc_type"
                                                       value="RC" <?php if ($keypad_preselected_doc_type == 'RC')
												{
													echo "checked";
												} ?>></td>
                                            <td onclick=selDocType('RC')>Registro Civil</td>
                                        </tr>
                                        <tr id="nu_select">
                                            <td><input class="doc_type_sel_radio" type="radio" name="doc_type"
                                                       value="NU" <?php if ($keypad_preselected_doc_type == 'NU')
												{
													echo "checked";
												} ?>></td>
                                            <td onclick=selDocType('NU')>NUIP</td>
                                        </tr>
                                        <tr id="ce_select">
                                            <td><input class="doc_type_sel_radio" type="radio" name="doc_type"
                                                       value="CE" <?php if ($keypad_preselected_doc_type == 'CE')
												{
													echo "checked";
												} ?>></td>
                                            <td onclick=selDocType('CE')>Cédula de Extranjería</td>
                                        </tr>
                                        <tr id="te_select">
                                            <td><input class="doc_type_sel_radio" type="radio" name="doc_type"
                                                       value="TE" <?php if ($keypad_preselected_doc_type == 'TE')
												{
													echo "checked";
												} ?>></td>
                                            <td onclick=selDocType('TE')>Tarjeta de Extranjería</td>
                                        </tr>
                                        <tr id="ms_select">
                                            <td><input class="doc_type_sel_radio" type="radio" name="doc_type"
                                                       value="MS" <?php if ($keypad_preselected_doc_type == 'MS')
												{
													echo "checked";
												} ?>></td>
                                            <td onclick=selDocType('MS')>Menor sin Identificación</td>
                                        </tr>
                                        <tr id="ni_select">
                                            <td><input class="doc_type_sel_radio" type="radio" name="doc_type"
                                                       value="NI" <?php if ($keypad_preselected_doc_type == 'NI')
												{
													echo "checked";
												} ?>></td>
                                            <td onclick=selDocType('NI')>NIT</td>
                                        </tr>
                                        <tr id="na_select">
                                            <td><input class="doc_type_sel_radio" type="radio" name="doc_type"
                                                       value="NA" <?php if ($keypad_preselected_doc_type == 'NA')
												{
													echo "checked";
												} ?>></td>
                                            <td onclick=selDocType('NA')>Otro</td>
                                        </tr>
                                    </table>
                                </td>

                            </tr>
                            <tr>
                                <td>
                                    <button class="keypad_num" type="button" onmousedown="numPad('4')" style=" ">4
                                    </button>
                                </td>
                                <td>
                                    <button class="keypad_num" type="button" onmousedown="numPad('5')" style=" ">5
                                    </button>
                                </td>
                                <td>
                                    <button class="keypad_num" type="button" onmousedown="numPad('6')" style=" ">6
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button class="keypad_num" type="button" onmousedown="numPad('7')" style=" ">7
                                    </button>
                                </td>
                                <td>
                                    <button class="keypad_num" type="button" onmousedown="numPad('8')" style=" ">8
                                    </button>
                                </td>
                                <td>
                                    <button class="keypad_num" type="button" onmousedown="numPad('9')" style=" ">9
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button class="keypad_num" type="button" onmousedown="numPad('0')" style=" ">0
                                    </button>
                                </td>
                                <td colspan="2">
                                    <button id="send_doc_button" class="keypad_num" type="button"
                                            onmousedown="numPad('SEND')"
                                            style="font-size: 35px; height:70px; width:145px;">ENVIAR
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <button id="i_have_no_doc" class="no_entered_doc" <?php if ($separate_get_doc_modes == 'true')
		{
			echo 'style="display: none;"';
		} ?> type="button" onmousedown="numPad('SEND_EMPTY')"><?php echo $strings["I_HAVE_NO_DOC_MESSAGE"]; ?></button>

		<?php
		if ($separate_get_doc_modes == 'true')
		{
			?>
            <button id="show_keypad_button" class="no_entered_doc" type="button"
                    onmousedown="showKeypad()"><?php echo $strings["I_HAVE_NO_PHYSICAL_DOC_MESSAGE"]; ?></button>
			<?php
		}

		?>

    </form>

	<?php
	if ($withPdf417Reader)
	{
		?>
        <div class="lector_doc_message_img"></div>
        <div class="flecha_lector"></div>
		<?php
	}
	?>

	<?php
	if ($separate_get_doc_modes == 'true')
	{
		?>
        <div id="separate_get_doc_volver_div" class="volver_div" style="display: none;">
            <input class="volver_btn_class" type="button" value="Volver" onmousedown="showInicial();">
        </div>

		<?php
	}

	if ($firstPage != 'getDoc')
	{
		?>
        <div class="volver_div">
            <input class="volver_btn_class" type="button" value="Volver" onmousedown="stopAndIndex();">
        </div>
		<?php
	}

	?>

    <div id="extras_1">
        <div id="inner_extras_1">
        </div>
    </div>

</center>

</body>

</html>
