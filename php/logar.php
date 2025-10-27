<?php
session_start();
header('Content-Type: application/json');

define('CHAVE_CRIPTOGRAFIA', 'f6905f9753fa2d65b731f4870b907674d31ed099ea61c91f5c29a7ec8a20d561');
define('ALGORITMO', 'AES-256-CBC');

function descriptografar($dados_base64) {
    $dados_combinados = base64_decode($dados_base64);
    $iv_len = openssl_cipher_iv_length(ALGORITMO);
    $iv = substr($dados_combinados, 0, $iv_len);
    $dados_criptografados = substr($dados_combinados, $iv_len);
    return openssl_decrypt($dados_criptografados, ALGORITMO, CHAVE_CRIPTOGRAFIA, 0, $iv);
}

$email = $_POST["email"];
$senha = $_POST["senha"];

$resposta["status"] = "n";
$resposta["mensagem"] = "";

$con = mysqli_connect("localhost:3306", "root", "", "cadastro");

if (mysqli_connect_errno()) {
    http_response_code(500);
    $resposta["mensagem"] = "Falha ao conectar ao banco de dados.";
    echo json_encode($resposta);
    exit;
}

$query = "SELECT senha_login, nome FROM pessoa WHERE email = ?";
$stmt = mysqli_stmt_init($con);

if (!mysqli_stmt_prepare($stmt, $query)) {
    http_response_code(500);
    $resposta["status"] = "n";
    $resposta["mensagem"] = "Erro ao preparar a consulta ao banco de dados.";
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($resultado->num_rows != 1) {
    http_response_code(404);
    $resposta["status"] = "n";
    $resposta["mensagem"] = "Usuário não encontrado. Verifique o email."; 
}

$usuario = $resultado->fetch_assoc();
$hash_do_banco = $usuario['senha_login'];

if (password_verify($senha, $hash_do_banco)) {

    $nome_puro = descriptografar($usuario['nome']);
            
    $resposta["status"] = "s";
    $resposta["mensagem"] = "Login efetuado com sucesso! Bem-vindo(a), " . $nome_puro . "!";

    $_SESSION['logado'] = true;
    $_SESSION['nome_usuario'] = $nome_puro;

    } else {
        http_response_code(401);
        $resposta["status"] = "n";
        $resposta["mensagem"] = "Senha incorreta. Tente novamente.";
    }
    
mysqli_stmt_close($stmt);

mysqli_close($con);
echo json_encode($resposta);
?>