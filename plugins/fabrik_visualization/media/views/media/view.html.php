<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewMedia extends JView
{

	function display($tmpl = 'default')
	{
		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0) )));
		$this->row = $model->getVisualization();
		$model->setListIds();
		$params = $model->getParams();

		$js = $model->getJs();
		$srcs = FabrikHelperHTML::framework();
		//FabrikHelperHTML::addScriptDeclaration($js);
		$srcs[] = 'plugins/fabrik_visualization/media/media.js';
		if ($params->get('media_which_player', 'jw') == 'jw')
		{
			$srcs[] = 'plugins/fabrik_visualization/media/libs/jw/jwplayer.js';
		}
		FabrikHelperHTML::script($srcs, $js);
		if ($this->row->published == 0)
		{
			JError::raiseWarning(500, JText::_('JERROR_ALERTNOAUTHOR'));
			return '';
		}
		$media = $model->_row;
		$this->media = $model->getMedia();

		$this->assign('params', $params);
		$viewName = $this->getName();
		$pluginManager = FabrikWorker::getPluginManager();
		$plugin = $pluginManager->getPlugIn('media', 'visualization');
		$this->assign('containerId', $this->get('ContainerId'));
		$this->assign('showFilters', JRequest::getInt('showfilters', $params->get('show_filters')) === 1 ?  1 : 0);
		$this->assignRef('filters', $this->get('Filters'));
		$this->assign('params', $model->getParams());
		$pluginParams = $model->getPluginParams();
		$tmpl = $pluginParams->get('media_layout', $tmpl);
		$tmplpath = JPATH_ROOT . '/plugins/fabrik_visualization/media/views/media/tmpl/' . $tmpl;
		$this->_setPath('template', $tmplpath);
		FabrikHelperHTML::stylesheetFromPath('plugins/fabrik_visualization/media/views/media/tmpl/' . $tmpl . '/template.css');
		echo parent::display();
	}

}
?>