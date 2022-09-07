<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.remember
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Menus\Administrator\Helper\AssociationsHelper;
use Joomla\Module\Menu\Site\Helper\MenuHelper;

/**
 * Joomla! System Remember Me Plugin
 *
 * @since  1.5
 */

class PlgSystemSeolanghref extends CMSPlugin
{
	/**
	 * @var    string[]
	 * @since  1.0.0
	 */
	protected $unsetUrls = [
		'http://joomla1.joomla.local:8074/fr/test-fr"',
		'http://joomla1.joomla.local:8074/de/test-2'
	];


    /**
     * Change Head Links
     *
     * @return  void
     * @since   1.0.0
	 */

    public function onBeforeCompileHead()
    {
	    $doc = Factory::getDocument();

	    $app  = Factory::getApplication();
	    $active = $app->getMenu()->getActive();

		var_dump($app->isClient('site'));

		if($app->isClient('site'))
		{
		    $associations = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', $active->id);

		    $head_data = $doc->getHeadData();

		    $links     = $head_data['links'];

		    $lang        = Factory::getLanguage();
		    $currentLanguageTag = $lang->get('tag');
		    $currentLanguageTagShort = substr(strtolower($lang->get('tag')), 0, 2);
			$allLanguages  = LanguageHelper::getLanguages('lang_code');

			$baseUrl = Uri::base();

			$currentUrl = Uri::current();

	        foreach ($allLanguages as $langTag => $values)
	        {
				$langTagShort = substr($langTag, 0, 2);

				if($langTag !== $currentLanguageTag)
				{
					$menuItemId = (int) $associations[$langTag]->id;
			        $menu = $app->getMenu();
					$menuItem = $menu->getItem( $menuItemId );

					$link = Route::_($menuItem->link . '&Itemid=' . $menuItem->id . '&lang=' . $langTagShort);

					unset($doc->_links[$baseUrl . substr($link, 1)]);
					$doc->addHeadLink($baseUrl . substr($link, 1), 'alternate', 'rel',['hreflang' => $langTagShort]);
				}
				else{
					$link = Route::_($active->link . '&Itemid=' . $active->id . '&lang=' . $currentLanguageTagShort);
					unset($doc->_links[$baseUrl . substr($link, 1)]);
					$doc->addHeadLink($baseUrl . substr($link, 1), 'canonical', 'rel',['hreflang' => $currentLanguageTagShort]);
					$doc->addHeadLink($baseUrl . substr($link, 1) . '####', 'alternate', 'rel',['hreflang' => $currentLanguageTagShort]);
				}

	        }

		}
    }

	/**
	 * Listener for the `onAfterRender` event
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.0.0
	 */
	public function onAfterRender()
	{

		$app = Factory::getApplication();
		if ($app->isClient('administrator'))
		{
			return;
		}

		$body = $app->getBody();
		$bodyNew = str_replace('####', '', $body);
		$app->setBody($bodyNew);
	}

	/**
	 * Suchen nach einzelnem Arrayelement auf Basis der aktuellen URL
	 *
	 * @param   string  $url      Aktuelle URL
	 * @param   string  $langTag  e.g. de-ch
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function _searchForArray($url, $langTag)
	{
		$array = $this->datas;

		foreach ($array as $key => $val)
		{
			if ($val[$langTag] === $url)
			{
				return $val;
			}
		}

		return array();
	}
}
