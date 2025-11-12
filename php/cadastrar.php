<?php
header('Content-Type: application/json');
require_once 'descriptografar.php';

$resposta["status"] = "n";
$resposta["mensagem"] = "";

try {
    $payload_json = file_get_contents('php://input');
    $payload = json_decode($payload_json, true);

    $formData = descriptografar($payload);

} catch (Exception $e) {

    http_response_code(400);
    $resposta["mensagem"] = "Erro na descriptografia: " . $e->getMessage();
    die(json_encode($resposta));
}

$email = $formData['email'];
$senha = $formData['senha'];
$confirmacao_senha = $formData['confirmacao_senha'];
$nome = $formData['nome'];
$data_nascimento = $formData['data_nascimento'];
$contato = $formData['contato'];
$cep = $formData['cep'];
$nome_logradouro = $formData['nome_logradouro'];
$tipo_logradouro = $formData['tipo_logradouro'];
$numero_logradouro = $formData['numero_logradouro'];


if ($senha == ""){
     http_response_code(400);
    $resposta["mensagem"] = "Voce precisa colocar senha!";
    die(json_encode($resposta));
}

if ($email == ""){
     http_response_code(400);
    $resposta["mensagem"] = "voce precisa de um email!";
    die(json_encode($resposta));
}

if ($senha != $confirmacao_senha){
    http_response_code(400);
    $resposta["mensagem"] = "senhas não iguais, tente novamente!";
    die(json_encode($resposta));
}

$algoritmo_hash = PASSWORD_ARGON2ID;
$senha_hashsada = password_hash($senha, $algoritmo_hash);

$con = mysqli_connect("localhost:3306", "root", "", "bikes");

if (mysqli_connect_errno()) {
    http_response_code(500);
    $resposta["mensagem"] = "Falha ao conectar ao banco de dados.";
    die(json_encode($resposta));
}


mysqli_begin_transaction($con);
try{

    $stmt = mysqli_stmt_init($con);


    $query_endereco = "INSERT INTO endereco (cep, nome_logradouro, numero_residencia, tipo_logradouro) VALUES (?, ?, ?, ?);";
    mysqli_stmt_prepare($stmt, $query_endereco);
    mysqli_stmt_bind_param($stmt, 'ssss', $cep, $nome_logradouro, $numero_logradouro, $tipo_logradouro);
    $resultado_endereco = mysqli_stmt_execute($stmt);

    $id_endereco_gerado = mysqli_insert_id($con);


    $query_pessoa = "INSERT INTO pessoa (id_endereco, nome, data_nascimento, senha_login, email) VALUES (?, ?, ?, ?, ?);";
    mysqli_stmt_prepare($stmt, $query_pessoa);
    mysqli_stmt_bind_param($stmt, 'issss', $id_endereco_gerado, $nome, $data_nascimento, $senha_hashsada, $email);
    $resultado_pessoa = mysqli_stmt_execute($stmt);

    $id_pessoa_gerado = mysqli_insert_id($con);


    $query_contato = "INSERT INTO contato (id_pessoa, numero_contato) VALUES (?, ?);";    
    mysqli_stmt_prepare($stmt, $query_contato);
    mysqli_stmt_bind_param($stmt, 'is', $id_pessoa_gerado, $contato);
    $resultado_contato = mysqli_stmt_execute($stmt);

    $query_cliente = "INSERT INTO cliente (id_cliente, limite_credito) VALUES (?, ?)";
    mysqli_stmt_prepare($stmt, $query_cliente);
    $limite_credito = "500";
    mysqli_stmt_bind_param($stmt, 'is', $id_pessoa_gerado, $limite_credito);
    $resultado_cliente = mysqli_stmt_execute($stmt);


    if($resultado_pessoa == true and $resultado_endereco == true and $resultado_contato == true and $resultado_cliente == true){
        mysqli_commit($con);
        $resposta["status"] = "s";
        $resposta["mensagem"] = "cadastrado efetuado com sucesso";
    }else{
        throw new Exception("Cadastro falhou. Falta informação ou a informação é inválida, tente novamente!");
    }
    mysqli_stmt_close($stmt);


} catch (Exception $e){
    mysqli_rollback($con);

    http_response_code(500);
    $resposta["mensagem"] = "Cadastro falhou!";
}


mysqli_close($con);

$json = json_encode($resposta);
echo($json);




?>