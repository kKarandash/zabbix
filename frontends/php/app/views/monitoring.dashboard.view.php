<?php
/*
** Zabbix
** Copyright (C) 2001-2017 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
**/


require_once dirname(__FILE__).'/monitoring.dashboard.view.js.php';

$this->addJsFile('dashboard.grid.js');

$url_list = (new CUrl('zabbix.php'))
	->setArgument('action', 'dashboard.list');
$url_view = (new CUrl('zabbix.php'))
	->setArgument('action', 'dashboard.view')
	->setArgument('dashboardid', $data['dashboard']['dashboardid']);
if ($data['fullscreen']) {
	$url_list->setArgument('fullscreen', '1');
	$url_view->setArgument('fullscreen', '1');
}

$form = (new CForm('post', (new CUrl('zabbix.php'))
	->setArgument('action', 'dashboard.update')
	->getUrl()
))
	->setName('dashboard_sharing_form')
	->addStyle('display: none;');

$user_group_shares_table = (new CTable())
	->setHeader([_('User groups'), _('Permissions'), _('Action')])
	->addStyle('width: 100%;');

$add_user_group_btn = ([(new CButton(null, _('Add')))
	->onClick("return PopUp('popup.php?dstfrm=".$form->getName().
		"&srctbl=usrgrp&srcfld1=usrgrpid&srcfld2=name&multiselect=1')"
	)
	->addClass(ZBX_STYLE_BTN_LINK)
]);

$user_group_shares_table->addRow(
	(new CRow(
		(new CCol($add_user_group_btn))->setColSpan(3)
	))->setId('user_group_list_footer')
);

// User sharing table.
$user_shares_table = (new CTable())
	->setHeader([_('Users'), _('Permissions'), _('Action')])
	->addStyle('width: 100%;');

$add_user_btn = ([(new CButton(null, _('Add')))
	->onClick("return PopUp('popup.php?dstfrm=".$form->getName().
		"&srctbl=users&srcfld1=userid&srcfld2=fullname&multiselect=1')"
	)
	->addClass(ZBX_STYLE_BTN_LINK)]);

$user_shares_table->addRow(
	(new CRow(
		(new CCol($add_user_btn))->setColSpan(3)
	))->setId('user_list_footer')
);

// create form
$form
	->addItem(new CInput('hidden', 'dashboardid', $data['dashboard']['dashboardid']))
	// indicator to help delete all users
	->addItem(new CInput('hidden', 'users['.CControllerDashboardUpdate::EMPTY_USER.']', '1'))
	// indicator to help delete all user groups
	->addItem(new CInput('hidden', 'userGroups['.CControllerDashboardUpdate::EMPTY_GROUP.']', '1'))
	->addItem((new CFormList('sharing_form'))
	->addRow(_('Type'),
		(new CRadioButtonList('private', (int) $data['dashboard']['private']))
			->addValue(_('Private'), PRIVATE_SHARING)
			->addValue(_('Public'), PUBLIC_SHARING)
			->setModern(true)
	)
	->addRow(_('List of user group shares'),
		(new CDiv($user_group_shares_table))
			->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
			->addStyle('min-width: '.ZBX_TEXTAREA_STANDARD_WIDTH.'px;')
	)
	->addRow(_('List of user shares'),
		(new CDiv($user_shares_table))
			->addClass(ZBX_STYLE_TABLE_FORMS_SEPARATOR)
			->addStyle('min-width: '.ZBX_TEXTAREA_STANDARD_WIDTH.'px;')
	)
);

(new CWidget())
	->setTitle($data['dashboard']['name'])
	->setControls((new CForm())
		->cleanItems()
		->addItem((new CList())
			->addItem(get_icon('dashconf', ['enabled' => $data['filter_enabled']]))
			->addItem((new CButton(SPACE))
				->addClass(ZBX_STYLE_BTN_ACTION)
				->setTitle(_('Actions'))
				->setAttribute(
					'data-menu-popup',
					CJs::encodeJson([
						'type' => 'dashboard',
						'label' => _('Actions'),
						'items' => [
							[
								'name' => 'sharing',
								'label' => _('Sharing'),
								'form_data' => [
									'dashboardid' => $data['dashboard']['dashboardid'],
								],
								'disabled' => !$data['dashboard']['editable']
							]
						]
					])
				)
			)
			->addItem(get_icon('fullscreen', ['fullscreen' => $data['fullscreen']]))
		)
	)
	->addItem((new CList())
		->addItem([
			(new CSpan())->addItem(new CLink(_('All dashboards'), $url_list->getUrl())),
			'/',
			(new CSpan())
				->addItem(new CLink($data['dashboard']['name'], $url_view->getUrl()))
				->addClass(ZBX_STYLE_SELECTED)
		])
		->addClass(ZBX_STYLE_OBJECT_GROUP)
	)
	->addItem((new CDiv())->addClass(ZBX_STYLE_DASHBRD_GRID_WIDGET_CONTAINER))
	->addItem($form)
	->show();

/*
 * Javascript
 */
// activating blinking
$this->addPostJS('jqBlink.blink();');

// Initialize dashboard grid
$this->addPostJS(
	'jQuery(".'.ZBX_STYLE_DASHBRD_GRID_WIDGET_CONTAINER.'")'.
		'.dashboardGrid()'.
		'.dashboardGrid("addWidgets", '.CJs::encodeJson($data['grid_widgets']).');'
);
