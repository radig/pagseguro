Plugin de integração PagSeguro e CakePHP
========================================

Está é a documentação para a Versão 2.1 do plugin PagSeguro.

Requisitos:
-----------

+ CakePHP 2.*
+ API PagSeguro 2.0
+ PHP 5.3.*

Primeiros passos
----------------

O plugin disponibiliza 3 libs principais: *PagSeguroCheckout*, *PagSeguroNotifications* e *PagSeguroConsult*.
Cada uma destas possui um componente correspondete para facilitar o uso em controladores, são eles: *Checkout*, *Notifications*
e *Consult*.

Existem duas **configurações** que **devem ser feitas antes de usar qualquer lib ou componente deste plugin**: seu *email* e *token de segurança*
gerado no PagSeguro.

Uma forma de definir globalmente essa configuração é usando a classe Configure do CakePHP no seu arquivo Config/bootstrap.php

Exemplo:
	Configure::write('PagSeguro', array(
		'email' => 'exemplo@exemplo.com.br', // Email da conta do vendedor
		'token' => '3893im3u93i3m9iu39i39iu' // Token gerado pelo PagSeguro
	));

Mas você pode definir e redefinir essas configurações quantas vezes quiser invocando o método
estático *config()* disponível em todas as libs e components do plugin.

Exemplo:
	PagSeguroCheckout::config(array(
		'email' => 'exemplo@exemplo.com.br',
		'token' => '3893im3u93i3m9iu39i39iu'
	));

Ou ainda nas configurações do componente

Exemplo:
	public $components = array('PagSeguro.Checkout', array(
		'email' => 'exemplo@exemplo.com.br',
		'token' => '3893im3u93i3m9iu39i39iu'
	));

As três formas possuem o mesmo efeito: alteram as configurações de comunicação entre o plugin e
o PagSeguro.

PagSeguroCheckout e CheckoutComponent
-----------------

Esta lib viabiliza as solicitações de transação com o PagSeguro. O component
é apenas um wrapper que facilita o uso da lib em um controller.

### Carregando o component:
	
	public $components = array('PagSeguro.Checkout');

### Funcionamento

A operação de *checkout* ou finalização de um pedido consiste das seguintes ações:

1. Informar um código de referência, que relacionará a transação do PagSeguro com a operação em seu sistema (ID da compra, por exemplo) (obrigatório)
2. Adicionar itens ao carrinho (obrigatório)
3. Adicionar as informações do cliente (obrigatório)
4. Informar tipo de entrega (opcional)
5. Informar endereço de entrega (opcional)
6. Finalizar (obrigatório)

Cada uma das etapas está associada à um método da lib PagSeguroCheckout.

Vale reforçar que a mesma Api da lib está disponível como o component Checkout, basta trocar as chamadas
de ``PagSecuguroCheckout::metodo()`` para ``$this->Checkout->metodo()``, onde *metodo()* será substituido
pelo método em questão da Api.

### Api PagSeguroCheckout

#### PagSeguroCheckout::setReference()

Define o código de referência, que será associado pelo PagSeguro com a transação efetuada.
É prático associalo com o ID da operação de venda.

#### PagSeguroCheckout::setCustomer($email, $name, $areaCode = null, $phone = null)

Define dados do cliente, email e nome são obrigatórios.

**Atenção**: O nome deve ser composto minimamente de um sobrenome, com espaço simples (apenas um)
separando os dois.

#### PagSeguroCheckout::setItem(array())

Para adicionar um item ao carrinho, existem duas opções:

	PagSeguroCheckout::setItem(array(
		'id' => '30', // ID de identificação do produto
		'description' => 'Notebook LG', // Descrição do produto geralmente o nome
		'amount' => '1000.00', // Valor do produto no formato americano
		'quantity' => '1', // Quantidade do produto
		'weight' => '1000' // Pesso do produto em gramas
		'shippingCost'
	));

ou, em uma assinatura direta sem array

	PagSeguroCheckout::addItem($id, $name, $amount, $quantity = 1, $weight = 0, $shippingCost = null)

Como sugere a segunda assinatura, é obrigatório (em ambos os casos) informar um id, name/description e valor.

#### PagSeguroCheckout::setShippingAddress($zip, $address, $number, $completion, $neighborhood, $city, $state, $country)

* $zip: código do CEP
* $address: nome da rua/logradouro
* $number: número do local
* $completion: complemento
* $neighborhood: bairro
* $city: cidade
* $state: estado (sigla, duas letras)
* $country: país (sigla, três letras)


##### PagSeguroCheckout::finalize()

Este método finaliza e gera a requisição POST para o servidor do PagSeguro.

O retorno contém a resposta do PagSeguro à solicitação, com erros ou código de redirecionamento.

Na versão disponibilizada pelo componente, a assinatura do método recebe um valor
booleano que habilita ou não o redirecionamento automático do usuário para o PagSeguro.

Caso queira fazer o redirecionamento manualmente, passe o valor *false* ao componente
e acesse o endereço que você deve usar para redirecionar o usuário pela chave 'redirectTo'
do retorno.

Exemplo

	$retorno = $this->Checkout->finalize(false);

	if(isset($retorno['redirectTo'])) {
		// sucesso, redirecionao usuário
		$this->redirect($retorno['redirectTo']);
	}

	// se chegou aqui é por que falhou
	pr($retorno); // printar os erros para entender sua estrutura



Exemplo básico mas completo:

class PagesController extends AppController {

	public $components = array('PagSeguro.Checkout');

	public function index() {

		$this->Checkout->setReference(2012)
						->setCustomer('joao.silva@gmail.com', 'João da Silva', '11', '99999999')
						->addItem(30, 'Notebook LG', '1000.00', '1', '1000')
						->setShippingType('PAC')
						->setShippingAddress('99999999', 'Rua do Joãozinho', 800, '', 'Vila João', 'São Paulo', 'SP', 'BRA');

		$resturn = $this->Checkout->finalize(false);
		if (isset($resturn['redirectTo'])) {
			$this->redirect($retorno['redirectTo']);
		}

		// se chegou aqui é por que falhou
	}
}


PagSeguroNotification e NotificationsComponent
-----------------------------------------------

Lib e componente que facilitam o tratamento das notificações enviadas pelo PagSeguro à
sua aplicação. Essas notificações podem ser de dois tipos: o retorno do seu cliente após
finalizar uma compra *ou* uma alteração de status em alguma transação.

### Api PagSeguroNotification

#### PagSeguroNotification::read(array)

Recebe como parâmetro os dados $_POST enviado pelo PagSeguro em sua
requisição e retorna os detalhes da transação em questão.

Exemplo

	$transacao = $this->Notification->read($this->request->data);

	pr($transacao);

O retorno contém as seguintes chaves:

* *date*: Data de criação da transação
* *code*: Código da transação no PagSeguro
* *value*: Valor da transação
* *status*: Situação corrente
* *reference*: Código de referência da transação na sua aplicação
* *modified*: Data da última alteração
* paymentType: Tipo de pagamento (opcional)
* paymentCode: Código do pagamento (opcional)