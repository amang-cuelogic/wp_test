<?php
/**
 * @version     2.0 +
 * @package       Open Source Excellence Security Suite
 * @subpackage    Centrora Security Firewall
 * @subpackage    Open Source Excellence WordPress Firewall
 * @author        Open Source Excellence {@link http://www.opensource-excellence.com}
 * @author        Created on 01-Jun-2013
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *  @Copyright Copyright (C) 2008 - 2012- ... Open Source Excellence
 */
if (!defined('OSE_FRAMEWORK') && !defined('OSE_ADMINPATH') && !defined('_JEXEC'))
{
 die('Direct Access Not Allowed');
}
/**
 * COutputProcessor class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * COutputProcessor transforms the content into a different format.
 *
 * COutputProcessor captures the output generated by an action or a view fragment
 * and passes it to its {@link onProcessOutput} event handlers for further processing.
 *
 * The event handler may process the output and store it back to the {@link COutputEvent::output}
 * property. By setting the {@link CEvent::handled handled} property of the event parameter
 * to true, the output will not be echoed anymore. Otherwise (by default), the output will be echoed.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class COutputProcessor extends CFilterWidget
{
	/**
	 * Initializes the widget.
	 * This method starts the output buffering.
	 */
	public function init()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	/**
	 * Executes the widget.
	 * This method stops output buffering and processes the captured output.
	 */
	public function run()
	{
		$output=ob_get_clean();
		$this->processOutput($output);
	}

	/**
	 * Processes the captured output.
	 *
	 * The default implementation raises an {@link onProcessOutput} event.
	 * If the event is not handled by any event handler, the output will be echoed.
	 *
	 * @param string $output the captured output to be processed
	 */
	public function processOutput($output)
	{
		if($this->hasEventHandler('onProcessOutput'))
		{
			$event=new COutputEvent($this,$output);
			$this->onProcessOutput($event);
			if(!$event->handled)
				echo $output;
		}
		else
			echo $output;
	}

	/**
	 * Raised when the output has been captured.
	 * @param COutputEvent $event event parameter
	 */
	public function onProcessOutput($event)
	{
		$this->raiseEvent('onProcessOutput',$event);
	}
}
