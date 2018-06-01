<?php class FakeMailpoetLista extends Model
{
    public function __construct()
    {
        parent::__construct();

        $this->initial_html = '<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
    <title>Teste</title>
    <meta content=text/html;charset=utf-8 http-equiv=content-type>
    <meta name=viewport content="width=device-width, initial-scale=1.0">
    <style>
        body {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            width: 100% !important;
            padding: 0;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        img {
            text-decoration: none;
            outline-style: none;
            -ms-interpolation-mode: bicubic
        }

        td {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%
        }

        p {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%
        }

        a {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%
        }

        li {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%
        }

        blockquote {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%
        }

    </style>
</head>
<body><img src="'. Winged::$protocol .'admin/fake-mailpoet-lista/update-state?id_lista=%replace_lista%&id_pessoa=%replace_pessoa%">';

        $this->final_html = '<table style="border-collapse: collapse; width: 600px; max-width: 600px; min-width: 600px; margin: auto; display: block; text-align: left"
       width="600" border="0">
    <tbody>
    <tr>
        <td style="font-size: 12px; color: #333; text-align: center; border: 0px; padding: 0px;" border="0" colspan="1" width="600">
            <br><br>
            <a href="'. Winged::$protocol .'admin/fake-mailpoet-lista/cancel-signature?id_lista=%replace_lista%&id_pessoa=%replace_pessoa%">Cancelar Assinatura</a>
        </td>
    </tr>
    </tbody>
    </table>    
</body>
</html>';

        return $this;
    }

    public $initial_html = false;

    public $final_html = false;

    /** @var $id_lista integer */
    public $id_lista;
    /** @var $data_envio string */
    public $data_envio;
    /** @var $titulo string */
    public $titulo;
    /** @var $rodape string */
    public $rodape;
    /** @var $topo string */
    public $topo;
    /** @var $canceladas string */
    public $canceladas;

    public $grupos = null;

    public $mails = null;

    public static function tableName()
    {
        return "fake_mailpoet_lista";
    }

    public static function primaryKeyName()
    {
        return "id_lista";
    }

    public function primaryKey($pk = false)
    {
        if ($pk && (is_int($pk) || intval($pk) != 0)) {
            $this->id_lista = $pk;
            return $this;
        }
        return $this->id_lista;
    }

    public function behaviors()
    {
        return [
            'data_envio' => function () {
                return (new CoreDate())->sql();
            },
            'rodape' => function(){
                return htmlentities($this->rodape);
            },
            'topo' => function(){
                return htmlentities($this->topo);
            }
        ];
    }

    public function reverseBehaviors()
    {
        return [
            'rodape' => function(){
                return html_entity_decode($this->rodape);
            },
            'topo' => function(){
                return html_entity_decode($this->topo);
            }
        ];
    }

    public function labels()
    {
        return [
            'titulo' => 'Título do e-mail: ',
            'topo' => 'Topo do documento: ',
            'rodape' => 'Rodapé do documento: ',
            'grupos' => 'Para quais grupos o e-mail deve ser enviado: '
        ];
    }

    public function rules()
    {
        return [
            'grupos' => [
                'safe' => 'safe'
            ]
        ];
    }
}