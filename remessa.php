<?
include 'vendor/autoload.php';
    $mysqli = new mysqli("localhost", "unidas_unidas", "unidas2018", "unidas_intranet");
    $query = "SELECT * FROM boleto_filiadas WHERE remessa = 0";
    $result = $mysqli->query($query);
    $i = 0;

    $codigo_banco = Cnab\Banco::ITAU;
$arquivo = new Cnab\Remessa\Cnab400\Arquivo($codigo_banco);
$arquivo->configure(array(
    'data_geracao'  => new DateTime('2018-12-20'),
    'data_gravacao' => new DateTime('2018-12-20'), 
    'nome_fantasia' => 'Unidas', // seu nome de empresa
    'razao_social'  => 'União Nacional das Instituições de Autogestão em Saúde',  // sua razão social
    'cnpj'          => '69275337000108', // seu cnpj completo
    'banco'         => $codigo_banco, //código do banco
    'logradouro'    => 'Alameda Santos',
    'numero'        => '1.000 - 8° andar',
    'bairro'        => 'Cerqueira César', 
    'cidade'        => 'São Paulo',
    'uf'            => 'SP',
    'cep'           => '01418-100',
    'agencia'       => '8351', 
    'conta'         => '19031', // número da conta
    'conta_dac'     => '2', // digito da conta
));

while($row = $result->fetch_array()){
   
    $nosso_Numero = $row['nosso_numero'];
    $valor = $row['valor'];
    $numero_Documento = $row['numero_documento'];
    $nome_Cliente = $row['nome_cliente'];
    $tipo_Doc = 'cnpj';
    $documento = $row['documento'];
    $logradouro = $row['logradouro'];
    $data_Vencimento = $row['data_vencimento'];
    $data_Documento = $row['data_documento'];
    $bairro = $row['bairro'];
    $cep = $row['cep'];
    $cidade = $row['cidade'];
    $estado = $row['estado'];
    $id = $row['id'];
    
// você pode adicionar vários boletos em uma remessa
$arquivo->insertDetalhe(array(
    'codigo_de_ocorrencia' => 1, // 1 = Entrada de título, futuramente poderemos ter uma constante
    'nosso_numero'      => $nosso_Numero,
    'numero_documento'  => $numero_Documento,
    'carteira'          => '109',
    'especie'           => Cnab\Especie::ITAU_DUPLICATA_DE_SERVICO, // Você pode consultar as especies Cnab\Especie
    'valor'             => $valor, // Valor do boleto
    'instrucao1'        => 2, // 1 = Protestar com (Prazo) dias, 2 = Devolver após (Prazo) dias, futuramente poderemos ter uma constante
    'instrucao2'        => 0, // preenchido com zeros
    'sacado_nome'       => $nome_Cliente, // O Sacado é o cliente, preste atenção nos campos abaixo
    'sacado_razao_social'       => $nome_Cliente, // O Sacado é o cliente, preste atenção nos campos abaixo
    'sacado_tipo'       => 'cnpj', //campo fixo, escreva 'cpf' (sim as letras cpf) se for pessoa fisica, cnpj se for pessoa juridica
    'sacado_cpf'        => $documento,
    'sacado_cnpj'       => $documento,
    'sacado_logradouro' => $logradouro,
    'sacado_bairro'     => $bairro,
    'sacado_cep'        => $cep, // sem hífem
    'sacado_cidade'     => $cidade,
    'sacado_uf'         => $estado,
    'data_vencimento'   => $data_Vencimento,
    'data_cadastro'     => $data_Documento,
    'juros_de_um_dia'     => 0.00, // Valor do juros de 1 dia'
    'data_desconto'       => new DateTime('2018-12-20'),
    'valor_desconto'      => 00.0, // Valor do desconto
    'prazo'               => 10, // prazo de dias para o cliente pagar após o vencimento
    'taxa_de_permanencia' => '0', //00 = Acata Comissão por Dia (recomendável), 51 Acata Condições de Cadastramento na CAIXA
    'mensagem'            => 'Contribuição Mensal - Unidas Nacional',
    'data_multa'          => new DateTime('2018-12-30'), // data da multa
    'valor_multa'         => 00.0, // valor da multa
));


$query2 = "UPDATE boleto_filiadas set remessa = '1' WHERE id ='$id'";
$update = $mysqli->query($query2);
$i++;
}
// para salvar
$arquivo->save('remessa.txt');
?>