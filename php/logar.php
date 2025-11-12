<?php
session_start();                        //sessão para depois usar variáveis de sessão
header('Content-Type: application/json');

require_once 'descriptografar.php';

$resposta["status"] = "n";
$resposta["mensagem"] = "";

try {
    $payload_json = file_get_contents('php://input');
    $payload = json_decode($payload_json, true);

    $formData = descriptografar($payload);          //descriptografa o input

} catch (Exception $e) {

    http_response_code(400);
    $resposta["mensagem"] = "Erro na descriptografia: " . $e->getMessage();
    die(json_encode($resposta));
}




$email = $formData['email'];        //declara as variaveis a partir do formData
$senha = $formData['senha'];


$con = mysqli_connect("localhost:3306", "root", "", "bikes");       //connect

if (mysqli_connect_errno()) {
    http_response_code(500);
    $resposta["mensagem"] = "Falha ao conectar ao banco de dados.";
    die(json_encode($resposta));
}

$query = "SELECT senha_login, nome FROM pessoa WHERE email = ?";        //query buscando a senha do email
$stmt = mysqli_stmt_init($con);

if (!mysqli_stmt_prepare($stmt, $query)) {
    http_response_code(500);
    $resposta["mensagem"] = "Erro ao preparar a consulta ao banco de dados.";
    die(json_encode($resposta));
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);                         //pega o resultado da query

if ($resultado->num_rows != 1) {
    http_response_code(404);
    $resposta["mensagem"] = "Usuário não encontrado. Verifique o email.";
    die(json_encode($resposta)); 
}

$usuario = $resultado->fetch_assoc();
$hash_do_banco = $usuario['senha_login'];

if (password_verify($senha, $hash_do_banco)) {              //compara a senha do user com a salva no banco

    $nome = $usuario['nome'];

    $resposta["status"] = "s";
    $resposta["mensagem"] = "Login efetuado com sucesso! Bem-vindo(a), " . $nome . "!";     //uso da variável de sessão

    $_SESSION['logado'] = true;
    $_SESSION['nome_usuario'] = $nome;

    } else {
        http_response_code(401);
        $resposta["mensagem"] = "Senha incorreta. Tente novamente.";
    }
    
mysqli_stmt_close($stmt);

mysqli_close($con);
echo json_encode($resposta);
?>