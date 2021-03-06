<?php

/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE . '/components'.DS.'com_fabrik'.DS.'models'.DS.'visualization.php');

class fabrikModelMedia extends FabrikFEModelVisualization {


	/** js name for meidi **/
	var $calName = null;

	function getMedia()
	{
		$app = JFactory::getApplication();
		$Itemid	= @$app->getMenu('site')->getActive()->id;
		$params = $this->getParams();
		$w = $params->get('media_width');
		$h = $params->get('media_height');
		$return = '';
		if ($params->get('media_which_player', 'jw') == 'xspf')
		{
			$player_type = "Extended";
			$player_url = COM_FABRIK_LIVESITE.$this->srcBase."media/libs/xspf/$player_type/xspf_player.swf";
			$playlist_url = 'index.php?option=com_fabrik&controller=visualization.media&view=visualization&task=getPlaylist&format=raw&Itemid='. $Itemid. '&visualizationid='.$this->getId();
			$playlist_url = urlencode($playlist_url);
			$return = '<object type="application/x-shockwave-flash" width="400" height="170" data="' . $player_url . '?playlist_url=' . $playlist_url . '">';
			$return .= '<param name="movie" value="xspf_player.swf?playlist_url=' . $playlist_url . '" />';
			$return .= '</object>';
		}
		else
		{
			$return = "<div id='jw_player'></div>";
		}
		return $return;
	}

	function getPlaylist()
	{
		$params = $this->getParams();

		$mediaElement	= $params->get('media_media_elementList');
		$mediaElement .= '_raw';
		$titleElement	= $params->get('media_title_elementList', '');
		$imageElement	= $params->get('media_image_elementList', '');
		if (!empty($imageElement)) {
			$imageElement .= '_raw';
		}
		$infoElement = $params->get('media_info_elementList', '');
		$noteElement = $params->get('media_note_elementList', '');
		$dateElement = $params->get('media_published_elementList', '');

		$listid = $params->get('media_table');

		$listModel = JModel::getInstance('list', 'FabrikFEModel');
		$listModel->setId($listid);
		$list = $listModel->getTable();
		$form = $listModel->getFormModel();
		//remove filters?
		// $$$ hugh - remove pagination BEFORE calling render().  Otherwise render() applies
		// session state/defaults when it calls getPagination, which is then returned as a cached
		// object if we call getPagination after render().  So call it first, then render() will
		// get our cached pagination, rather than vice versa.
		$nav = $listModel->getPagination(0, 0, 0);
		$listModel->render();
		$alldata = $listModel->getData();
		$document = JFactory::getDocument();

		if ($params->get('media_which_player', 'jw') == 'xspf')
		{
			$retstr	= "<?xml version=\"1.0\" encoding=\"".$document->_charset."\"?>\n";
			$retstr .= "<playlist version=\"1\" xmlns = \"http://xspf.org/ns/0/\">\n";
			$retstr .= "	<title>" . $list->label . "</title>\n";
			$retstr .= "	<trackList>\n";
			foreach ($alldata as $data) {
				foreach ($data as $row) {
					if (!isset($row->$mediaElement)) {
						continue;
					}
					$location = $row->$mediaElement;
					if (empty($location)) {
						continue;
					}
					$location = str_replace('\\','/',$location);
					$location = JString::ltrim($location, '/');
					$location = COM_FABRIK_LIVESITE . $location;
					//$location = urlencode($location);
					$retstr .= "		<track>\n";
					$retstr .= "			<location>" . $location . "</location>\n";
					if (!empty($titleElement)) {
						$title = $row->$titleElement;
						$retstr .= "			<title>" . $title . "</title>\n";
					}
					if (!empty($imageElement)) {
						$image = $row->$imageElement;
						if (!empty($image)) {
							$image = str_replace('\\','/',$image);
							$image = JString::ltrim($image, '/');
							$image = COM_FABRIK_LIVESITE . $image;
							$retstr .= "			<image>" . $image . "</image>\n";
						}
					}
					if (!empty($noteElement)) {
						$note = $row->$noteElement;
						$retstr .= "			<annotation>" . $note . "</annotation>\n";
					}
					if (!empty($infoElement)) {
						$link = $row->$titleElement;
						$retstr .= "			<info>" . $link . "</info>\n";
					}
					else {
						$link = JRoute::_('index.php?option=com_fabrik&view=form&formid=' . $form->getId() . '&rowid=' . $row->__pk_val);
						$retstr .= "			<info>" . $link . "</info>\n";
					}
					$retstr .= "		</track>\n";
				}
			}
			$retstr .= "	</trackList>\n";
			$retstr .= "</playlist>\n";
		}
		else
		{
			$retstr	= "<?xml version=\"1.0\" encoding=\"".$document->_charset."\"?>\n";
			$retstr .= '<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">' . "\n";
			$retstr .= "<channel>\n";
			$retstr .= "	<title>" . $list->label . "</title>\n";
			foreach ($alldata as $data) {
				foreach ($data as $row) {
					if (!isset($row->$mediaElement)) {
						continue;
					}
					$location = $row->$mediaElement;
					if (empty($location)) {
						continue;
					}
					$location = str_replace('\\','/',$location);
					$location = JString::ltrim($location, '/');
					$location = COM_FABRIK_LIVESITE . $location;
					//$location = urlencode($location);
					$retstr .= "		<item>\n";
					$retstr .= '			<media:content url="' . $location . '" />' . "\n";
					if (!empty($titleElement)) {
						$title = $row->$titleElement;
						$retstr .= "			<title>" . $title . "</title>\n";
					}
					if (!empty($imageElement)) {
						$image = $row->$imageElement;
						if (!empty($image)) {
							$image = str_replace('\\','/',$image);
							$image = JString::ltrim($image, '/');
							$image = COM_FABRIK_LIVESITE . $image;
							$retstr .= '			<media:thumbnail url="' . $image . '" />' . "\n";
						}
					}
					if (!empty($noteElement)) {
						$note = $row->$noteElement;
						$retstr .= "			<description>" . $note . "</description>\n";
					}
					if (!empty($infoElement)) {
						$link = $row->$titleElement;
						$retstr .= "			<link>" . $link . "</link>\n";
					}
					else {
						$link = JRoute::_('index.php?option=com_fabrik&view=form&formid=' . $form->getId() . '&rowid=' . $row->__pk_val);
						$retstr .= "			<link>" . $link . "</link>\n";
					}
					if (!empty($dateElement)) {
						$pubDate =& JFactory::getDate($row->$dateElement);
						$retstr .= "			<pubDate>".htmlspecialchars($pubDate->toRFC822(),ENT_COMPAT, 'UTF-8')."</pubDate>\n";

					}
					$retstr .= "		</item>\n";
				}
			}
			$retstr .= "</channel>\n";
			$retstr .= "</rss>\n";
		}
		return $retstr;
	}

