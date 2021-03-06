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
if (!defined('OSE_FRAMEWORK') && !defined('OSEFWDIR') && !defined('_JEXEC'))
{
	die('Direct Access Not Allowed');
}
class ScanreportModel extends BaseModel
{
	public function __construct()
	{
		$this->loadLibrary ();
		$this->loadDatabase ();
	}
	protected function loadLibrary()
	{
		oseFirewall::callLibClass('vsscanstat', 'vsscanstat');
	}
	public function loadLocalScript()
	{
		$baseUrl = Yii::app()->baseUrl;
		$cs = Yii::app()->getClientScript();
		$this->loadJSLauguage ($cs, $baseUrl);
		$cs->registerScriptFile($baseUrl.'/public/js/scanreport.js', CClientScript::POS_END);
	}
	public function getCHeader()
	{
		return oLang::_get('SCANREPORT_TITLE');
	}
	public function getCDescription()
	{
		return oLang::_get('SCANREPORT_DESC');
	}
	public function getTypeList()
	{
		$return = array();
		$oseVsscanStat = new oseVsscanStat();
		$return['id'] = 1;
		if (oseFirewall::isDBReady())
		{
			$return['results'] = $oseVsscanStat->getTypeList();
			if (empty($return['results']))
			{
				$return['results']['id'] = 0;
				$return['results']['type'] = 'N/A';
			}
			$return['total'] = count($return['results']);
		}
		else
		{
			$return['results']['id'] = 0;
			$return['results']['type'] = 'N/A';
			$return['total'] = 0;
		}
		return $return;
	}
	public function getMalwareMap()
	{
		$return = array();
		$oseVsscanStat = new oseVsscanStat();
		$return['id'] = 1;
		if (oseFirewall::isDBReady())
		{
			$return['results'] = $oseVsscanStat->getMalwareMap();
			if (empty($return['results']))
			{
				$return['results']['id'] = 0;
				$return['results']['filename'] = oLang::_get('SCANREPORT_CLEAN');	
			}
			$return['total'] = $oseVsscanStat->getMalwareTotal();
		}
		else
		{
			$return['results']['id'] = 0;
			$return['results']['filename'] = oLang::_get('SCANREPORT_CLEAN');
			$return['total'] = 0;
		}
		return $return;
	}
	public function viewfile($id)
	{
		$return = array();
		$oseVsscanStat = new oseVsscanStat();
		$content = $oseVsscanStat->getFileContent($id);
		return $content;
	}
	public function getStatistics()
	{
		$oseFirewallStat = new oseFirewallStat();
		return $oseFirewallStat->getACLIPStatistic();
	}
}
