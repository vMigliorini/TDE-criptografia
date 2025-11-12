Página de cadastro e login suportadas por um backend que criptografa de forma híbrida (criptografando os dados com uma chave simétrica e então criptografando a chave simétrica com RSA pública). Utilizei JSEncrypt e CryptoJS para criptografar a partir do front end. também
usei, no backend, OpenSSL para carregar chaves e descriptografar e MySQLi para inserir os dados no banco de dados.
