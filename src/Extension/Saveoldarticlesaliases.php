<?php
/**
 * @package       Save old articles aliases
 * @version       1.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\Console\Saveoldarticlesaliases\Extension;

\defined('_JEXEC') or die;

use Joomla\Application\ApplicationEvents;
use Joomla\Application\Event\ApplicationEvent;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;

use Joomla\Plugin\Console\Saveoldarticlesaliases\Extension\Console\SaveoldarticlesaliasesCommand;

use Joomla\Registry\Registry;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class Saveoldarticlesaliases extends CMSPlugin implements SubscriberInterface
{
	use MVCFactoryAwareTrait;
	use DatabaseAwareTrait;

	/**
	 * Choose which events this plugin is subscribed to and will respond to
	 * @return string[]
	 *
	 * @since version
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			\Joomla\Application\ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
		];
	}

	/**
	 * Register custom commands
	 *
	 * @since version
	 */
	public function registerCommands(): void
	{
		//hello super joomler command
		Factory::getContainer()->share(
			'save.old.articles.aliases',
			function (ContainerInterface $container) {
				return new saveoldarticlesaliasesCommand;
			},
			true
		);

		// add hello super joomlers command to joomla.php cli script
		Factory::getContainer()->get(\Joomla\CMS\Console\Loader\WritableLoaderInterface::class)->add('oldarticlesaliases:save', 'save.old.articles.aliases');

	}




}