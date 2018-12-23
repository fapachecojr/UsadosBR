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
	* Rest API
	*
	* @var string
	**/
	protected $_api = 'https://api.vmotors.com.br';

	/**
	* Upload API
	*
	* @var string
	**/
	protected $_upload = 'https://upload.webmotors.com.br';


	/**
	* config to all requests
	*
	* @var array
	**/
	private static $cfg = [];


	/**
	* basic pre login
	*
	* @var string
	**/
	private static $_username 	= 'webmotors';
	private static $_password 	= 'aA123456';
	private static $_grant_type = 'password';


	/**
	* Mobile config
	*
	* @var array
	**/
	private static $_mobile 	= [
		'so' 	=> 'Android',
		'vapp' 	=> '2.7.4',
		'vso' 	=> '6.0.1 (MARSHMALLOW 23)',
		'db' 	=> 'motorola',
		'dm' 	=> 'XT1225'
	];


	/**
	* @var array
	*		   grant_type
	*		   password
	*		   username
	*		   email
	*		   senha
	*
	* @var string
	*		   token
	**/
	public function __construct($data = null){


		if(empty($data)) throw new Exception("Empty data in __construct");

		if(is_array($data)){

			if(isset($data['username'])){

				if(empty($data['username'])) throw new Exception("Empty data[username]");
				if(empty($data['password'])) throw new Exception("Empty data[password]");

				self::$cfg['username'] 	= strtoupper($data['username']);
				self::$cfg['password'] 	= $data['password'];
				self::$cfg['uniqid'] 	= uniqid();

			} else if($data['token']){

				self::$cfg['token'] = 'Bearer ' . $data['token'];
				self::$cfg['hash'] 	= $data['hash'];

			} else {
				throw new Exception("Error data in __construct");
			}

		}

    }



    private function _preLogin(){

		$_url = $this->_api . '/plataformarevendedor/token'; 

		$_request = $this->request($_url)
							->addHeader('Content-Type', 'application/x-www-form-urlencoded')
				            ->addPost('username', self::$_username)
				            ->addPost('password', self::$_password)
				            ->addPost('grant_type', self::$_grant_type)
				            ->setIsToken()
				            ->getResponse();


		return $_request;
		
	}


	public function login(){


		$_preLogin = $this->_preLogin();

		$_url = $this->_api . '/plataformarevendedor/v2/acesso'; 

		$_request = $this->request($_url)
                                            ->addHeader('Content-Type', 'application/json')
                                            ->addHeader('Authorization', 'Bearer '.$_preLogin['body']['access_token'])
				            ->addPost('email', 		self::$cfg['username'])
				            ->addPost('senha', 		self::$cfg['password'])
				            ->addPost('uniqueid', 	self::$cfg['uniqid'])
				            ->addPost('so', 		self::$_mobile['so'])
				            ->addPost('vapp', 		self::$_mobile['vapp'])
				            ->addPost('vso', 		self::$_mobile['vso'])
				            ->addPost('db', 		self::$_mobile['db'])
				            ->addPost('dm', 		self::$_mobile['dm'])
				            ->getResponse();


		if($_request['status'] != 'ok') throw new Exception(Utils::getError($_request['body']['Mensagens'][0]));

		return [
			'status' => 'ok',
			'token' => $_preLogin['body']['access_token'],
			'hash' => $_request['body']['Retorno']
		];
		
	}



	public function getUser(){


		$_url = $this->_api . '/plataformarevendedor/v2/acesso'; 

		$_request = $this->request($_url)
							->addHeader('Content-Type', 'application/x-www-form-urlencoded')
				            ->addHeader('Authorization', self::$cfg['token'])
				            ->addHeader('hash_usuario', self::$cfg['hash'])
				            ->addParam('hash_usuario', self::$cfg['hash'])
				            ->getResponse();

		if($_request['status'] != 'ok') throw new Exception(Utils::getError($_request['body']['Mensagens'][0]));

		return [
			'status' => 'ok',
			'user' => $_request['body']['Retorno']
		];
		
	}


	public function setUserHash($dealer_id){


		$_url = $this->_api . '/plataformarevendedor/v2/revendedor/hash_grupo'; 

		$_request = $this->request($_url)
				            ->addHeader('Authorization', self::$cfg['token'])
				            ->addHeader('hash_usuario', self::$cfg['hash'])
				            ->addParam('hash_usuario', self::$cfg['hash'])
				            ->addParam('id_loja', $dealer_id)
				            ->getResponse();

		if($_request['status'] != 'ok') throw new Exception(Utils::getError($_request['body']['Mensagens'][0]));

		self::$cfg['hash'] = $_request['body']['Retorno'];
		
	}






	/**
	* 
	* GET the destinations in which ads can be published
	* 
	* @var Array 
	*
	* @return array 	
	*
	**/
	public function getDealersList($codeGroup)
	{
		$_url = $this->_api . '/plataformarevendedor/v2/grupos/listar';

		$_request = $this->request($_url)
							->addHeader('Authorization', self::$cfg['token'])
				            ->addHeader('hash_usuario', self::$cfg['hash'])
				            ->addParam('hash_usuario', self::$cfg['hash'])
				            ->addParam('cod_grupo', $codeGroup)
				            ->addParam('cod_grupo_usuario', 0)
							->getResponse();

		if($_request['status'] != 'ok') throw new Exception(Utils::getError($_request['body']['Mensagens'][0]));


		return [
			'status' => 'ok',
			'stores' => $_request['body']['Retorno'][0]['Lojas']
		];
	}


	/**
	* 
	* GET the destinations in which ads can be published
	* 
	* @var Array 
	*
	* @return array 	
	*
	**/
	public function getDealerToken($dealer_id)
	{
		$_url = $this->_api . '/plataformarevendedor/v2/revendedor/hash_grupo';

		$_request = $this->request($_url)
							->addHeader('Authorization', self::$cfg['token'])
				            ->addHeader('hash_usuario', self::$cfg['hash'])
				            ->addParam('hash_usuario', self::$cfg['hash'])
				            ->addParam('id_loja', $dealer_id)
							->getResponse();


		if($_request['status'] != 'ok') throw new Exception(Utils::getError($_request['body']['Mensagens'][0]));


		return [
			'status' => 'ok',
			'token' => $_request['body']['Retorno']
		];
	}

	/**
	* 
	* GET the destinations in which ads can be published
	* 
	* @var Array 
	*
	* @return array 	
	*
	**/
	public function getLeadsByDay($days = 7, $dealer_token)
	{
		$_url = $this->_api . '/plataformarevendedor/v2/leads/proposta';

		$_request = $this->request($_url)
							->addHeader('Authorization', self::$cfg['token'])
				            ->addHeader('hash_usuario', self::$cfg['hash'])
				            ->addParam('hash_usuario', $dealer_token)
				            ->addParam('limite', 20)
				            ->addParam('id_status', 0)
				            ->addParam('periodo', $days)
				            ->addParam('id_tipolead', 0)
				            ->addParam('id_midia', -1)
				            ->addParam('ordem', 'data')
				            ->addParam('alerta_tempo', 0)
				            ->addParam('texto', '')
							->getResponse();

		if($_request['status'] != 'ok') throw new Exception(Utils::getError($_request['body']['Mensagens'][0]));


		return [
			'status' => 'ok',
			'leads' => $_request['body']['Retorno']
		];
	}



	/**
	* 
	* GET the full lead data
	* 
	* @var Array 
	*
	* @return array 	
	*
	**/
	public function getLead($lead_id, $dealer_token)
	{
		$_url = $this->_api . '/plataformarevendedor/v2/leads/proposta-detalhes';

		$_request = $this->request($_url)
							->addHeader('Authorization', self::$cfg['token'])
				            ->addHeader('hash_usuario', self::$cfg['hash'])
				            ->addParam('hash_usuario', $dealer_token)
				            ->addParam('id_mensagem', $lead_id)
				            ->addParam('id_tipo_lead', 1)
							->getResponse();


		if($_request['status'] != 'ok') throw new Exception(Utils::getError($_request['body']['Mensagens'][0]));


		return [
			'status' => 'ok',
			'lead' => $_request['body']['Retorno']['Mensagem']
		];
	}



	/**
	* 
	* POST accept a lead
	* 
	* @var Array 
	*
	* @return array 	
	*
	**/
	public function acceptLead($lead_id, $dealer_token)
	{
		$_url = $this->_api . '/plataformarevendedor/v2/leads/aceita-lead';

		$_request = $this->request($_url)
							->addHeader('Content-Type', 'application/json')
							->addHeader('Authorization', self::$cfg['token'])
				            ->addHeader('hash_usuario', self::$cfg['hash'])
				            ->addParam('hash_usuario', $dealer_token)
				            ->addPost('cod_mensagem', $lead_id)
				            ->addPost('aceito', true)
				            ->addPost('mensagem', '')
				            ->addPost('hash_usuario', $dealer_token)
				            ->addPost('tipo_lead', 1)
				            ->addPost('fraseRecusa', 0)
				            ->addPost('Valor', 0)
				            ->addPost('Parcela', 0)
				            ->addPost('Entrada', 0)
							->getResponse();


		if($_request['status'] != 'ok') throw new Exception(Utils::getError($_request['body']['Mensagens'][0]));


		return [
			'status' => 'ok',
			'lead' => $_request['body']['Retorno']
		];
	}



	/**
	* 
	* GET Model list
	*
	* @var Array
	*        id_maker
	*
	* @return array 	
	*
	**/
	public function getModels($make_id)
	{
		$_url = $this->_api . '/plataformarevendedor/v2/catalogo/modelo';

		return $this->request($_url)
					->addHeader('Content-Type', 'application/json')
					->addHeader('Authorization', self::$cfg['token'])
		            ->addHeader('hash_usuario', self::$cfg['hash'])
		            ->addParam('hash_usuario', self::$cfg['hash'])
		            ->addParam('id_tipo_veiculo', 1)
		            ->addParam('id_marca', $make_id)
    				->getResponse();
	}


	/**
	* 
	* GET year model list
	*
	* @var Array
	*        id_model
	*
	* @return array 	
	*
	**/
	public function getYearModel($model_id)
	{
		$_url = $this->_api . '/plataformarevendedor/v2/catalogo/modelo-anos';

		return $this->request($_url)
					->addHeader('Content-Type', 'application/json')
					->addHeader('Authorization', self::$cfg['token'])
		            ->addHeader('hash_usuario', self::$cfg['hash'])
		            ->addParam('hash_usuario', self::$cfg['hash'])
		            ->addParam('id_tipo_veiculo', 1)
		            ->addParam('id_modelo', $model_id)
    				->getResponse();
	}




	/**
	* 
	* GET version list
	*
	* @var Array
	*        id_model
	*		 year_model
	*
	* @return array 	
	*
	**/
	public function getVersions($model_id, $model_year)
	{
		$_url = $this->_api . '/plataformarevendedor/v2/catalogo/versao';

		return $this->request($_url)
					->addHeader('Content-Type', 'application/json')
					->addHeader('Authorization', self::$cfg['token'])
		            ->addHeader('hash_usuario', self::$cfg['hash'])
		            ->addParam('hash_usuario', self::$cfg['hash'])
		            ->addParam('id_tipo_veiculo', 1)
		            ->addParam('id_modelo', $model_id)
		            ->addParam('ano_modelo', $model_year)
    				->getResponse();
	}


	public function getAdsType(){

		$_url = $this->_api . '/plataformarevendedor/v2/integradores/listar-modalidades-relacionadas';

		return $this->request($_url)
					->addHeader('Authorization', self::$cfg['token'])
					->addHeader('hash_usuario', self::$cfg['hash'])
					->addParam('hash_usuario', self::$cfg['hash'])
		            ->getResponse();
	}



	/**
	* 
	* Post new ad
	*
	*
	* @var Array
	*        price
	*        make_id
	*        make_name
	*        model_id
	*        model_name
	*        version_id
	*        version_name
	*        color_id
	*        color_name
	*        km
	*        type
	*        plate
	*        year_model
	*        year_building
	*        ads_type > getAdsType()
	*        transmission_id
	*        transmission_name
	*        fuel_id
	*        fuel_name
	*        doors_name
	*        description
	*        equipments > "8;13;5"
	*
	* @return array 	
	*
	**/

	public function postDeal($params)
	{

		$_url = $this->_api . '/plataformarevendedor/v2/estoque';


		return $this->request($_url)
			->addHeader('hash_usuario', self::$cfg['hash'])
			->addHeader('Content-Type', 'application/json')
			->addHeader('Connection', 'Keep-Alive')
			->addHeader('Authorization', self::$cfg['token'])
			->addPost('PrecoReal', '')
			->addPost('PrecoSite', '')
			->addPost('PrecoClassificados', floatval($params['price']))
			->addPost('TipoVeiculo', "1")
			->addPost('Canais', new \stdClass())
			->addPost('IDMarca', (int)$params['make_id'])
			->addPost('Marca', $params['make_name'])
			->addPost('IDModelo', (int)$params['model_id'])
			->addPost('Modelo', $params['model_name'])
			->addPost('IDVersao', (int)$params['version_id'])
			->addPost('Versao', $params['version_name'])
			->addPost('IDCor', (int)$params['color_id'])
			->addPost('Cor', $params['color_name'])
			->addPost('Quilometragem', (string)$params['km'])
			->addPost('Veiculo0KM', (int)$params['type'])
			->addPost('Placa', $params['plate'])
			->addPost('AnoModelo', (int)$params['year_model'])
			->addPost('AnoFabricacao', (int)$params['year_building'])
			->addPost('Ativo', true)
			->addPost('TipoAnuncio', (string)$params['ads_type'])
			->addPost('IDCambio', (int)$params['transmission_id'])
			->addPost('Cambio', $params['transmission_name'])
			->addPost('IDCombustivel', (int)$params['fuel_id'])
			->addPost('Combustivel', $params['fuel_name'])
			->addPost('QuantidadePorta', (int)$params['doors_name'])
			->addPost('Observacoes', $params['description'])
			->addPost('IDOpcionais', $params['equipments_id'])
			->addPost('Opcionais', $params['equipments_name'])
			->addPost('hash_usuario', self::$cfg['hash'])
            ->getResponse();
	}


	/**
	* 
	* Post new picture in ad
	*
	*
	* @var Array
	*        base64
	*		 order
	*		 deal_id
	*
	* @return array 	
	*
	**/
	public function uploadPicture($params)
	{
		$endpoint = '/plataformarevendedor/v2/estoque/fotos-multiplas';


		$jsonData[] = [
			'Foto' => $params['base64'],
			'IdFoto' => 0,
			'Ordem' => (int)$params['order'],
			'Origem' => "camera",
			'CodVeiculo' => (int)$params['deal_id']
		];

		return $this->request($this->_upload . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash'])
			->addHeader('Authorization', self::$cfg['token'])
			->addHeader('Accept-Encoding', 'gzip')
			->addHeader('Connection', 'Keep-Alive')
			->addHeader('Content-Type', 'application/json')
			->addParam('atualizaStatus', 1)
			->addParam('hash_usuario', self::$cfg['hash'])
			->addPostJSON(json_encode($jsonData))
			->getResponse();

	}


	/**
	* 
	* Set visible ad
	*
	* @var Array
	*        deal_id
	*		 price
	*
	* @return array 	
	*
	**/
	public function putPublish($params){

		$_url = $this->_api . '/plataformarevendedor/v2/estoque/definir-modalidade';


		$jsonData[] = [
			'IdAnuncio' => $params['deal_id'],
			'IdIntegradorModalidade' => $params['ads_type'],
			'IdIntegrador' => $params['ads_midia'],
			'IntegradorPreco' => $params['price'],
			'hash_usuario' => self::$cfg['hash']
		];
		
		return $this->request($_url)
			->addHeader('hash_usuario', self::$cfg['hash'])
			->addHeader('Authorization', self::$cfg['token'])
			->addHeader('Content-Type', 'application/json; charset=utf-8')
			->addPutJSON(json_encode($jsonData))
			->getResponse();
	}



	/**
	* 
	* Get deal status
	*
	* @var Array
	*        deal_id
	*
	* @return array 	
	*
	**/
	public function getDealStatus($deal_id){

		$_url = $this->_api . '/plataformarevendedor/v2/estoque/listar-status-anuncio';
		
		return $this->request($_url)
			->addHeader('hash_usuario', self::$cfg['hash'])
			->addHeader('Authorization', self::$cfg['token'])
			->addHeader('Content-Type', 'application/json;')
			->addParam('id_anuncio', $deal_id)
			->addParam('hash_usuario', self::$cfg['hash'])
			->getResponse();
	}



	/**
	* 
	* Delete deal
	*
	* @var Array
	*        deal_id
	*
	* @return array 	
	*
	**/
	public function deleteDeal($deal_id){

		$_url = $this->_api . '/plataformarevendedor/v2/estoque/exclusao';
		
		return $this->request($_url)
			->addHeader('hash_usuario', self::$cfg['hash'])
			->addHeader('Authorization', self::$cfg['token'])
			->addHeader('Content-Type', 'application/json;')
			->addParam('tipo_veiculo', 1)
			->addParam('id_anuncio', $deal_id)
			->addParam('hash_usuario', self::$cfg['hash'])
			->addDelete('tipo_veiculo', strval(1))
			->addDelete('id_anuncio', strval($deal_id))
			->addDelete('hash_usuario', self::$cfg['hash'])
			->getResponse();
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
    public function request($url)
    {
        return new Request($this, $url);
	}
	

 
	

	

	/**
	* 
	* Get List of bid metrics
	*
	* @return array 	
	*
	**/
	public function getMetricsBids()
	{
		$endpoint = '/plataformarevendedor/v2/leads/proposta-metrica?hash_usuario='.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	/**
	* 
	* Get bid list
	*
	* @return array 	
	*
	**/
	public function getBids(){
		$endpoint = '/plataformarevendedor/v2/leads/proposta?hash_usuario='.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();		
	}

	/**
	* 
	* GET Bid Detail 
	*
	* @var Array
	*        id_bid
	*        type_lead
	*
	* @return array 	
	*
	**/
	public function getBidDetail($params)
	{
		$endpoint = '/plataformarevendedor/v2/leads/proposta-detalhes?id_mensagem='.$params['id_bid'].'&id_tipo_lead='.$params['type_lead'].'&hash_usuario='.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	/**
	* 
	* GET dashboard informations
	*
	* @return array 	
	*
	**/
	public function getDashboard()
	{
		$endpoint = '/plataformarevendedor/v2/dashboard/home?hash_usuario='.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	/**
	* 
	* GET Filter list of leads
	* 
	* @return array 	
	*
	**/
	public function getLeadsFilter()
	{
		return $this->request($this->_api . '/plataformarevendedor/v2/filtro/leads?hash_usuario='. self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	/**
	* 
	* GET maker list
	*
	* @return array 	
	*
	**/
	public function getMaker()
	{
		$endpoint = '/plataformarevendedor/v2/catalogo/marca?id_tipo_veiculo=1&'.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	

	

	

	/**
	* 
	* GET Detail car
	*
	* @var Array
	*        id_version
	*		 year_model
	*
	* @return array 	
	*
	**/
	public function getDetailCar($params)
	{
		$endpoint = '/plataformarevendedor/v2/catalogo/especificacao-tecnica?id_versao='.$params['id_version'].'&'.'&ano='.$params['year_model'].'&'.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	/**
	* 
	* GET car price avaliation 
	*
	* @var Array
	*        year_model
	*		 id_maker
	*		 id_model
	*		 id_version
	*		 uf
	*		 armored
	*
	* @return array 	
	*
	**/
	public function getPriceAvaliation($params)
	{
		$endpoint = '/plataformarevendedor/v2/avaliacao/carro?ano='.$params['year_model'].'&marca='.$params['id_maker'].'&modelo='.$params['id_model'].'&versao='.$params['id_version'].'&estado='.$params['uf'].'&blindado='.$params['armored'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	/**
	* 
	* GET List options
	*
	*
	* @return array 	
	*
	**/
	public function getOptions()
	{
		$endpoint = '/plataformarevendedor/v2/catalogo/opcionais?hash_usuario='.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	/**
	* 
	* GET List propertys
	*
	*
	* @return array 	
	*
	**/
	public function getProperty()
	{
		return $property = [
			'only_owner'              => 'Unico Dono',
			'alienated'               => 'Alienado',
			'IPVA'                    => 'IPVA Pago',
			'licensed'                => 'Licenciado',
			'schedule_reviewed'       => 'Revisado no Agenda do Carro',
			'concessionaire_reviewed' => 'Revisado concessionária',
			'maker_warranty'          => 'Garantia de Fábrica',
			'pne'                     => 'Adptado para pessoas com necessidades especiais'
		];
	}

	/**
	* 
	* GET List colors
	*
	*
	* @return array 	
	*
	**/
	public function getColors()
	{
		$endpoint = '/plataformarevendedor/v2/catalogo/cores?hash_usuario='.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	/**
	* 
	* GET ad list
	*
	* @return array 	
	*
	**/
	public function getAds()
	{
		$endpoint = '/plataformarevendedor/v2/estoque/busca?hash_usuario='.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	/**
	* 
	* GET pictures list by ad
	*
	* @var Array
	*        id_ad
	*
	* @return array 	
	*
	**/
	public function getPictures($params){
		$endpoint = '/plataformarevendedor/v2/estoque/fotos?codigo_anuncio='.$params['id_ad'].'&hash_usuario='.self::$cfg['hash_usuario'];
		
		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
            ->getResponse();
	}

	

	

	




	/**
	* 
	* Update a ad
	*
	*
	* @var Array
	*	 	 id_ad
	*        pne
	* 		 IPVA
	*		 licensed
	*		 id_options
	*		 year_maker
	*		 concessionaire_reviewed
	*		 id_color
	*		 only_owner
	*		 price
	*		 plate
	*		 alienated
	*		 name_maker
	*		 id_maker
	*		 name_color
	*		 maker_warranty
	*		 name_options
	*		 name_model
	*		 id_exchange
	*		 doors
	*		 id_version
	*		 0_km
	*		 fuel
	*		 schedule_reviewed
	*		 id_model
	*		 exchange
	*		 armored
	*		 km
	*		 year_model
	*		 active
	*		 version
	*		 text
	*
	* @return array 	
	*
	**/
	public function putAd($params)
	{
		$endpoint = '/plataformarevendedor/v2/estoque?hash_usuario=' . self::$cfg['hash_usuario'];

		$paginacao = [
			'TotalItens'   => 45,
			'TotalPaginas' => 3,
			'PaginaAtual'  => 1
		];

		$integradores = [
			'TipoModalidade'   			=> 1,
			'NomeModalidade'   			=> "Anúncio",
			'StatusModalidade' 			=> 0,
			'IdIntegrador'     			=> 4,
			'Data'             			=> "0001-01-01T00:00:00-02:00",
			'AnuncioCanal'     			=> "",
			'IdModalidade'              => "2943",
			'ModalidadeQuantidadeTotal' => 99999,
			'Id'                        => 118
		];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('content-Type', 'application/json')
			->addHeader('Accept', '*/*')
			->addHeader('Accept-Language', 'pt-BR;q=1.0')
			->addHeader('Accept-Encoding', 'gzip; q=1.0, compress; q=0.5')
			->addHeader('Authorization', self::$cfg['access_token'])
			->addPut('CodModalidadeWebMotors', 0)
			->addPut('Caracteristica_AdaptadoDeficiente', $params['pne'])
			->addPut('PrecoUnico', 0)
			->addPut('TipoAnuncio', "0")
			->addPut('Paginacao', $paginacao)
			->addPut('Integradores', $integradores)
			->addPut('Caracteristica_Licenciado', true)
			->addPut('hash_usuario', self::$cfg['hash_usuario'])
			->addPut('VeiculoAdaptadoDeficiente', $params['pne'])
			->addPut('NomeFantasia', $params['nome_fantasia'])
			->addPut('RevisadoConcessionaria',true)
			->addPut('ID', 0)
			->addPut('PrecoClassificados', $params['price'])
			->addPut('IDEstoque', $params['deal_id'])
			->addPut('VeiculoAlienado',false)
			->addPut('Caracteristica_UnicoDono', true)
			->addPut('IDCombustivel',0)
			->addPut('Marca', $params['name_maker'])
			->addPut('Caracteristica_VeiculoAlienado', false)
			->addPut('IDInformacoesComplentares', 'S;N;N;S;S;N;N;S')
			->addPut('Licenciado', false)
			->addPut('PrecoReal', $params['price'])
			->addPut('Cilindrada',0)
			->addPut('PrecoMedio',0)
			->addPut('IDCambio', $params['id_exchange'])
			->addPut('VersaoTabela', '')
			->addPut('TipoVeiculo', 1)
			->addPut('Modelo', $params['name_modelo'])
			->addPut('Veiculo0KM', $params['0_km'])
			->addPut('Combustivel', $params['fuel'])
			->addPut('RevisadoNoProgramaAgendadoCarro',true)
			->addPut('IDModelo', $params['id_model'])
			->addPut('Cambio', $params['name_exchange'])
			->addPut('Renavam',0)
			->addPut('IDUsuario',0)
			->addPut('Status_Anuncio_UsadosBR',0)
			->addPut('Engine', "")
			->addPut('Ativo', false)
			->addPut('PrecoSite', ' ')
			->addPut('Versao', $params['name_version'])
			->addPut('Observacoes', $params['text'])
			->addPut('TotalAnuncios', 0)
			->addPut('Email', $params['email'])
			->addPut('IPVAPago', true)
			->addPut('Total_Leads', 0)
			->addPut('IDOpcionais', $params['id_options'])
			->addPut('AnoFabricacao', $params['year_maker'])
			->addPut('IDCor', $params['id_color'])
			->addPut('Caracteristica_RevisadoConcessionaria', true)
			->addPut('Placa', $params['plate'])
			->addPut('Cor', $params['name_color'])
			->addPut('Caracteristica_GarantiaFabrica', true)
			->addPut('CodAnuncioCliente', 0)
			->addPut('IDOrigemVeiculo',0)
			->addPut('QtdFotos',$params['qtd_photos'])
			->addPut('Queue',0)
			->addPut('QuantidadePortas', $params['doors'])
			->addPut('Opcionais', $params['name_options'])
			->addPut('UnicoDono', true)
			->addPut('IDLogImportacaoCliente',0)
			->addPut('IDVersao', $params['id_version'])
			->addPut('Caracteristica_RevisadoAgenda', true)
			->addPut('Foto', $params['main_photo'])
			->addPut('Telefone', $params['phone'])
			->addPut('Blindado', 'N')
			->addPut('Quilometragem', $params['mileage'])
			->addPut('Canais', new \stdClass())
			->addPut('AnoModelo', $params['year_model'])
			->addPut('CodClienteWebMotors', $params['id_delear'])
			->addPut('DataInclusao', $params['data_inclusao'])
			->addPut('GarantiaFabrica', true)
			->addPut('PossivelInativo',0)
            ->getResponse();
	}

	/**
	* 
	* delete post
	*
	*
	* @var Array
	*        id_ad
	*
	* @return array 	
	*
	**/
	public function deletePost($params)
	{
		$endpoint = '/plataformarevendedor/v2/estoque/exclusao?id_anuncio='.$params['id_ad'].'&hash_usuario='.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])
			->addHeader('Accept-Encoding', 'gzip')
			->addDelete('id_anuncio', $params['id_ad'])
            ->getResponse();
	}
 

	/**
	* 
	* delete a picture
	*
	*
	* @var Array
	*        id_ad
	*
	* @return array 	
	*
	**/
	public function deletePicture($params)
	{
		$endpoint = '/plataformarevendedor/v2/estoque/fotos-multiplas?hash_usuario='.self::$cfg['hash_usuario'];

		return $this->request($this->_api . $endpoint)
			->addHeader('hash_usuario', self::$cfg['hash_usuario'])
			->addHeader('Authorization', self::$cfg['access_token'])		
			->addHeader('Content-Type', 'application/json')
			->addHeader('Accept-Encoding', 'gzip')
			->addDelete(0, $params)
			->getResponse();
	}

}