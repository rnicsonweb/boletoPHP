<?php
    $mysqli = new mysqli("localhost", "unidas_unidas", "unidas2018", "unidas_intranet");
    $query = "SELECT * FROM gerar_boleto";
    $query2 = "SELECT * FROM boletos_gerados ORDER BY id DESC";
    $result = $mysqli->query($query);
    $result2 = $mysqli->query($query2);
    $row2 = $result2->fetch_assoc();
    
    $nosso_Numero = $row2['nosso_numero'];
    while ($row = $result->fetch_assoc()) {
    $valor = $row['valor_boleto'];
    $cliente = $row['cliente'];
    $cnpj = $row['cnpj'];
    $email = $row['email'];
    $documento = $row['documento'];
    $vencimento = $row['data_vencimento'];
    $data_vencimento = date("d/m/Y",strtotime("$vencimento"));
    
    $documento = $row['data_documento'];
    $data_documento = date("d/m/Y",strtotime("$documento"));
    $vencimento_remessa = date("Y-m-d",strtotime("$vencimento"));
    $documento_remessa = date("Y-m-d",strtotime("$documento"));
    $endereco = $row['endereco'];
    $bairro = $row['bairro'];
    $cep = $row['cep'];
    $cidade = $row['cidade'];
    $estado = $row['estado'];
    $id = $row['id'];
    $data = date('m');
    $numero_Documento = "CONT-".date('m')."/".date('Y');
    $posicao_Numero = $nosso_Numero+1;
    $data_atual = date("Y");
    $registro = $posicao_Numero;

// DADOS DO BOLETO PARA O SEU CLIENTE
$dias_de_prazo_para_pagamento = 0;
$taxa_boleto = 0.00;
$data_venc = $data_vencimento;  // Prazo de X dias OU informe data: "13/04/2006"; 
$valor_cobrado = "2950,00"; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(",", ".",$valor_cobrado);
$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

$dadosboleto["nosso_numero"] = $posicao_Numero;  // Nosso numero - REGRA: M�ximo de 8 caracteres!
$dadosboleto["numero_documento"] = $numero_Documento;	// Num do pedido ou nosso numero
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = $data_documento; // Data de emiss�o do Boleto
$dadosboleto["data_processamento"] = $data_documento; // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor; 	// Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = $cliente .' - '. $cnpj;
$dadosboleto["endereco1"] = $endereco. '-' .$bairro;
$dadosboleto["endereco2"] = $cidade. '-'. $estado. '-  CEP:'. $cep;

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = "Filiação - Unidas";
$dadosboleto["demonstrativo2"] = "Contribuição REF. AGOSTO/2018";
$dadosboleto["demonstrativo3"] = "Unidas - https://www.unidas.org.br";
$dadosboleto["instrucoes1"] = "- Sr. Caixa, não receber após o vencimento";
$dadosboleto["instrucoes2"] = "";
$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: ricardo@unidas.org.br";
$dadosboleto["instrucoes4"] = "&nbsp; Emitido por Unidas Nacional";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "1";
$dadosboleto["valor_unitario"] = "$valor";
$dadosboleto["aceite"] = "S";		
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "R$";


// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


// DADOS DA SUA CONTA - SICREDI
$dadosboleto["agencia"] = "8351"; // Num da agencia, sem digito
$dadosboleto["conta"] = "19031";	// Num da conta, sem digito
$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

// DADOS PERSONALIZADOS - SICREDI
$dadosboleto["carteira"] = "109";

// SEUS DADOS
$dadosboleto["identificacao"] = "Unidas - União Nacional das Instituições de Auto Gestão em Saúde";
$dadosboleto["cpf_cnpj"] = "69275337000108";
$dadosboleto["endereco"] = "Alameda Santos, 1000 - 8ªAndar, Cerqueira César";
$dadosboleto["cidade_uf"] = "São Paulo / SP";
$dadosboleto["cedente"] = "União Nacional das Instituições de Autogestão em Saúde";

ob_start();

// NÃO ALTERAR!
include_once("include/funcoes_itau.php"); 
include("include/layout_sicredi.php");

$content = ob_get_clean();

// convert
require_once(dirname(__FILE__).'/html2pdf/html2pdf.class.php');
try
{
	$html2pdf = new HTML2PDF('P','A4','fr', array(0, 0, 0, 0));
	/* Abre a tela de impressão */
	//$html2pdf->pdf->IncludeJS("print(true);");
	
	$html2pdf->pdf->SetDisplayMode('real');
	
	/* Parametro vuehtml = true desabilita o pdf para desenvolvimento do layout */
	$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
	
	/* Abrir no navegador */
	//$html2pdf->Output('boleto.pdf');
	
	mkdir('boletos/'.date('m-Y'));
	
	/* Salva o PDF no servidor para enviar por email */
	$html2pdf->Output('boletos/'.date('m-Y').'/'.$cliente.'.pdf', 'F');
	
	/* Força o download no browser */
	//$html2pdf->Output('boleto'.$id.'.pdf', 'D');
}
catch(HTML2PDF_exception $e) {
	echo $e;
	exit;
	
}

// Inclui o arquivo class.phpmailer.php localizado na pasta class
require_once("PHPMailer_5.2.0/class.phpmailer.php");

// Inicia a classe PHPMailer
$mail = new PHPMailer(true);
 
// Define os dados do servidor e tipo de conexão
// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$mail->IsSMTP(); // Define que a mensagem será SMTP
 
try {
     $mail->Host = 'mail.unidas.org.br'; // Endereço do servidor SMTP (Autenticação, utilize o host smtp.seudomínio.com.br)
     $mail->SMTPAuth   = true;  // Usar autenticação SMTP (obrigatório para smtp.seudomínio.com.br)
     $mail->Port       = 587; //  Usar 587 porta SMTP
     $mail->Username = 'richard@unidas.org.br'; // Usuário do servidor SMTP (endereço de email)
     $mail->Password = '@Michele2018'; // Senha do servidor SMTP (senha do email usado)
 
     //Define o remetente
     // =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=    
     $mail->SetFrom('financeiro@unidas.org.br', 'UNIDAS'); //Seu e-mail
     $mail->AddReplyTo('financeiro@unidas.org.br', 'UNIDAS'); //Seu e-mail
     $mail->CharSet = 'utf-8';
     $mail->Subject = 'Boleto Unidas';//Assunto do e-mail
 
 
     //Define os destinatário(s)
     //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
     $mail->AddAddress($email, $cliente);
 
     //Campos abaixo são opcionais 
     //=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
     //$mail->AddCC('destinarario@dominio.com.br', 'Destinatario'); // Copia
     //$mail->AddBCC('destinatario_oculto@dominio.com.br', 'Destinatario2`'); // Cópia Oculta
     $mail->AddAttachment('boletos/'.date('m-Y').'/'.$cliente.'.pdf');      // Adicionar um anexo
 
 
     //Define o corpo do email
     $mail->MsgHTML("<p>Bom dia</p> 
<p>Prezada Filiada,</p>   
<p></p>  
<p>Segue em anexo boleto referente a contribuição UNIDAS 12/2018,</p>
<p></p> 
<p></p>  
<p></p>  
<p></p> 
<p></p> 
<p>Dúvidas estou à disposição</p> 
<p>Atenciosamente</p> 


<table border='0' cellpadding='0' cellspacing='0' style='width:347px;' width='347'>
	<tbody>
		<tr>
			<td style='width:205px;height:86px;'>
				<p>
					<img alt='dezembro laranja' height='68' src='https://www.unidas.org.br/boleto_filiada/gerar/imagens/unidas-mail.jpg' width='217' /></p>
			</td>
			<td style='width:142px;height:86px;'>
				<p>
					<strong>Ricardo Ferreira </strong></p>
				<p>
					<strong><em>Assistente Financeiro</em></strong></p>
				<p>
					<strong>11 3289.0855</strong></p>
				<p>
					<img alt='cid:image006.png@01D3889A.76D8FEF0' height='14' src='https://www.unidas.org.br/boleto_filiada/gerar/imagens/skype-mail.png' width='14' />live:ricardo_10835<u><a href='mailto:ricardo@unidas.org.br'>ricardo@unidas.org.br</a></u></p>
				<p>
					<a href='http://www.unidas.org.br/'>www.unidas.org.br</a></p>
			</td>
		</tr>
	</tbody>
</table>
<p>
	&nbsp;</p>
"); 
 
     ////Caso queira colocar o conteudo de um arquivo utilize o método abaixo ao invés da mensagem no corpo do e-mail.
     //$mail->MsgHTML(file_get_contents('arquivo.html'));
 
     $mail->Send();
     echo "Mensagem enviada com sucesso</p>\n";
 
    //caso apresente algum erro é apresentado abaixo com essa exceção.
    }catch (phpmailerException $e) {
      echo $e->errorMessage(); //Mensagem de erro costumizada do PHPMailer
}


	
$sql="INSERT INTO boletos_gerados (id,nosso_numero, numero_documento, data_vencimento, data_documento, valor_boleto, id_cliente) VALUES ('','$registro','$numero_Documento','$data_vencimento','$data_documento','$valor','$id')";
$insert = $mysqli->query($sql);

$sql2="INSERT INTO boleto_filiadas (id,nosso_numero, numero_documento, data_vencimento, data_documento, valor, nome_cliente, tipo_documento, documento, logradouro, bairro, cep, cidade, estado) VALUES ('','$registro','$numero_Documento','$vencimento_remessa','$documento_remessa','$valor','$cliente','CNPJ','$cnpj','$endereco', '$bairro', '$cep', '$cidade', '$estado')";
$insert2 = $mysqli->query($sql2);
}
