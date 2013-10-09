<?php
/**
 * Classe que disponibiliza os meios de pagamento disponíveis
 * no PagSeguro.
 *
 *
 * PHP versions 5.3+
 * Copyright 2010-2013, Felipe Theodoro Gonçalves, (http://ftgoncalves.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Felipe Theodoro Gonçalves
 * @author      Cauan Cabral
 * @link        https://github.com/ftgoncalves/pagseguro/
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class PaymentTypes {

	static protected $map = array(
		1 => 'Cartão de crédito',
		2 => 'Boleto',
		3 => 'Débito online (TEF)',
		4 => 'Saldo PagSeguro',
		5 => 'Oi Paggo');

	static protected $detailed = array(
		101 => 'Cartão de crédito Visa.',
		102 => 'Cartão de crédito MasterCard.',
		103 => 'Cartão de crédito American Express.',
		104 => 'Cartão de crédito Diners.',
		105 => 'Cartão de crédito Hipercard.',
		106 => 'Cartão de crédito Aura.',
		107 => 'Cartão de crédito Elo.',
		108 => 'Cartão de crédito PLENOCard.',
		109 => 'Cartão de crédito PersonalCard.',
		110 => 'Cartão de crédito JCB.',
		111 => 'Cartão de crédito Discover.',
		112 => 'Cartão de crédito BrasilCard.',
		113 => 'Cartão de crédito FORTBRASIL.',
		201 => 'Boleto Bradesco.',
		202 => 'Boleto Santander.',
		301 => 'Débito online Bradesco.',
		302 => 'Débito online Itaú.',
		303 => 'Débito online Unibanco.',
		304 => 'Débito online Banco do Brasil.',
		305 => 'Débito online Banco Real.',
		306 => 'Débito online Banrisul.',
		307 => 'Débito online HSBC.',
		401 => 'Saldo PagSeguro.',
		501 => 'Oi Paggo.');

	static public function getMessage($code)
	{
		if (isset(self::$map[$code])) {
			return self::$map[$code];
		}

		return null;
	}

	static public function getDetailedMessage($code)
	{
		if (isset(self::$detailed[$code])) {
			return self::$detailed[$code];
		}

		return null;
	}

	static public function getGenericOfDetailed($code)
	{
		$type = $code;
		if (strlen($code) > 1) {
			$type = substr($code, 0, 1);
		}

		if (isset(self::$map[$code])) {
			return self::$map[$code];
		}

		return null;
	}
}
