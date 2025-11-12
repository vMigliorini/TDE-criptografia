
function criptografar(form_dados){

        const dadosObjeto = Object.fromEntries(form_dados.entries())        //transforma o objeto formData em objeto simples
        const dadosJson = JSON.stringify(dadosObjeto)                      //transforma o objeto simples em vetores [chave][valor]

        const chaveSimetrica = CryptoJS.lib.WordArray.random(16)          //cria a chave simétrica
        const vi = CryptoJS.lib.WordArray.random(16)                     //gera o vetor de inicialização

        const dadosCriptografados = CryptoJS.AES.encrypt(dadosJson, chaveSimetrica, {       //criptograda os dados com a chave sim
            iv: vi,
            mode: CryptoJS.mode.CBC,
            padding: CryptoJS.pad.Pkcs7
        })

        const RSA_chavePublica = `-----BEGIN PUBLIC KEY-----
                                MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAij2IhJYRT03J5xAQL150
                                UkTstNU4CuWXD180IrzmVTA4liuxOzfDeuv7BREh+Q7UeIV7tHzMOaA1G3/4u0xD
                                Cm4fLWEqwikOEBc+qMCOFsLt8UtJpi1nlpsorrxvpu/uBU1OzbN+SD3hncHwKujy
                                da7zbhA6u/pOZnYO1A63g0e2x8cCNv+sNTZbfTlKnwtAdoQMavu82+1dhn6+R1hf
                                XULKpY5DChbCr7p9quUzCGnPtOwdDKiPtmq9scLNtnxjHfji89qSvUdicIKj4STN
                                5VoCTa2XL9l2qCPLGZAiiaKCyLAKO746gloynoQb+y2e+xCXdGEu2Vqv612eMs+i
                                XwIDAQAB
                                -----END PUBLIC KEY-----`                                               //chave gerada com openssl

        const criptografiaRSA = new JSEncrypt()                         //gera um novo RSA                                         
        criptografiaRSA.setPublicKey(RSA_chavePublica)                 //define a chave pública nele

        const chaveSimetricaBase64 = CryptoJS.enc.Base64.stringify(chaveSimetrica)                    //transforma a chave para texto
        const chaveSimetricaCriptografada = criptografiaRSA.encrypt(chaveSimetricaBase64)           //encripta a chave base64

        if (!chaveSimetricaCriptografada) {
            throw new Error("erro na criptografia da chave simétrica")
        }

        const payload = {                                               //vai ser passado para o php
            encryptedKey: chaveSimetricaCriptografada,                 //guarda a chave sim
            iv: CryptoJS.enc.Base64.stringify(vi),                    //guarda o iv na base 64
            data: dadosCriptografados.toString()                     //guarda os dados
        }

        return payload
}


async function cadastrar() {

    var form = document.getElementById("form-cadastro")
    var form_dados = new FormData(form)

    try {

        const payload = criptografar(form_dados)                //criptografa o form

        var resposta = await fetch("php/cadastrar.php", {       //envia os dados já criptografados para input
            method: "POST",
            body: JSON.stringify(payload),
            headers: {
                'Content-Type': 'application/json'
            }
        })

        var dados = await resposta.json()                       //array com o retorno do php

        if (dados.status == "s") {
            alert(dados.mensagem)
            window.location.href = 'pagina-login/index.html'    //redireciona para a pagina de login
        } else {
            alert(dados.mensagem)
        }

    } catch (error) {
        alert("Erro durante a criptografia o envio ou criptografia" + error.message)
    }
}




async function logar() {
    var form = document.getElementById("form-login")
    form_dados = new FormData(form)

    try {

    
        payload = criptografar(form_dados)

        var resposta = await fetch("../php/logar.php", {
            method: "POST",
            body: JSON.stringify(payload),
            headers: {
                'Content-Type': 'application/json'
                }
        })

        var dados = await resposta.json()

        if (dados.status == "s") {
            alert(dados.mensagem)
        } else {
            alert(dados.mensagem)
        }

    } catch (error) {
        alert("Erro durante a criptografia o envio ou criptografia" + error.message)
    }
}