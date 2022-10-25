<?php

$data_inicial = isset($_POST['data_inicial']) ? $_POST['data_inicial'] : false;
$intevalo_ciclos = isset($_POST['intevalo_ciclos']) ? $_POST['intevalo_ciclos'] : false;
$quantidade_ciclos = isset($_POST['quantidade_ciclos']) ? $_POST['quantidade_ciclos'] : false;

if (!$data_inicial || !$intevalo_ciclos || !$quantidade_ciclos) {
    $data['status'] = false;
    $data['message'] = "Preencha todos os campos!";
    echo json_encode($data);
    exit;
}

if ($intevalo_ciclos < 0 || $quantidade_ciclos < 0) {
    $data['status'] = false;
    $data['message'] = "Valores negativos não são validos!";
    echo json_encode($data);
    exit;
}


function generateDate($data_inicial, $nu_ciclos, $nu_prazo)
{
    $diasemana = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sabado');

    $date = new DateTime($data_inicial);
    $date->sub(new DateInterval('P1D'));
    $ciclo = new DateInterval('P' . $nu_ciclos . 'D');
    $prazo = new DatePeriod($date, $ciclo, $nu_prazo);

    $datas = array();

    foreach ($prazo as $chave => $data) {
        if ($chave != 0) :
            $diasemana_numero  = date('w', strtotime($data->format('Y-m-d')));
            $dados = array();
            $dados["datas_sem_acrecimos"] = $data->format('Y-m-d');

            if ($diasemana[$diasemana_numero] == "Sabado") :
                $newDate = new DateTime($dados["datas_sem_acrecimos"]);
                $newDate->add(new DateInterval('P2D'));
                $dados["datas_acrecimos"] = $newDate->format('Y-m-d');
            endif;

            if ($diasemana[$diasemana_numero] == "Domingo") :
                $newDate = new DateTime($dados["datas_sem_acrecimos"]);
                $newDate->add(new DateInterval('P1D'));
                $dados["datas_acrecimos"] = $newDate->format('Y-m-d');
            endif;

            $datas[] = $dados;
        endif;
    }
    return $datas;
};

echo json_encode(generateDate($data_inicial, $intevalo_ciclos, $quantidade_ciclos));
