<?php

/**
 *
 * Category View
 *
 * @package	VirtueMart
 * @subpackage Category
 * @author RickG, jseros
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2022 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 10924 2023-09-29 08:36:50Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for maintaining the list of categories
 *
 * @package	VirtueMart
 * @subpackage Category
 * @author RickG, jseros
 */
class VirtuemartViewCategory extends VmViewAdmin {

	function display($tpl = null) {

		$model = VmModel::getModel('category');
		$layoutName = $this->getLayout();

		$task = vRequest::getCmd('task',$layoutName);
		$this->assignRef('task', $task);

		$this->user = $user = JFactory::getUser();
		if ($layoutName == 'edit') {

			vmLanguage::loadJLang('com_virtuemart_config');

			$category = $model->getCategory(null, false);
			if(!empty($category->_loadedWithLangFallback)){
				vmInfo('COM_VM_LOADED_WITH_LANGFALLBACK',$category->_loadedWithLangFallback);
			}
			$this->setOrigLang($category);

			// Toolbar
			$text='';
			if (isset($category->category_name)) $name = $category->category_name; else $name ='';
			if(!empty($category->virtuemart_category_id)){
				$text = '<a href="'.juri::root().'index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category->virtuemart_category_id.'" target="_blank" >'. $name.'<span class="vm2-modallink"></span></a>';
			}

			$this->SetViewTitle('CATEGORY',$text);

			$model->addImages($category);

			$this->jTemplateList = ShopFunctions::renderTemplateList(vmText::_('COM_VIRTUEMART_ADMIN_CFG_JOOMLA_TEMPLATE_DEFAULT'));

			$cmodel = VmModel::getModel('config');

			$this->categoryLayoutList = $cmodel->getLayoutList('category');

			$this->productLayoutList = $cmodel->getLayoutList('productdetails');

			$this->productsFieldList  = $cmodel->getFieldList('products');

			//Nice fix by Joe, the 4. param prevents setting an category itself as child
			$categorylist = '';//ShopFunctions::categoryListTree(array($parent->virtuemart_category_id), 0, 0, (array) $category->virtuemart_category_id);

			$param = '';
			if(!empty($category->category_parent_id)){
				$param .= '&virtuemart_category_id='.$category->category_parent_id;
			}
			if(!empty($category->virtuemart_category_id)){
				$param .= '&own_category_id='.$category->virtuemart_category_id;
			}
			if(empty($category->published)){
				$param .= '&onlyPublished=0';
			}
			vmJsApi::ajaxCategoryDropDown('category_parent_id', $param, vmText::_('COM_VIRTUEMART_CATEGORY_FORM_TOP_LEVEL'));

			$this->vendorList = '';
			if($this->showVendors()){
                $this->vendorList= ShopFunctions::renderVendorList($category->virtuemart_vendor_id);
			}

			$this->assignRef('category', $category);
			$this->assignRef('categorylist', $categorylist);

			$this->addStandardEditViewCommands($category->virtuemart_category_id,$category);
		}
		else {
			$this->SetViewTitle('CATEGORY_S');

			$keyWord ='';

			$this->assignRef('catmodel',	$model);
			$this->addStandardDefaultViewCommands();
			$this->addStandardDefaultViewLists($model,'category_name');

			$app = JFactory::getApplication ();

			//$topCategory=vRequest::getInt('top_category_id',0);
			$topCategory = $app->getUserStateFromRequest ( 'com_virtuemart.category.top_category_id', 'top_category_id', '', 'int');
			$app->setUserState( 'com_virtuemart.category.top_category_id',$topCategory);
			$param = '';
			if(!empty($topCategory)){
				$param = '&top_category_id='.$topCategory;
			}
			vmJsApi::ajaxCategoryDropDown('top_category_id', $param, vmText::_('COM_VIRTUEMART_CATEGORY_FORM_TOP_LEVEL'));

            $vendor_id = $app->getUserStateFromRequest ( 'com_virtuemart.category.vendor_id', 'virtuemart_vendor_id', '', 'int');
			$this->categories = $model->getCategoryTree($topCategory,-1,false,$this->lists['search'], '', '', false, $vendor_id);

			$catmodel = VmModel::getModel('category');
			foreach($this->categories as $i=>$c){
				$this->categories[$i]->productcount = $catmodel->countProducts($this->categories[$i]->virtuemart_category_id);
			}
			$this->setPaginationDragAndOrderIcons($this->categories);

            $this->lists['vendors'] = '';
            if($this->showVendors()){
                $this->lists['vendors'] = Shopfunctions::renderVendorList($vendor_id, 'virtuemart_vendor_id', true);
            }

			vmdebug('my $this->lists',$this->lists);
			//vmdebug('my categories',$this->categories);
		}


		parent::display($tpl);
	}

	function setPaginationDragAndOrderIcons(&$categories){

		$catsOrderUpDown = array();
		$model = VmModel::getModel('category');
		foreach($categories as $i=>$c){
			//$categories[$i]->productcount = $model->countProducts($c->virtuemart_category_id);

			if(empty($catsOrderUpDown[$c->category_parent_id])){
				$catsOrderUpDown[$c->category_parent_id]['max'] = $c->ordering;
				$catsOrderUpDown[$c->category_parent_id]['min'] = $c->ordering;
			} else {
				$catsOrderUpDown[$c->category_parent_id]['max'] = max($catsOrderUpDown[$c->category_parent_id]['max'],$c->ordering);
				$catsOrderUpDown[$c->category_parent_id]['min'] = min($catsOrderUpDown[$c->category_parent_id]['min'],$c->ordering);
			}

		}

		foreach($categories as $i=>$c){
			if($c->ordering == $catsOrderUpDown[$c->category_parent_id]['max']){
				$categories[$i]->showOrderDown = false;
			} else {
				$categories[$i]->showOrderDown = true;
			}

			if($c->ordering == $catsOrderUpDown[$c->category_parent_id]['min']){
				$categories[$i]->showOrderUp = false;
			} else {
				$categories[$i]->showOrderUp = true;
			}
		}

		$this->catpagination = $model->getPagination();

		$this->showDrag = 0;
		if(count($categories) <= $this->catpagination->limit and $model->_selectedOrderingDir=='ASC' and strpos($model->_selectedOrdering,'ordering')!==FALSE and count($catsOrderUpDown)==1){
			$this->showDrag = 1;
		}
	}
}

// pure php no closing tag