	function setListIds()
	{
		if (!isset($this->listids)) {
			$params = $this->getParams();
			$this->listids = (array) $params->get('media_table');
		}
	}

	function getMediaName()
	{
		if(is_null($this->mediaName)) {
			$media = $this->_row;
			$this->mediaName = "oMedia{$media->id}";
		}
		return $this->mediaName;
	}

	/**
	* build js string to create the map js object
	* @return string
	*/
	function getJs()
	{
		$params = $this->getParams();
		$str = "head.ready(function() {";
		$viz = $this->getVisualization();
		$opts = new stdClass();
		$opts->which_player = $params->get('media_which_player', 'jw');
		if ($params->get('media_which_player', 'jw') == 'jw')
		{
			$opts->jw_swf_url = COM_FABRIK_LIVESITE.'plugins/fabrik_visualization/media/libs/jw/player.swf';
			$opts->jw_playlist_url = COM_FABRIK_LIVESITE.'index.php?option=com_fabrik&controller=visualization.media&view=visualization&task=getPlaylist&format=raw&visualizationid='.$this->getId();
			$opts->jw_skin = COM_FABRIK_LIVESITE.'plugins/fabrik_visualization/media/libs/jw/skins/' . $params->get('media_jw_skin', 'snel.zip');
		}
		$opts->width = (int)$params->get('media_width', '350');
		$opts->height = (int)$params->get('media_height', '250');
		$opts = json_encode($opts);
		$str .= "fabrikMedia{$viz->id} = new FbMediaViz('media_div', $opts)";
		$str .= "\n" . "Fabrik.addBlock('vizualization_{$viz->id}', fabrikMedia{$viz->id});";
		$str .= "});\n";
		return $str;
	}
}

?>