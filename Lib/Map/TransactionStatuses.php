<?php
namespace PagSeguro\Lib\Map;

/**
 * Classe que disponibiliza as situações das transações para
 * fácil recuperação das mensagens.
 *
 * Métodos auxiliares são disponíbilizados afim de facilitar
 * a lógica da aplicação, abstraindo a definição de quais situações
 * são consideras finais e quais não são.
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
class TransactionStatuses {

	static public $map = array(
		1 => 'Aguardando pagamento',
		2 => 'Em análise',
		3 => 'Paga',
		4 => 'Disponível',
		5 => 'Em disputa',
		6 => 'Devolvida',
		7 => 'Cancelada');

	static public function getMessage($code)
	{
		if (isset(self::$map[$code])) {
			return self::$map[$code];
		}

		return null;
	}

	static public function isOk($code)
	{
		$oks = array(3, 4);
		return in_array($code, $oks);
	}

	static public function isPending($code)
	{
		$pendings = array(1, 2, 5);
		return in_array($code, $pendings);
	}

	static public function isAborted($code)
	{
		$aborteds = array(6, 7);
		return in_array($code, $aborteds);
	}
}
