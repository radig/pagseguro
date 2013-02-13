<?php
App::uses('CakeException', 'CORE');

/**
 * Classe de exceção que parseia corretamente os erros da API
 * para gravação de logs.
 *
 * PHP versions 5.3+
 * Copyright 2010-2013, Felipe Theodoro Gonçalves, (http://ftgoncalves.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author	 	 Felipe Theodoro Gonçalves
 * @author       Cauan Cabral
 * @link         https://github.com/ftgoncalves/pagseguro/
 * @license      MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @version      2.2
 */
class PagSeguroException extends CakeException {

	/**
	 * Sobrescreve exceção do Cake para incluir informação
	 * do erro que poderá ser logada.
	 *
	 * @param string  $message Mensagem da Exceção
	 * @param integer $code    Código do erro
	 * @param string  $error   O erro retornado pelo PagSeguro (possivelmente um XML)
	 */
	public function __construct($message, $code = 1, $error = null)
	{
		if (!empty($error)) {
			try {
				$decoded = Xml::toArray(Xml::build($error));
				$error = $this->_parseXmlError($decoded);
			} catch (XmlException $e) {
				// apenas uma string... não faz conversão
			}

			$msg = $message . " (Problema relacionado ao PagSeguro)\n" . $error;
			CakeLog::write('error', $msg);
		}

		parent::__construct($message, $code);
	}

	/**
	 * Parseia um erro XML (convertido para Array) retornado pelo PagSeguro retornando
	 * uma string.
	 *
	 * @param  array $error
	 * @return string
	 */
	protected function _parseXmlError($errors)
	{
		if (!isset($errors['errors'])) {
			return '';
		}

		$str = '';
		foreach ($errors['errors'] as $error) {
			$str .= "[{$error['code']}] {$error['message']}\n";
		}

		return $str;
	}
}
