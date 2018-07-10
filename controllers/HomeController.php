<?php

use Winged\Controller\Controller;
use Winged\Image\Image;
use Winged\Form\Form;
use PagSeguro\Config;
use Winged\Http\Request;
use Winged\Winged;
use Winged\Formater\Formater;
use Winged\Utils\RandomName;
use Winged\File\File;

/**
 * Class HomeController
 */
class HomeController extends Controller
{
    /**
     * HomeController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function actionOrdemBolao()
    {
        $peoples = [
            'Rodrigo',
            'Luci Guerra',
            'Luci',
            'Gabs',
            'Are',
            'Val',
            'Ale',
            'João',
            'Matheus'
        ];

        $count = 1;

        ?>
        <h1>ORDEM DE PALPITES DO BOLÃO</h1>
        <?php

        while (count7($peoples) > 0) {
            $index = rand(0, count7($peoples) - 1);
            echo '#' . $count . ' - ' . $peoples[$index] . '<br>';
            $count++;
            $n = [];
            foreach ($peoples as $key => $people) {
                if ($key != $index) {
                    $n[] = $people;
                }
            }
            $peoples = $n;
        }
    }

    /**
     * @return array
     */
    public function actionGetCorreios()
    {
        return ['data' => new SimpleXMLElement((new Request(post('url'), [], [], false))->send()->output())];
    }

    public function actionGetCep()
    {

        $estados = [
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
        ];

        $request = new Request('http://apps.widenet.com.br/busca-cep/api/cep/' . Formater::toUrl(post('cep')) . '.json');
        $response = $request->send();
        if ($response->ok()) {
            $response = json_decode($response->output());
            $response->uf = $response->state;
            $response->state = $estados[$response->state];

            return ['status' => true, 'data' => [
                'bairro' => $response->district,
                'estado' => $response->state,
                'cidade' => $response->city,
                'rua' => $response->address
            ]];
        }
        return ['status' => false, 'data' => []];
    }


    public function actionCheckout()
    {

        include_once './Modules/PagSeguro/Autoload.php';

        Config::isSandBox();

        $estados = [
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
        ];

        $data = Winged::post();
        $data['senderCPF'] = str_replace([',', '.', '-'], '', Formater::toUrl($data['senderCPF'], Formater::KEEP_FORMAT));
        $data['creditCardHolderCPF'] = str_replace([',', '.', '-'], '', Formater::toUrl($data['creditCardHolderCPF'], Formater::KEEP_FORMAT));
        $data['creditCardNumber'] = str_replace([',', '.', '-'], '', Formater::toUrl($data['creditCardNumber'], Formater::KEEP_FORMAT));
        $data['billingAddressPostalCode'] = str_replace([',', '.', '-'], '', Formater::toUrl($data['billingAddressPostalCode'], Formater::KEEP_FORMAT));
        $data['shippingAddressPostalCode'] = str_replace([',', '.', '-'], '', Formater::toUrl($data['shippingAddressPostalCode'], Formater::KEEP_FORMAT));
        $data['paymentMode'] = Config::getPaymentMode();
        $data['paymentMode'] = Config::getPaymentMode();
        $data['notificationURL'] = Winged::$protocol . 'home/notification/';
        $data['reference'] = RandomName::generate('sisisi', true, false);
        $data['receiverEmail'] = Config::getEmail();
        $data['extraAmount'] = '0.00';
        $data['currency'] = 'BRL';

        $data['billingAddressState'] = get_key_by_value($data['billingAddressState'], $estados);
        $data['shippingAddressState'] = get_key_by_value($data['shippingAddressState'], $estados);

        switch ($data['paymentMethod']) {
            case 'credit_card':
                $data['paymentMethod'] = 'creditCard';
                break;
            default:
                break;
        }

        //pre_clear_buffer_die($data);

        $request = new Request('https://ws.' . Config::getSandbox() . 'pagseguro.uol.com.br/v2/checkout?token=' . Config::getToken() . '&email=' . Config::getEmail() . '',
            $data,
            [
                'headers' => [
                    'Content-Type: application/x-www-form-urlencoded; charset=ISO-8859-1'
                ],
                'type' => Request::$REQUEST_POST,
            ], false);
        $response = $request->send();
        if ($response->ok()) {
            $response = new \SimpleXMLElement($response->output());
            pre_clear_buffer_die($response);
        } else {
            pre_clear_buffer_die([$response->output(), $data]);
        }
    }

    public function actionNotification(){

        header("access-control-allow-origin: https://sandbox.pagseguro.uol.com.br");
        $file = new File('./notifications/' . time() . '.json', true);
        $file->write(json_encode($_POST) . json_encode($_GET));
    }

    public function actionIndex()
    {


        $data = exif_read_data('./kkkk.jpg');

        $this->renderHtml('home');

        exit;

        include_once './Modules/PagSeguro/Autoload.php';

        Config::isSandBox();
        Config::addRequiredJs($this);
        $this->addJs('jquery', './Modules/PagSeguro/Assets/jquery.js');
        $this->addJs('pagseguro', './Modules/PagSeguro/Assets/pagseguro.example.js');
        $this->addCss('bootstrap', './Modules/PagSeguro/Assets/bootstrap.css');
        $this->addCss('pagseguro', './Modules/PagSeguro/Assets/pagseguro.example.css');


        $this->renderHtml('pagseguro');

        exit;

        $usuarios = new Usuarios();
        $form = new Form($usuarios);
        $form->begin();

        $form->addInput('nome', 'radio', [
            'values' => [
                0 => 'ADM',
                1 => 'FUN',
            ]
        ]);

        $form->end();


        //$ftp = new Ftp('pradoit-com-br.umbler.net', 'pradoit-com-br', 'QWEqwe123');

        //$ftp->up();

        //pre_clear_buffer_die($ftp);

        //$ftp->access('public/admin/assets/css/core/files/');

        //$ftp->put('./cacau.png', './now/gety/images/cacau.png', true);

        //$ftp->rmdir('now');

        //pre_clear_buffer_die($ftp->currentDir);

        //$ftp->rmdir('now');

        //$ftp->put('./cacau.png', './now/try/create/cacau.png', true);

        //$articles = new News();
        //$articles = $articles->select()->from(['fg' => News::tableName()])->find();

        //$component = new Components('Home', ComponentParser::getComponent('./views/components/', 'Site\Components\Home', [
        //    'articles' => $articles
        //]));

        //$component->get('Home')->begin();

        //$component->get('Home')->free();

    }

    public function actionMyImage()
    {
        $image = new Image('http://www.tempie.com.br/assets/images/cacau.png', false);
        $image->printable();
    }

}