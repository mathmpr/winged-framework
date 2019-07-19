<?php
ob_end_clean();

require_once 'projects/admin/classes/tcpdf/tcpdf.php';

$now = (new Date(time(), false))->dmy();

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Beesystem');
$pdf->SetTitle('Separação de produtos no estoque físico - ' . $now);
$pdf->SetSubject('Separação de produtos no estoque físico - ' . $now);
$pdf->SetKeywords('TCPDF, PDF, pedido, beesystem');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->AddPage();
$pdf->setCellHeightRatio(2);

$tagvs = array('h1' => array(0 => array('h' => 1, 'n' => 3), 1 => array('h' => 1, 'n' => 2)),
    'h2' => array(0 => array('h' => '', 'n' => 1), 1 => array('h' => '', 'n' => 1)));
$pdf->setHtmlVSpace($tagvs);


if (!empty($itens)) {
    foreach ($itens as $item) {
        $trs .= '<tr>
                    <td border="1">&nbsp;&nbsp;' . $item['quantidade'] . '</td>
                    <td colspan="5" border="1">&nbsp;&nbsp;' . $item['produto'] . '</td>                    
                 </tr>';
    }
}

/**
 * @var $pedido Pedidos
 * @var $pp PedidosProdutos
 */

$html = <<<EOF
<style>
    @import url('https://fonts.googleapis.com/css?family=Roboto');
    body, html, div, p, h1, h2, h3, h4, h5, span, em, i, video, img, :after, :before, td{
        font-family: 'Roboto', sans-serif;
    }
    .yellow{
        background-color: #ffd22a;
    }
    .border{
        border: 1px solid #313131;
    }
</style>
<h2 style="font-size: 14px;">Separação de produtos no estoque físico - {$now}</h2>
<table>
    <tr>
        <th border="1"><b>&nbsp;&nbsp;Quantidade</b></th>
        <th colspan="5" border="1"><b>&nbsp;&nbsp;Produto</b></th>
    </tr>
    {$trs}
</table>
<br>
EOF;

if ($pedidos) {
    foreach ($pedidos as $pedido) {

        $observacoes = ($pedido->observacoes == '') ? '' : '<p style="color: #ea1f33;"><b>Observações: </b>' . $pedido->observacoes . '</p><br>';

        $html .= <<<EOF
{$observacoes}
EOF;
    }
}


$pdf->writeHTML($html, true, false, true, false, '');
$pdf->lastPage();
$pdf->Output('pedido_' . $pedido->primaryKey() . '.pdf', 'I');