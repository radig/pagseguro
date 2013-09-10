<?php
App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');
App::uses('PagSeguroException', 'PagSeguro.Lib');
App::uses('PagSeguroValidation', 'PagSeguro.Lib');
App::uses('TransactionStatuses', 'PagSeguro.Lib/Map');

/**
 * Classe base que fornece estrutura comum aos componentes
 * que interagem com o PagSeguro
 *
 * PHP versions 5.3+
 * Copyright 2010-2013, Felipe Theodoro Gonçalves, (http://ftgoncalves.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Felipe Theodoro Gonçalves
 * @author      Cauan Cabral
 * @link        https://github.com/radig/pagseguro/
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class PagSeguro {

	/**
	 * URI do webservice do PagSeguro
	 *
	 * @var array
	 */
	protected $URI = array(
		'scheme' => 'https',
		'host' => 'ws.pagseguro.uol.com.br',
		'port' => '443',
		'path' => '',
	);

	/**
	 * Configurações para uso da API
	 *
	 * @var array
	 */
	protected $settings = array(
		'email' => null,
		'token' => null
	);

	/**
	 * Conjunto de caracteres utilizados na comunicação
	 * com a API.
	 *
	 * @var string
	 */
	public $charset = 'UTF-8';

	/**
	 * Timeout das requisições com a API.
	 *
	 * @var integer
	 */
	public $timeout = 20;

	/**
	 * Armazena o último erro retornado pela API.
	 *
	 * @var mixed
	 */
	public $lastError = null;

	public function __construct($settings = array()) {

		if (empty($settings) && Configure::read('PagSeguro') !== null) {
			$settings = (array)Configure::read('PagSeguro');
		}

		$this->settings = array_merge($this->settings, $settings);
	}

	/**
	 * Sobrescreve as configurações em tempo de execução
	 *
	 * @param array $config
	 */
	public function config($config = null) {
		if ($config !== null) {
			$this->settings = array_merge($this->settings, $config);
			$this->_settingsValidates();

			return $this;
		}

		return $this->settings;
	}

	/**
	 * Retorna o nome de uma situação a partir de seu
	 * código.
	 *
	 * @param  int $statusCode Código da situação (status)
	 * @return string Nome da situação
	 */
	public static function getStatusName($statusCode) {
		$msg = PagSeguro\Lib\Map\TransactionStatuses::getMessage($statusCode);
		return $msg ?: 'Situação inválida';
	}

	/**
	 * Envia os dados para API do PagSeguro usando método especificado.
	 *
	 * @throws PagSeguroException
	 * @param array $data
	 * @param string $method
	 * @return array
	 */
	protected function _sendData($data, $method = 'POST') {
		$this->_settingsValidates();

		$HttpSocket = new HttpSocket(array(
			'timeout' => $this->timeout
		));

		if ('get' === strtolower($method)) {
			$return = $HttpSocket->get(
				$this->URI,
				$data,
				array('header' => array('Content-Type' => "application/x-www-form-urlencoded; charset={$this->charset}"))
			);
		} else {
			$return = $HttpSocket->post(
				$this->URI,
				$data,
				array('header' => array('Content-Type' => "application/x-www-form-urlencoded; charset={$this->charset}"))
			);
		}

		switch ($return->code) {
			case 200:
				break;
			case 400:
				throw new PagSeguroException('A requisição foi rejeitada pela API do PagSeguro. Verifique as configurações.', 400, $return->body);
			case 401:
				throw new PagSeguroException('O Token ou E-mail foi rejeitado pelo PagSeguro. Verifique as configurações.', 401);
			case 404:
				throw new PagSeguroException('Recurso não encontrado. Verifique os dados enviados.', 404);
			default:
				throw new PagSeguroException('Erro desconhecido com a API do PagSeguro. Verifique suas configurações.');
		}

		try {
			$response = Xml::toArray(Xml::build($return->body));
		}
		catch (XmlException $e) {
			throw new PagSeguroException('A resposta do PagSeguro não é um XML válido.');
		}

		if ($this->_parseResponseErrors($response)) {
			throw new PagSeguroException("Erro com os dados enviados no PagSeguro.", 666, $return->body);
		}

		return $this->_parseResponse($response);
	}

	/**
	 * Parseia e retorna a resposta do PagSeguro.
	 * Deve ser implementado nas classes filhas
	 *
	 * @param array $data
	 * @return array
	 */
	protected function _parseResponse($data) {
		throw new PagSeguroException("Erro de implementação. O método _parseResponse deve ser implementado nas classes filhas de PagSeguro.");
	}

	/**
	 * Verifica se há erros na resposta do PagSeguro e formata as mensagens
	 * no atributo lastError da classe.
	 *
	 * @param array $data
	 * @return bool True caso hajam erros, False caso contrário
	 */
	protected function _parseResponseErrors($data) {
		if (!isset($response['errors'])) {
			return false;
		}

		$lastError = "Erro no PagSeguro: \n";

		foreach ($response['errors'] as $error) {
			$lastError .= "[{$error['error']['code']}] {$error['error']['message']}\n";
		}

		return true;
	}

	/**
	 * Valida os dados de configuração caso falhe dispara uma exceção
	 *
	 * @throws PagSeguroException
	 * @return void
	 */
	protected function _settingsValidates() {
		$fields = array('email', 'token');

		foreach ($fields as $fieldName) {
			if (!isset($this->settings[$fieldName])) {
				throw new PagSeguroException("Erro de configuração - Atributo '{$fieldName}' não definido.");
			}

			if (PagSeguroValidation::$fieldName($this->settings[$fieldName]) === false) {
				$msg = str_replace(':attr:', $fieldName, PagSeguroValidation::$lastError);
				throw new PagSeguroException("Erro de configuração - " . $msg);
			}
		}
	}
}
