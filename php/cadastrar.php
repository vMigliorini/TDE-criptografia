<?php
header('Content-Type: application/json');

define('CHAVE_CRIPTOGRAFIA', 'f6905f9753fa2d65b731f4870b907674d31ed099ea61c91f5c29a7ec8a20d561');
define('ALGORITMO', 'AES-256-CBC');

function criptografar($dados) {
    $iv_len = openssl_cipher_iv_length(ALGORITMO);
    $iv = random_bytes($iv_len);
    $dados_criptografados = openssl_encrypt($dados, ALGORITMO, CHAVE_CRIPTOGRAFIA, 0, $iv);
    return base64_encode($iv . $dados_criptografados);
}


$email = $_POST["email"];
$nome = $_POST["nome"];
$senha = $_POST["senha"];
$confirmacao_senha = $_POST["confirmacao_senha"];
$data_nascimento = $_POST["data_nascimento"];
$contato = $_POST["contato"];
$cep = $_POST["cep"];
$nome_logradouro = $_POST["nome_logradouro"];
$numero_logradouro = $_POST["numero_logradouro"];
$tipo_logradouro = $_POST["tipo_logradouro"];

$resposta["status"] = "n";
$resposta["mensagem"] = "";

$nome_criptografado = criptografar($nome);
$data_nascimento_criptografada = criptografar($data_nascimento);
$contato_criptografado = criptografar($contato);
$cep_criptografado = criptografar($cep);
$nome_logradouro_criptografado = criptografar($nome_logradouro);
$numero_logradouro_criptografado = criptografar($numero_logradouro);
$tipo_logradouro_criptografado = criptografar($tipo_logradouro);

if ($senha != $confirmacao_senha){
    http_response_code(400);
    $resposta["status"] = "n";
    $resposta["mensagem"] = "senhas não iguais, tente novamente!";
    $json = json_encode($resposta);
    echo($json);
    exit;
}

$algoritmo_hash = PASSWORD_ARGON2ID;
$senha_hashsada = password_hash($senha, $algoritmo_hash);

$con = mysqli_connect("localhost:3306", "root", "PUC@1234", "cadastro");


mysqli_begin_transaction($con);
try{

    $stmt = mysqli_stmt_init($con);


    $query_endereco = "INSERT INTO endereco (cep, nome_logradouro, numero_residencia, tipo_logradouro) VALUES (?, ?, ?, ?);";
    mysqli_stmt_prepare($stmt, $query_endereco);
    mysqli_stmt_bind_param($stmt, 'ssss', $cep_criptografado, $nome_logradouro_criptografado, $numero_logradouro_criptografado, $tipo_logradouro_criptografado);
    $resultado_endereco = mysqli_stmt_execute($stmt);

    $id_endereco_gerado = mysqli_insert_id($con);


    $query_pessoa = "INSERT INTO pessoa (id_endereco, nome, data_nascimento, senha_login, email) VALUES (?, ?, ?, ?, ?);";
    mysqli_stmt_prepare($stmt, $query_pessoa);
    mysqli_stmt_bind_param($stmt, 'issss', $id_endereco_gerado, $nome_criptografado, $data_nascimento_criptografada, $senha_hashsada, $email);
    $resultado_pessoa = mysqli_stmt_execute($stmt);

    $id_pessoa_gerado = mysqli_insert_id($con);


    $query_contato = "INSERT INTO contato (id_pessoa, numero_contato) VALUES (?, ?);";    
    mysqli_stmt_prepare($stmt, $query_contato);
    mysqli_stmt_bind_param($stmt, 'is', $id_pessoa_gerado, $contato_criptografado);
    $resultado_contato = mysqli_stmt_execute($stmt);


    if($resultado_pessoa == true and $resultado_endereco == true and $resultado_contato == true){
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
    $resposta["status"] = "n";
    $resposta["mensagem"] = "Cadastro falhou!";
}


mysqli_close($con);

$json = json_encode($resposta);
echo($json);




?>