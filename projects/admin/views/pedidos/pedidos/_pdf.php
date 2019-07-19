<?php
ob_end_clean();

use Winged\Formater\Formater;

require_once 'projects/admin/classes/tcpdf/tcpdf.php';

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Beesystem');
$pdf->SetTitle('Pedido MS BEBIDAS Nº ' . $pedido->primaryKey());
$pdf->SetSubject('Informações do pedido Nº ' . $pedido->primaryKey());
$pdf->SetKeywords('TCPDF, PDF, pedido, beesystem');
$pdf->SetPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->AddPage();
$pdf->setCellHeightRatio(2);


$tagvs = array('h1' => array(0 => array('h' => 1, 'n' => 3), 1 => array('h' => 1, 'n' => 2)),
    'h2' => array(0 => array('h' => '', 'n' => 1), 1 => array('h' => '', 'n' => 1)));
$pdf->setHtmlVSpace($tagvs);

$quantidade = 0;

$valor = 0;

$desconto = 0;

$trs = '';

if ($pedido_produto) {
    foreach ($pedido_produto as $produto) {
        $quantidade += $produto->quantidade;
        $valor += ($produto->valor_unico - ($produto->valor_unico * $produto->porcentagem_desconto / 100)) * $produto->quantidade;
        $desconto += $produto->porcentagem_desconto;

        $trs .= '<tr>
                    <td border="1">&nbsp;&nbsp;' . $produto->quantidade . '</td>
                    <td colspan="3" border="1">&nbsp;&nbsp;' . $produto->nome . '</td>
                    <td border="1">&nbsp;&nbsp;R$ ' . Formater::intToCurrency($produto->valor_unico) . '</td>
                    <td border="1">&nbsp;&nbsp;R$ ' . Formater::intToCurrency(($produto->valor_unico - ($produto->valor_unico * $produto->porcentagem_desconto / 100)) * $produto->quantidade) . '</td>
                 </tr>';

    }
}

$observacoes = ($pedido->observacoes == '') ? '' : '<p style="color: #ea1f33;"><b>Observações: </b>' . $pedido->observacoes . '</p>';

$valor += $pedido->valor_frete;

$valor = Formater::intToCurrency($valor);

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
<h2 style="font-size: 12px;">Pedido MS BEBIDAS Nº {$pedido->primaryKey()}</h2>
<table>
    <tr>
        <td colspan="2">&nbsp;&nbsp;Vendedor: {$pedido->extra()->usuario}</td>    
        <td>Telefone: {$pedido->extra()->telefone}</td>    
    </tr>
    <tr>
        <td colspan="2">&nbsp;&nbsp;Nome fantasia: {$pedido->extra()->nome_fantasia}</td>    
        <td>CNPJ: {$pedido->extra()->cnpj}</td>    
    </tr>
    <tr>
        <td colspan="2">&nbsp;&nbsp;Razão social: {$pedido->extra()->razao_social}</td>    
        <td>I.E: {$pedido->extra()->inscricao_estadual}</td>    
    </tr>
    <tr>
        <td colspan="2">&nbsp;&nbsp;Comprador: {$pedido->extra()->nome}</td>  
    </tr>
    <tr>
        <td colspan="3">&nbsp;&nbsp;Endereço: {$pedido->endereco}</td> 
    </tr>
    <tr>
        <td class="yellow" colspan="3">&nbsp;&nbsp;&nbsp;Data de entrega: {$pedido->agendamento}</td>   
    </tr>
</table>    
<br><br>
<table>
    <tr>
        <th class="weight" border="1">&nbsp;&nbsp;<b>Qtd</b></th>
        <th colspan="3" class="weight" border="1">&nbsp;&nbsp;<b>Produto</b></th>
        <th class="weight" border="1">&nbsp;&nbsp;<b>Valor</b></th>
        <th class="weight" border="1">&nbsp;&nbsp;<b>Total</b></th>
    </tr>
    {$trs}  
    <tr>
        <th class="weight" colspan="4"></th>
        <th class="weight" border="1">&nbsp;&nbsp;<b>Valor total</b></th>
        <th class="weight" border="1">&nbsp;&nbsp;<b>R$ {$valor}</b></th>
    </tr>
</table>
<br>
{$observacoes}
EOF;

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->lastPage();
$pdf->Output('pedido_' . $pedido->primaryKey() . '.pdf', 'I');