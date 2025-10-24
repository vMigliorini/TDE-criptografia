async function cadastrar(){
    var form = document.getElementById("form-cadastro")
    dados = new FormData(form)

    var resposta = await fetch("php/cadastrar.php", {
        method: "POST",
        body: dados
    })

    
}

async function logar() {
    var form = document.getElementById("form-login")
    dados = new FormData(form)

    var resposta = await fetch("../php/logar.php", {
        method: "POST",
        body: dados
    })
}