<?php
function descriptografar($payload){
    

    if (empty($payload['encryptedKey']) || empty($payload['iv']) || empty($payload['data'])) {
        throw new Exception("Payload incompleto. Dados de criptografia ausentes.");
    }

    $chaveCriptografada_base64 = $payload['encryptedKey'];
    $vi_base64 = $payload['iv'];
    $dadosCriptografados_base64 = $payload['data'];

    try {
        $chavePrivadaPEM = file_get_contents("../chaves/private_key.pem");

        $chavePrivada = openssl_pkey_get_private($chavePrivadaPEM); 

        if (!$chavePrivadaPEM) {
            throw new Exception('Falha ao carregar a chave privada RSA.');
        }

        $chaveCriptografadaRSA_raw = base64_decode($chaveCriptografada_base64);

        openssl_private_decrypt(
            $chaveCriptografadaRSA_raw,
            $chaveSimetricaDescriptografada_base64,
            $chavePrivada,
            OPENSSL_PKCS1_PADDING
        );

        if ($chaveSimetricaDescriptografada_base64 === null) {
            throw new Exception('Falha ao descriptografar a chave simétrica (RSA).');
        }

        $chaveAES_raw = base64_decode($chaveSimetricaDescriptografada_base64);
        $vi_raw = base64_decode($vi_base64);

        $jsonDescriptografado = openssl_decrypt(
            $dadosCriptografados_base64,
            'AES-128-CBC',
            $chaveAES_raw,
            OPENSSL_ZERO_PADDING,
            $vi_raw,
        );

        $jsonDescriptografado = rtrim($jsonDescriptografado, "\0..\16");

        $formData = json_decode($jsonDescriptografado, true);

        if ($formData === null) {
            throw new Exception('Falha ao decodificar o JSON dos dados (dados corrompidos).');
        }

    } catch (Exception $e) {
        http_response_code(500);
        $resposta["mensagem"] = "Erro na descriptografia: " . $e->getMessage();
        die(json_encode($resposta));
    }
    return $formData;

}

?>