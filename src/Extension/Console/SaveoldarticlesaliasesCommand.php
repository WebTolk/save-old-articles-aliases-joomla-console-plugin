<?php
/**
 * @package       Save old articles aliases
 * @version       1.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2024 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */
 
namespace Joomla\Plugin\Console\Saveoldarticlesaliases\Extension\Console;

use Joomla\CMS\Factory;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Registry\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Joomla\CMS\Component\ComponentHelper;
use Symfony\Component\Console\Helper\ProgressBar;

defined('_JEXEC') or die;

class SaveoldarticlesaliasesCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected static $defaultName = 'oldarticlesaliases:save';

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function configure(): void
	{
//		$this->addOption('domain', 'd', InputOption::VALUE_REQUIRED, 'Domain for check without', '');
		$this->addOption('cats', 'c', InputOption::VALUE_OPTIONAL, 'Only this categories ids will be handled. Separate its via commma without spaces.', '');
		$this->addOption('nocats', 'nc', InputOption::VALUE_OPTIONAL, 'All categories exclude specified ids. Separate its via commma without spaces.', '');
		$this->addArgument('test', InputArgument::OPTIONAL, 'Check how article alias will changed', null);
		$this->setDescription("Helps you to move to new Joomla router. Saves old aliases of articles by adding their IDs to them at the beginning");
		/*$this->setHelp(
			<<<EOF
			Scan Joomla sites 
			###########
			
			<comment>php joomla.php oldarticlesaliases:save --cats=1,14,543</comment>
			
			<comment>php joomla.php oldarticlesaliases:save --nocats=1,14,543</comment>
			
			###########
			EOF
		);*/
	}


	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{

		$symfonyStyle = new SymfonyStyle($input, $output);
		$symfonyStyle->caution(
		[
			'Be careful!',
			'This process will change aliases in database directly. Make a backup of the database before you start working!',
			'##################',
			'Run this command ONLY ONCE, otherwise the aliases will have duplicate article IDs.',
			'##################',
			'Make a backup again before starting work.',
		]);
		
		$confirm = $symfonyStyle->choice('Have you made a database backup and want to continue?', ['Yes','No'], 'No');

		if('Yes' != $confirm)
		{
			$symfonyStyle->info('You canceled the aliases update');
			return Command::INVALID;
		}
		/**@var $cats Categories ids comma separated list */
		$cats = $input->getOption('cats','');
		if(!empty($cats))
		{
			$cats = explode(',',$cats);
		} else {
			$cats = [];
		}
		$nocats = $input->getOption('nocats','');

		$scriptStart = microtime(true);

		if ($cats && $nocats)
		{
			$symfonyStyle->error('Please, specify only one option or  nothing');

			return Command::INVALID;
		}

	
		 /** @var \Joomla\Component\Content\Administrator\Extension\ContentComponent $contentComponent */
        $contentComponent = Factory::getApplication()->bootComponent('com_content');

        /** @var \Joomla\Component\Content\Site\Model\ArticlesModel $model */
        $model = $contentComponent->getMVCFactory()
            ->createModel('Articles', 'Administrator', ['ignore_request' => true]);
		
		$params = Factory::getContainer()->get('config');
		$componentParams = ComponentHelper::getParams('com_content');
		$params->merge($componentParams);
		
		// Set application parameters in model
        $model->setState('params', $params);
        $model->setState('list.start', 0);

        // This module does not use tags data
        $model->setState('load_tags', false);

		/**
		 *  Test mode
		 */
		if ($input->getArgument('test')) {
			 $articles = $model->getItems();
			 $article = $articles[0];
			 $symfonyStyle->info(
				 [
					'#####################################################',
					'# Test mode. You can see how alias will be changed. #',
					'#####################################################'
				 ]
			 );
			 
			 
			 
			 $headers = ['Article', 'Old alias', 'New alias'];
			 $rows = [
				  [$article->title,$article->alias, $article->id.'-'.$article->alias],
			 ];
			  $symfonyStyle->table($headers, $rows);
			 
			 return Command::SUCCESS;
			 
		}
		
		$articles = [];
		
        // Category filter
		if(!empty($cats))
		{
			foreach($cats as $category_id)
			{
				$model->setState('filter.category_id', $category_id);
				$articles = array_merge($articles, $model->getItems());
			}
		} else {
			 $articles = $model->getItems();
		}

		$articles_ids = [];
		foreach($articles as $article)        
		{
			$articles_ids[] = $article->id;
		}
		
		unset ($articles);
		
		$total_articles = count($articles_ids);
		$symfonyStyle->info('Total articles: '. $total_articles);
		
		$aliases_updated = 0;

		if($total_articles)
		{
	
			ProgressBar::setFormatDefinition('custom', '<info>%current%/%max%</info> -- %message%');
			$this->progressBar = new ProgressBar($output, $total_articles);
			$this->progressBar->setFormat('custom');
			
			foreach($articles_ids as $article_id)
			{
			
				if(!$this->updateArticleAlias($article_id))
				{
					$symfonyStyle->error('Error with update article with id '.$article_id);
					
					return Command::FAILURE;
				}
				
				$aliases_updated++;
				
				$this->progressBar->advance();
				$this->progressBar->setMessage('Aliases updated: '.$aliases_updated);
				
			}
			
			$this->progressBar->finish();
		} else {
			$symfonyStyle->warning('There is no articles found');
			return Command::INVALID;	
		}
		
		$time = number_format(microtime(true) - $scriptStart, 2, '.', '');
		$symfonyStyle->newLine();
		$symfonyStyle->writeln(
		[
		'============================',
		'FINISHED IN SECONDS ' . $time
		]);
		
		return Command::SUCCESS;

	}
	
	
	private function updateArticleAlias($article_id):bool
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->update('#__content')
		->set(
				[
					$db->quoteName('alias') . ' = concat('.$db->quoteName('id').','.$db->quote('-').','.$db->quoteName('alias').')'
				]
			 )
		->where('id=' . $db->quote($article_id));	
		
        return $db->setQuery($query)->execute();
	}
	
}
