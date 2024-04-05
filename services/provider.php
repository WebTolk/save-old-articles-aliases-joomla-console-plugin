<?php
/**
 * @package       Save old articles aliases
 * @version       1.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Console\Saveoldarticlesaliases\Extension\Saveoldarticlesaliases;

return new class () implements ServiceProviderInterface {

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @since   1.1.0
	 */
	public function register(Container $container)
	{
		$container->registerServiceProvider(new MVCFactory('Joomla\\Plugin\\Console\\Saveoldarticlesaliases'));

		$container->set(PluginInterface::class,
			function (Container $container) {
				$config     = (array) PluginHelper::getPlugin('console', 'saveoldarticlesaliases');

				$subject    = $container->get(DispatcherInterface::class);
				$mvcFactory = $container->get(MVCFactoryInterface::class);

				$app = Factory::getApplication();

				$plugin = new Saveoldarticlesaliases($subject, $config);
				$plugin->setApplication($app);
				$plugin->setMVCFactory($mvcFactory);

				return $plugin;
			}
		);
	}
};
