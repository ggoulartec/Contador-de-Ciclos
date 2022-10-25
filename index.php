<?php
 function url(){
    return sprintf(
      "%s://%s%s",
      isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
      $_SERVER['SERVER_NAME'],
      $_SERVER['REQUEST_URI']
    );
  }
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Calendario</title>
    <link rel="stylesheet" href="<?=url()?>dycalendar.css">
    <link rel="stylesheet" href="<?=url()?>style.css">
    <style>
        .dycalendar-target-date-red {
            background: red;
            color     : #FFF !important;
        }
    </style>
</head>

<body>
    <div class="box2">
        <div class="container-lista">
            <ul class="form lista none" style="overflow-x: auto;"></ul>
            <a href="#" id="close" class="btn"><span>voltar</span></a>
        </div>
        <form class="form">
            <h2>Contador de Ciclos</h2>
            <div class="inputBox">
                <input type="date" name="data_inicial" required="required" style="z-index: 1">
                <span style="z-index: 1">Data de inicio do prazo</span>
                <em></em>
            </div>
            <div class="inputBox">
                <input type="number" name="intevalo_ciclos" required="required">
                <span>Intervalo entre os cliclos</span>
                <em></em>
            </div>
            <div class="inputBox">
                <input type="number" name="quantidade_ciclos" required="required" min="1">
                <span>Quantidade de Ciclos</span>
                <em></em>
            </div>
            <input type="submit" value="Gerar datas">
        </form>
    </div>
    <section>
        <div class="box">
            <div class="container">
                <div id="dycalendar"></div>
            </div>
        </div>
    </section>

    <script src="dycalendar.js"></script>

    <script>
        (async () => {
            const urlbase= '<?=url()?>';

            dycalendar.draw({ target: '#dycalendar', type: 'month', dayformat: 'full', prevnextbutton: 'show' })

            const calendario = document.querySelector("#dycalendar");
            const formulario = document.querySelector('form');

            const validacaoData = (dt) => {
                const validacao         = ['D', 'S', 'T', 'Q']
                const tdAll             = document.querySelectorAll('tbody tr td');
                const calendario_header = document.querySelector('.dycalendar-header');
                const options           = JSON.parse(calendario_header.getAttribute("data-option"));
                const tds               = Array.from(tdAll).filter((td) => (!validacao.includes(td.innerText) && td.innerText.trim()))

                const dia = dt.split('-')[2];
                const mes = dt.split('-')[1];
                const ano = dt.split('-')[0];

                const data = tds.map(element => {
                    if (element.innerText.padStart(2, '0') == dia && (options.month + 1) == mes && options.year == ano) {
                        return element;
                    } else { return false; }
                }).filter(data => data);

                return data;
            }

            const marcacao = (person) => {
                person.forEach((data) => {
                    if (Object.keys(data).length == 2) {
                        for (let index = 0; index < Object.keys(data).length; index++) {
                            if (Object.keys(data)[index] == "datas_acrecimos") {
                                const element = validacaoData(data.datas_acrecimos);
                                if (element.length != 0) element[0].classList.add("dycalendar-target-date-red");
                            }
                        }
                    } else {
                        const element = validacaoData(data.datas_sem_acrecimos);
                        if (element.length != 0) element[0].classList.add("dycalendar-target-date");
                    }
                })
            }

            formulario.addEventListener('submit', async event => {
                event.preventDefault();

                const data     = new FormData(formulario);
                const response = await fetch(urlbase + "api-calendario.php", { method: "post", body: data });
                const person   = await response.json();
                const lista    = document.querySelector('.lista');
                const close    = document.querySelector("#close");

                if (person.status == false) {
                    alert(person.message)
                }

                formulario.classList.add('none');
                lista.classList.remove('none');
                close.classList.remove('none');
                lista.innerHTML = "";

                person.forEach(data => {
                    const dia    = (data.datas_acrecimos) ? data.datas_acrecimos.split('-')[2] : data.datas_sem_acrecimos.split('-')[2];
                    const mes    = (data.datas_acrecimos) ? data.datas_acrecimos.split('-')[1] : data.datas_sem_acrecimos.split('-')[1];
                    const ano    = (data.datas_acrecimos) ? data.datas_acrecimos.split('-')[0] : data.datas_sem_acrecimos.split('-')[0];
                    const li     = document.createElement('li');
                    li.innerText = "Data: " + `${dia}-${mes}-${ano}`;

                    if (!data.datas_acrecimos) {
                        li.classList.add('white');
                    } else {
                        li.classList.add('red');
                    }

                    lista.append(li);
                })

                marcacao(person);

                const observer = new MutationObserver(mutation => { marcacao(person); });

                observer.observe(calendario, { childList: true })
            })

            const close = document.querySelector("#close");
            close.addEventListener("click", function () {
                const formulario = document.querySelector('form');
                const lista      = document.querySelector('.lista');
                formulario.classList.remove('none');
                lista.classList.add('none');
                close.classList.add('none');
                window.location.href = urlbase;
            })
        })();
    </script>
</body>

</html>