<?php

namespace UsadosBR;

/**
 * UsadosBR API v1.
 *
 * TERMS OF USE:
 * - This code is in no way affiliated with, authorized, maintained, sponsored
 *   or endorsed by UsadosBR or any of its affiliates or subsidiaries. This is
 *   an independent and unofficial API. Use at your own risk.
 * - We do NOT support or tolerate anyone who wants to use this API to send spam
 *   or commit other online crimes.
 *
 */
class UsadosBR {

    /**
     * Rest url
     *
     * @var string
     * */
    protected $_url = 'https://www.usadosbr.com/';
    
    protected $mCurl = null;

    protected $_userAgent = 'Dalvik/2.1.0 (Linux; U; Android 6.0.1; XT1225 Build/MPGS24.107-70.2-7)';
    protected $_accessType = 'central';
    
    
    /**
     * config to all requests
     *
     * @var array
     * */
    private static $cfg = [];
    
    
    private $cookieJar = null;

    public function __construct($data = null) {


        if (empty($data))
            throw new Exception("Empty data in __construct");

        if (is_array($data)) {

            if (isset($data['email'])) {

                if (empty($data['email']))
                    throw new Exception("Empty data[email]");
                if (empty($data['password']))
                    throw new Exception("Empty data[password]");

                $this->cookieJar = tempnam('/tmp','cookie-usadosbr-'.date('YmdHis'));
                
                self::$cfg['email'] = strtoupper($data['email']);
                self::$cfg['password'] = $data['password'];
                self::$cfg['token'] = $this->getToken();
                
                $this->_accessType = $data['access_type'];

            } else {
                throw new Exception("Error data in __construct");
            }
        }
    }

    private function getToken() {
        $_request = $this->request($this->_url . "login")
                ->addHeader('Content-Type', 'text/html')
                ->setDebug()
                ->addFieldsGet([
                    ['type' => 'name', 'desc' => '_token']
                ])
                ->getResponse();
        
        return $_request['fields']['_token'];
    }

    public function login() {
        $this->mCurl = curl_init($this->_url .'logar');
        curl_setopt($this->mCurl, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($this->mCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->mCurl, CURLOPT_COOKIEJAR, $this->cookieJar);
        curl_setopt($this->mCurl, CURLOPT_POSTFIELDS, '_token=' . self::$cfg['token'] . '&email='.self::$cfg['email'].'&password='.self::$cfg['password']);
        $page = curl_exec($this->mCurl);
        curl_close($this->mCurl);
    }
    
    public function verificaDados()
    {
        $this->mCurl = curl_init($this->_url . $this->_accessType . '/verifica-dados');
        curl_setopt($this->mCurl, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($this->mCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->mCurl, CURLOPT_COOKIEFILE, $this->cookieJar);
        curl_setopt($this->mCurl, CURLOPT_POSTFIELDS, 'referer='.$this->_url.'login');
        $page = curl_exec($this->mCurl);
//
//        //DOM Resp
//        $oDom = new \UsadosBR\simple_html_dom();
//        $oDom->load($page);
//        $resetcontent = $oDom->getElementById("sf-resetcontent");                
        curl_close($this->mCurl);
    }
    
    public function getMeusDados()
    {     
        // retrieve account balance
//        $this->mCurl = curl_init($this->_url .'dashboard/meus-dados');
//        $this->mCurl = curl_init($this->_url .'central/meus-dados');
       
        $this->mCurl = curl_init($this->_url . $this->_accessType . '/meus-dados');
        curl_setopt($this->mCurl, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($this->mCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->mCurl, CURLOPT_COOKIEFILE, $this->cookieJar);
        $page = curl_exec($this->mCurl);
        curl_close($this->mCurl);

        //DOM Resp
        $oDom = new \UsadosBR\simple_html_dom();
        $oDom->load($page);

        if($this->_accessType == 'dashboard')
        {
            $dados = [
                'nome' => $oDom->find('[name="name"]', 0)->value,
                'email' => $oDom->find('[name="email"]', 0)->value,
                'endereco' => $oDom->find('[name="address"]', 0)->value,
                'cpf' => $oDom->find('[name="cpf"]', 0)->value,
                'rg' => $oDom->find('[name="rg"]', 0)->value,
                'dt_nascimento' => $oDom->find('[name="birth"]', 0)->value,
                'cep' => $oDom->find('[name="zipcode"]', 0)->value,
                'complemento' => $oDom->find('[name="complement"]', 0)->value,
                'bairro' => $oDom->find('[name="neighborhood"]', 0)->value,
                'cidade' => $oDom->find('[name="city_id"]', 0)->value,
                'estado' => $oDom->find('[name="state_id"]', 0)->value,
                'celular' => $oDom->find('[name="cellphone"]', 0)->value,
                'fone2' => $oDom->find('[name="telephone_two"]', 0)->value,
                'fone3' => $oDom->find('[name="telephone_three"]', 0)->value,
                'info' => $oDom->find('[name="info"]', 0)->value
            ];
        }
        else
        {
            $dados = [
                'nome' => $oDom->find('[name="name"]', 0)->value,
                'horario_funcionamento' => $oDom->find('[name="business_hours"]', 0)->value,
                'telefone' => $oDom->find('[name="phone"]', 0)->value,
                'celular' => $oDom->find('[name="cellphone"]', 0)->value,
                'whatsapp' => (($oDom->find('[name="can_call"]', 0)->value == '0')?"Sim":"Não"),
                'telefone_2' => $oDom->find('[name="telephone_two"]', 0)->value,
                'telefone_3' => $oDom->find('[name="telephone_three"]', 0)->value,
                'outros_contatos' => $oDom->find('[name="other_contacts"]', 0)->value,
                'cep' => $oDom->find('[name="zipcode"]', 0)->value,
                'endereco' => $oDom->find('[name="address"]', 0)->value,
                'numero' => $oDom->find('[name="number"]', 0)->value,
                'complemento' => $oDom->find('[name="complement"]', 0)->value,
                'bairro' => $oDom->find('[name="neighborhood"]', 0)->value,
                'estado' => $oDom->find('[name="state_id"]', 0)->value,
                'cidade' => $oDom->find('[name="city_id"]', 0)->value,
                'latitude' => $oDom->find('[name="lat"]', 0)->value,
                'longitude' => $oDom->find('[name="lng"]', 0)->value,
                'email' => $oDom->find('[name="email"]', 0)->value,
                'cpf' => $oDom->find('[name="cpf"]', 0)->value,
                'codigo' => $oDom->find('[name="client_code"]', 0)->value,
                'nickname' => $oDom->find('[name="nickname"]', 0)->value,
                'email_cobranca' => $oDom->find('[name="billing_email"]', 0)->value
            ];
        }

        return $dados;
    }

    public function removeCookieJar() {
        //unlink($this->cookieJar) or die("Can't unlink ".$this->cookieJar);
    }

    /**
     *
     * Used internally, but can also be used by end-users if they want
     * to create completely custom API queries without modifying this library.
     *
     * @param string $url
     *
     * @return array
     */
    public function request($url) {
        return new Request($this, $url);
    }
}
