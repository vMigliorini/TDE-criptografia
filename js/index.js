async function cadastrar(){
    var form = document.getElementById("form-cadastro")
    form_dados = new FormData(form)

    var resposta = await fetch("php/cadastrar.php", {
        method: "POST",
        body: form_dados
    })

    var dados = await resposta.json()

    if (dados.status == "s"){
        alert(dados.mensagen)
    }else{
        alert(dados.mensagen)
    }
    
}

async function logar() {
    var form = document.getElementById("form-login")
    form_dados = new FormData(form)

    var resposta = await fetch("../php/logar.php", {
        method: "POST",
        body: form_dados
    })

    var dados = await resposta.json()
    if (dados.status == "s"){
        alert(dados.mensagen)
    }else{
        alert(dados.mensagen)
    }

}


