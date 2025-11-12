<?php
function descriptografar($payload){
    
    if (empty($payload['encryptedKey']) || empty($payload['iv']) || empty($payload['data'])) {      //verifica se o payload está completo
        throw new Exception("Payload incompleto. Dados de criptografia ausentes.");
    }

    $chaveCriptografada_base64 = $payload['encryptedKey'];          //pega o que passamos no payload
    $vi_base64 = $payload['iv'];
    $dadosCriptografados_base64 = $payload['data'];

    
    $chavePrivadaPEM = file_get_contents("../chaves/private_key.pem");      //pega a chave privada do servidor em texto
    $chavePrivada = openssl_pkey_get_private($chavePrivadaPEM);             //interpreta o texto e transforma em um recurso de chave especial

    if (!$chavePrivada) {
        throw new Exception('Falha ao carregar a chave privada RSA.');
    }

    $chaveCriptografadaRSA_raw = base64_decode($chaveCriptografada_base64);     //descriptografa a chave sim

    openssl_private_decrypt(
        $chaveCriptografadaRSA_raw,                 //source
        $chaveSimetricaDescriptografada_base64,     //variavel que vai armazenar
        $chavePrivada,                              //chave privada que serve para destrancar o que a pública trancou
        OPENSSL_PKCS1_PADDING                      
    );

    if ($chaveSimetricaDescriptografada_base64 === null) {
        throw new Exception('Falha ao descriptografar a chave simétrica (RSA).');
    }

    $chaveAES_raw = base64_decode($chaveSimetricaDescriptografada_base64);      //devolve ao formato de bytes
    $vi_raw = base64_decode($vi_base64);                                       //devolve ao formato de bytes

    $jsonDescriptografado = openssl_decrypt(        //descriptografa os dados e devolve para o formato JSON
        $dadosCriptografados_base64,
        'AES-128-CBC',
        $chaveAES_raw,
        OPENSSL_ZERO_PADDING,
        $vi_raw
    );

    $jsonDescriptografado = rtrim($jsonDescriptografado, "\0..\16");        //remove o padding
    $formData = json_decode($jsonDescriptografado, true);                   //transforma o JSON em um vetor

    if ($formData === null) {
        throw new Exception('Falha ao decodificar o JSON dos dados (dados corrompidos).');
    }

    return $formData;
}

?>