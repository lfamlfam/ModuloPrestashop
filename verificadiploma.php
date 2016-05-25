<?php

/*
Manual: http://doc.prestashop.com/display/PS15/Creating+a+PrestaShop+module#CreatingaPrestaShopmodule-Embeddingatemplateinthetheme
*/

if (!defined('_PS_VERSION_'))
	exit;

class VerificaDiploma extends Module
{

	public function __construct()
	{
		$this->name = 'verificadiploma';
		$this->tab = 'others';//cat. na lista de módulos
		$this->version = '0.0.1';
		$this->author = 'LFABM';
		$this->need_instance = 0;//precisa instanciar a classe na hora de mostrar a lista de módulos? Sim = 1 Não =0
		$this->ps_versions_compliancy = array(	'min' => '1.6',
							'max' => _PS_VERSION_);
		$this->bootstrap = true;// =true o PrestaShop não envolve os códigos dos templates com tags auxiliares na tela de configuração
		parent::__construct();//Executa o construtor da classe Module
		$this->displayName = $this->l('Verificador de Diploma');
		$this->description = $this->l('Módulo para verificar autenticidade de diplomas');
		$this->confirmUninstall = $this->l('Todos os dados dos diplomas serão apagados!');
		if (!Configuration::get('NOMEPARAMETRO_VERIFICADIPLOMA'))
			$this->warning = $this->l('Nenhum parametro de configuracao gravado');
	}


	public function install()
	{
		if (Shop::isFeatureActive())//Verifica se o multistore está ativo
				Shop::setContext(Shop::CONTEXT_ALL);//Se sim, "seta" o modulo para todas as lojas
			if (	!parent::install() || //verifica se a classe pai está instalada
				!$this->registerHook('leftColumn') ||//verifica se o módulo pode ser anexado ao HOOK leftColumn
				!$this->registerHook('header') ||//verifica se o módulo pode ser anexado ao HOOK header
				!Configuration::updateValue(
						'NOMEPARAMETRO_VERIFICADIPLOMA', 'algum valor')//cria a definição de configuração
			   ){
				return false;
			}
			else
			{
				Db::getInstance()->Execute('CREATE TABLE `'._DB_PREFIX_.'verificadiploma` (cod_diploma char(10) not null primary key, nome_aluno varchar(100))');
			}
		return true;
	}

	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('submit'.$this->name))
		{
			$my_module_name = strval(Tools::getValue('VERIFICADIPLOMA_CONFIG'));
			if (!$my_module_name  || empty($my_module_name) || !Validate::isGenericName($my_module_name))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			else
			{
				Configuration::updateValue('VERIFICADIPLOMA_CONFIG', $my_module_name);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		// Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		// Init Fields form array
		$fields_form[0]['form'] = array(
				'legend' => array(
					'title' => $this->l('Settings'),
					),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Configuration value'),
						'name' => 'VERIFICADIPLOMA_CONFIG',
						'size' => 20,
						'required' => true
					     )
					),
				'submit' => array(
					'title' => $this->l('Save'),
					'class' => 'button'
					)
				);

		$helper = new HelperForm();

		// Module, t    oken and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
				'save' =>
				array(
					'desc' => $this->l('Save'),
					'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
					'&token='.Tools::getAdminTokenLite('AdminModules'),
				     ),
				'back' => array(
					'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
					'desc' => $this->l('Back to list')
					)
				);

		// Load current value
		$helper->fields_value['VERIFICADIPLOMA_CONFIG'] = Configuration::get('VERIFICADIPLOMA_CONFIG');

		return $helper->generateForm($fields_form);
	}

	public function uninstall()
	{
		//Apaga o parâmetro de configuração que foi criado na instalação do módulo
			if (	!parent::uninstall() ||
				!Configuration::deleteByName('NOMEPARAMETRO_VERIFICADIPLOMA')
			   )
			{
				return false;
			}
			else
			{
				Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'verificadiploma`');
			}

		return true;
	}


}

?>
