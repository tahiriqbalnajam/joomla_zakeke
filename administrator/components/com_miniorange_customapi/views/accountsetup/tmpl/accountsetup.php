<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_customapi
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

JHtml::_('jquery.framework');
JHtml::_('stylesheet', JURI::base() . 'components/com_miniorange_customapi/assets/css/miniorange_customapi.css');
JHtml::_('stylesheet', JURI::base() . 'components/com_miniorange_customapi/assets/css/bootstrap-select-min.css');

JHtml::_('script',  'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js');
JHtml::_('stylesheet', JURI::base() . 'components/com_miniorange_customapi/assets/css/miniorange_boot.css');
JHtml::_('script', JURI::base() . 'components/com_miniorange_customapi/assets/js/bootstrap.js');
JHtml::_('stylesheet', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
JHtml::_('script', JURI::base() . 'components/com_miniorange_customapi/assets/js/bootstrap-multiselect.js');
JHtml::_('script', JUri::base() . 'components/com_miniorange_customapi/assets/js/bootstrap-select-min.js');
JHtml::_('stylesheet', JURI::base() . 'components/com_miniorange_customapi/assets/css/boostrap-multiselect.css');
JHtml::_('script', JURI::base() . 'components/com_miniorange_customapi/assets/js/bootstrap-min.js');
JHtml::_('script', JURI::base() . 'components/com_miniorange_customapi/assets/js/utility.js');



if (MocustomapiUtility::is_curl_installed() == 0){ ?>
    <p style="color:red;">(Warning: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a> is not installed or disabled) Please go to Troubleshooting for steps to enable curl.</p>
    <?php
}
$get = JFactory::getApplication()->input->get->getArray();
$tab_name = 'overview';
$active_tab = JFactory::getApplication()->input->get->getArray();


if (isset($active_tab['tab-panel']) && !empty($active_tab['tab-panel'])) {
    $tab_name = $active_tab['tab-panel'];
}

$jVersion = new JVersion();
$jCmsVersion = $jVersion->getShortVersion();
$jCmsVersion = substr($jCmsVersion, 0, 3);
if ($jCmsVersion > 4.0) {
    ?>
    <script>
        jQuery(document).ready(function() {
            jQuery('.btn-group').css("width", "60%");
        });
    </script>
    <?php
}

?>

<div class="form-horizontal">
    <div class="nav-tab-wrapper mo_idp_nav-tab-wrapper">
        
        <a id="custom_api_overview"
           class="mo_nav-tab <?php echo $tab_name == 'overview' ? 'mo_nav_tab_active' : ''; ?>" href="#customapi_plugin_overview"
           onclick="add_css_tab('#custom_api_overview');"
           data-toggle="tab"><?php echo JText::_('COM_MINIORANGE_API_PLUGIN_OVERVIEW');?>
        </a>

        <a id="show_custom_apis"
           class="mo_nav-tab <?php if($tab_name=='show_custom_apis' || $tab_name=='create_custom_api' || ($tab_name=='view_custom_api' && isset($get['api_name']))){ echo 'mo_nav_tab_active';} ?>" href="#view_all_apis"
           onclick="add_css_tab('#show_custom_apis');"
           data-toggle="tab"><?php echo JText::_('COM_MINIORANGE_API_TAB2_SETTINGS');?>
        </a>

        <a id="create_custom_api" href="#create_custom_apis" data-toggle="tab"></a> 

        <a id="view_custom_api" href="#view_current_custom_api" data-toggle="tab"></a> 

        <a id="create_custom_sql_api" href="#create_custom_sql_apis" data-toggle="tab"></a> 

        <a id="configure_external_apis" href="#configured_external_api" data-toggle="tab"></a> 
        
        <a id="create_sql_apis"
           class="mo_nav-tab <?php if($tab_name=='create_sql_apis' || $tab_name=='create_custom_sql_api' || ($tab_name=='view_custom_api' && isset($get['sql_api_name']))){ echo 'mo_nav_tab_active';} ?>"  href="#create_sql_api"
           onclick="add_css_tab('#create_sql_apis');"
           data-toggle="tab"><?php echo JText::_('COM_MINIORANGE_API_TAB3_SETTINGS');?>
        </a>

        <a id="add_authentication"
           class="mo_nav-tab <?php echo $tab_name == 'add_authentication' ? 'mo_nav_tab_active' : ''; ?>" href="#add_authentication_to_api"
           onclick="add_css_tab('#add_authentication');"
           data-toggle="tab"><?php echo JText::_('COM_MINIORANGE_API_TAB4_SETTINGS');?>
        </a>

        <a id="external_apis"
           class="mo_nav-tab  <?php if($tab_name=='custom_external_apis' || $tab_name=='configure_external_apis'){ echo 'mo_nav_tab_active';} ?>" href="#connect_external_apis"
           onclick="add_css_tab('#external_apis');"
           data-toggle="tab"><?php echo JText::_('COM_MINIORANGE_API_TAB5_SETTINGS');?>
        </a>

        <a id="upgrade_tab"
           class="mo_nav-tab <?php echo $tab_name == 'custom_api_upgrade' ? 'mo_nav_tab_active' : ''; ?>" href="#upgrade_plans"
           onclick="add_css_tab('#upgrade_tab');"
           data-toggle="tab"><?php echo JText::_('COM_MINIORANGE_API_PLUGIN_UPGRADE');?>
        </a>

    </div>
</div>

<div class="tab-content" id="myTabContent">
    <div id="customapi_plugin_overview" class="tab-pane <?php echo $tab_name == 'overview' ? 'active' : ''; ?>">
        <div class="row-fluid">
            <div class="mo_sync_table_layout_1">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8" >
                        <div class="mo_boot_p-3 mo_custom_api_tab">
                            <?php custom_api_plugin_overview(); ?>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <div class="mo_boot_p-3 mo_custom_api_tab"> <?php support_form(); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  

    <div id="view_all_apis" class="tab-pane <?php echo $tab_name == 'show_custom_apis' ? 'active' : ''; ?>">
        <div class="row-fluid">
            <div class="mo_sync_table_layout_1">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8" >
                        <div class="mo_boot_p-3 mo_custom_api_tab">
                            <?php show_all_custom_apis(); ?>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <div class="mo_boot_p-3 mo_custom_api_tab"> <?php support_form(); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="create_custom_apis" style="<?php echo ($tab_name=='create_custom_api')?'display:block':'display:none'; ?>">
        <div class="row-fluid">
            <div class="mo_sync_table_layout_1">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8" >
                        <div class="mo_boot_p-3 mo_custom_api_tab">
                            <?php create_custom_apis(); ?>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <div class="mo_boot_p-3 mo_custom_api_tab"> <?php support_form(); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="view_current_custom_api"  style="<?php echo ($tab_name=='view_custom_api')?'display:block':'display:none'; ?>">
        <div class="row-fluid">
            <div class="mo_sync_table_layout_1">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8" >
                        <div class="mo_boot_p-3 mo_custom_api_tab">
                            <?php view_current_custom_api(); ?>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <div class="mo_boot_p-3 mo_custom_api_tab"> <?php support_form(); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   

    <div id="create_sql_api" class="tab-pane <?php echo $tab_name == 'create_sql_apis' ? 'active' : ''; ?>">
        <div class="row-fluid">
            <div class="mo_sync_table_layout_1">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8" >
                        <div class="mo_boot_p-3 mo_custom_api_tab">
                            <?php create_sql_apis(); ?>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <div class="mo_boot_p-3 mo_custom_api_tab"> <?php support_form(); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div id="create_custom_sql_apis" class="tab-pane <?php echo $tab_name == 'create_custom_sql_api' ? 'active' : ''; ?>">
        <div class="row-fluid">
            <div class="mo_sync_table_layout_1">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8" >
                        <div class="mo_boot_p-3 mo_custom_api_tab">
                            <?php create_custom_sql_apis(); ?>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <div class="mo_boot_p-3 mo_custom_api_tab"> <?php support_form(); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="add_authentication_to_api" class="tab-pane <?php echo $tab_name == 'add_authentication' ? 'active' : ''; ?>">
        <div class="row-fluid">
            <div class="mo_sync_table_layout_1">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8" >
                        <div class="mo_boot_p-3 mo_custom_api_tab">
                            <?php add_authentication_to_api(); ?>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <div class="mo_boot_p-3 mo_custom_api_tab"> <?php support_form(); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="connect_external_apis" class="tab-pane <?php echo $tab_name == 'custom_external_apis' ? 'active' : ''; ?>">
        <div class="row-fluid">
            <div class="mo_sync_table_layout_1">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8" >
                        <div class="mo_boot_p-3 mo_custom_api_tab">
                            <?php connect_external_apis(); ?>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <div class="mo_boot_p-3 mo_custom_api_tab"> <?php support_form(); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="configure_external_api" class="tab-pane <?php echo $tab_name == 'configure_external_apis' ? 'active' : ''; ?>">
        <div class="row-fluid">
            <div class="mo_sync_table_layout_1">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-8" >
                        <div class="mo_boot_p-3 mo_custom_api_tab">
                            <?php configure_external_api(); ?>
                        </div>
                    </div>
                    <div class="mo_boot_col-sm-4">
                        <div class="mo_boot_p-3 mo_custom_api_tab"> <?php support_form(); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="upgrade_plans" class="tab-pane <?php echo $tab_name == 'custom_api_upgrade' ? 'active' : ''; ?>">
        <div class="row-fluid">
            <div class="mo_sync_table_layout_1">
                <div class="mo_sync_table_layout">
                    <?php custom_api_licensing_plans(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

function custom_api_plugin_overview()
{
    ?>
     <div class="mo_boot_row ">
            <div class="mo_boot_col-12">
                <h3>Overview</h3>
            </div>
            <div class="mo_boot_col-12"><hr>
                This plugin helps you to create custom endpoints/ Custom REST APIs into Joomla directly with an interactive Graphical User Interface (GUI) to fetch any type of data from any Joomla database tables like user groups to featured images, or any custom data or fields as well. 
                You can also use functions like GET, POST, PUT, DELETE (Insert, Update, Delete) data with these created Custom endpoint / Custom REST routes.<br><br>
                <strong>Joomla Custom API Free plugin</strong> is only for POC purposes and supports only GET request and allow to create limited APIs. Click <a href="index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=custom_api_upgrade">here</a> to know more about plans.
                <br><br>
                We also provide <strong> 7 day full featured trial </strong>of our premium plugin. So that you can test our plugin on your environment. Click <a href="mailto:joomlasupport@xecurify.com">here</a> to raise
                query for trial.
            </div>
        </div>
    <?php
}


function support_form()
{
    $current_user = JFactory::getUser();
    $result       = MocustomapiUtility::getCustomerDetails();
    $admin_email  = isset($result['email']) ? $result['email'] : '';
    $admin_phone  = isset($result['admin_phone']) ? $result['admin_phone'] : '';
    if($admin_email == '')
        $admin_email = $current_user->email;
    ?>
    <div id="sp_support_usync">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12 ">
                <h3>Feature Request (24*7 Support)</h3>
            </div>
            <div class="mo_boot_col-sm-12"><hr>
                <form  name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_customapi&view=accountsetup&task=accountsetup.contactUs');?>">
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_boot_row">
                            <p><strong>Need any help? Just give us a call at <span style="color:red">+1 978 658 9387</span></strong></p><br>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_text-center">
                        <div class="mo_boot_col-sm-12">
                            <input style="border: 1px solid #868383 !important;" type="email" class="mo_boot_form-control" name="query_email" value="<?php echo $admin_email; ?>" placeholder="Enter your email" required />
                        </div>
                        <div class="mo_boot_col-sm-12"><br>
                            <textarea  name="query_support" class="mo_boot_form-text-control" style="border-radius:4px;resize: vertical;width:100%; border: 1px solid #868383 !important;" cols="52" rows="5" required placeholder="Write your query here"></textarea>
                        </div>
                    </div>
                    <div class="mo_boot_row mo_boot_text-center">
                        <div class="mo_boot_col-sm-12">
                            <input type="hidden" name="option1" value="mo_usync_login_send_query"/><br>
                            <input type="submit" name="send_query" value="Submit Query" class="mo_boot_btn mo_boot_btn-users_sync" style="margin-top:5px"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

function custom_api_licensing_plans()
{
    $upgradeURL="https://portal.miniorange.com/initializePayment?requestOrigin=joomla_custom_api_premium_plan";
    
   ?>
    <div id="myModal" class="TC_modal" style="display:none">
        <div class="TC_modal-content" style="width: 40%!important;">
            <span class="TC_modal_close" onclick="hidemodal()" >&times;</span><br><br>
            <div class=" mo_boot_text-center">
                <p>
                    You Need to Login / Register in <strong>My Account</strong> tab to Upgrade your License 
                </p><br><br>
                <a href="<?php echo JURI::base()?>index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=account" class="mo_boot_btn mo_boot_btn-primary">LOGIN / REGISTER</a>
            </div>
        </div>
    </div>
    <div class="mo_idp_divided_layout mo-idp-full mo_media_licensing_container" >
        <div class="mo_boot_row mo_boot_mt-5">
            <div class="mo_boot_col-sm-1"></div>
            <div class="mo_boot_col-sm-5">
                <div class="mo_pricingTable">
                    <div class="pricingTable-header" style="height:200px" >
                        <h3 class="heading" style="text-align:center">FREE</h3>
                        <div style="text-align:center;">
                            <a href="#" class="upgrade_button" style="background:white;color:black!important">ACTIVE PLAN</a>
                        </div>
                    </div>  
                    <div class="pricing-content" >
                        <ul>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Limited Custom API's(endpoints) can be made.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Fetch data from any table.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Fetch operation available with single WHERE condition.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Create limited Custom API endpoints with custom SQL Query.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Support for limited External APIs Connection.</li> 
                            <li class="mo_pricing_list"><i class="fa fa-times" style="color:black;" ></i> Fetch operation available with Filters included.</li>
                            <li class="mo_pricing_list"><i class="fa fa-times" style="color:black;" ></i> Support for GET, POST, PUT & DELETE methods.</li>
                            <li class="mo_pricing_list"><i class="fa fa-times" style="color:black;" ></i> Restrict Public Access to Joomla REST APIs using Token Based Authentiction.</li>
                            <li class="mo_pricing_list"><i class="fa fa-times" style="color:black;" ></i> External API integration to fetch data in the Joomla, update data on External API provider side.</li>
                            <li class="mo_pricing_list"><i class="fa fa-times" style="color:black;" ></i> Integration on any Joomla event or any third-party plugin event/action.</li>
                            <li class="mo_pricing_list"><i class="fa fa-times" style="color:black;" ></i> Provide a Short code to use external API in Article.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="mo_boot_col-sm-5">
                <div class="mo_pricingTable">
                    <div class="pricingTable-header" style="height:200px" >
                        <h3 class="heading" style="text-align:center">PREMIUM</h3>
                        <div style="text-align:center">
                            <a class="upgrade_button" target="_blank" href="<?php echo $upgradeURL ?>">
                                Upgarde
                            </a>
                        </div>
                    </div>
                    <div class="pricing-content" >
                        <ul>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Unlimited Custom API's(endpoints) can be made.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Fetch data from any table.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Fetch operation available with multiple custom conditions.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Create unlimited Custom API endpoints with custom SQL Query.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Support for Unlimited External APIs Connection.</li> 
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Fetch operation available with Filters included.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Support for GET, POST, PUT & DELETE methods.</li>     
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Restrict Public Access to Joomla REST APIs using Token Based Authentiction.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> External API integration to fetch data in the Joomla, update data on External API provider side.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check" style="color:#007bff;" ></i> Integration on any Joomla event or any third-party plugin event/action.</li>
                            <li class="mo_pricing_list"><i class="fa fa-check"  style="color:#007bff;" ></i> Provide a Short code to use external API in Article.</li>
                        </ul>
                    </div>
                </div>
            </div> 
        </div>
        <div class="mo_boot_mt-5">
            <h3>Refund Policy -</h3>
            <p>
            At miniOrange, we want our customers to be 100% satisfied with their purchases. In case the licensed plugin you purchased, is not working as advertised, you can report the issue with our Joomla support team within the first 10 days of the purchase. After reporting the issue, our team will try to resolve those issues within the given timeline as stated by the team, and if the issue does not get resolved within the given time period, the whole amount will be refunded.</br></br>
            Note that this policy does not cover the following cases:<br>
            <li>1. Change in mind or change in requirements after purchase.</li>
            <li>2. Infrastructure issues do not allow the functionality to work.</li>
            <br>
            If you have any other queries regarding the return policy email us at <a href="mailto:joomlasupport@xecurify.com">joomlasupport@xecurify.com</a>
            </p>
        </div>
    </div>
    <?php
}

function show_all_custom_apis()
{
    $plugin_settings=MocustomapiUtility::getConfiguration();
    $is_api_exists=(!empty($plugin_settings['mo_custom_apis']) && $plugin_settings['mo_custom_apis']!='[]') ;

    ?>
    <div id="API_list"class="mo_boot_col-12">
        <div class="mo_boot_row">
            <div class="mo_boot_col-8">
                <h3>List of Custom APIs
                    <a href="https://plugins.miniorange.com/setup-custom-api-for-joomla#step3" target="_blank" class="mo_saml_know_more mo_tooltip">
                        <img src="<?php echo JUri::base();?>/components/com_miniorange_customapi/assets/images/list.png" width="20" height="20"><span class="mo_tooltiptext_right">Refer the setup guide for configuration.</span>
                    </a>
                </h3>
            </div>
            <div class="mo_boot_col-4">
                <a class="mo_boot_btn mo_boot_btn-users_sync" style="float:right;<?php  if($is_api_exists){echo 'display:block'; }else{echo 'display:none';} ?>" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_api'; ?>">Create API</a> 
            </div>
        </div><hr>
        <?php 
        if($is_api_exists){
            $custom_api_config=json_decode($plugin_settings['mo_custom_apis']);
            
            ?>
              <div class=" mo_boot_mt-5  mo_boot_table-responsive">
                <table class="table table-striped table-hover table-bordered">
                <tr>
                    <td style="width:30%"><strong>API NAME</strong></td>
                    <td><strong>ACTIONS</strong></td>
                </tr>
                <?php 
                
                    foreach($custom_api_config as $key=> $value)
                    {
                      ?>
                        <tr>
                            <td style="width:20%"><?php echo $key?> </td>
                            <td style="width:20%">
                            <div class="mo_boot_row">
                                <a class="mo_boot_mr-2 mo_boot_ml-2" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_api&api_name='.$key; ?>">Edit</a> |
                                <a class="mo_boot_mr-2 mo_boot_ml-2" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=view_custom_api&api_name='.$key; ?>">View</a> |
                                <form name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_customapi&view=accountsetup&task=accountsetup.deleteCurrentAPI');?>">
                                    <input type="hidden" name="api_name" value="<?php echo $key ?>" >
                                    <button class="mo_boot_btn mo_boot_btn-danger mo_boot_ml-2" style="">Delete</button>
                                </form>
                            </div>
                            </td>
                        </tr>
                      <?php
                    }
                    ?>
            </table>
        </div>
            <?php
        }else{
        ?>
         <div class="mo_boot_mt-3">
            <p><strong>You have not created any custom API.</strong> <a class="mo_boot_btn mo_boot_btn-users_sync" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_api'; ?>">Click here</a> to start.</p>
        </div>
        <?php }?>
    </div>
   
    <?php 
}
function create_custom_apis()
{
    $db=JFactory::getDBO();
    $tables = JFactory::getDbo()->getTableList();
    $plugin_settings=MocustomapiUtility::getConfiguration();
    $get = JFactory::getApplication()->input->get->getArray();
    $edit=0;
    if(isset($get['api_name']))
    {
        $api_configuration=MocustomapiUtility::fetch_api_info($get['api_name'], 'custom');
        $edit=1;
    }

    if(!empty($api_configuration->table_name))
    {
        $columnArr=$db->getTableColumns($api_configuration->table_name);
    }

   
    ?>
    <div id="create_api_function" class="mo_boot_col-12" >
        <div class="mo_boot_row">
            <div class="mo_boot_col-10">
                <h3>Create Custom API
                    <a href="https://plugins.miniorange.com/setup-custom-api-for-joomla#step3" target="_blank" class="mo_saml_know_more mo_tooltip">
                        <img src="<?php echo JUri::base();?>/components/com_miniorange_customapi/assets/images/list.png" width="20" height="20"><span class="mo_tooltiptext_right">Refer the setup guide for configuration.</span>
                    </a>
                </h3>
            </div>
            <div class="mo_boot_col-2">
                <a class="mo_boot_btn mo_boot_btn-danger"  style="float:right !important" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=show_custom_apis'; ?>">Close</a> 
            </div>
        </div><hr>
        <form class="mo_boot_p-2" id="create_api_form" name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_customapi&view=accountsetup&task=accountsetup.createAPI');?>">
            <div class="mo_boot_row mo_boot_mt-3">
                <div class="mo_boot_col-4">
                    <strong><span class="mo_required_field">*</span>API Name:</strong>
                </div>
                <div class="mo_boot_col-8">
                    <input type="text" class="mo_custom_api_textbox mo_boot_form-control" id="api_name" name="api_name" value="<?php echo !empty($api_configuration->api_name)?$api_configuration->api_name:''; ?>" <?php echo ($edit==1)?'readonly':''; ?>>
                </div>
            </div>
            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-4">
                <strong><span class="mo_required_field">*</span>Select Method:</strong>
                </div>
                <div class="mo_boot_col-5">
                    <select name="api_method" id="api_method" class="mo_boot_form-control" >
                        <option value="get" selected >GET</option>
                        <option value="post" disabled>POST[premium]</option>
                        <option value="put" disabled>PUT[premium]</option>
                        <option value="delete" disabled>DELETE[premium]</option>
                    </select>
                </div>
                <div class="mo_boot_col-3">
                    <span id="custom_api_method" style="color: chocolate;">Fetch Data via API</span>
                </div>
            </div>
            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-4">
                    <strong><span class="mo_required_field">*</span>Select Table:</strong>
                </div>
                <div class="mo_boot_col-8">
                    <select name="mo_table_name" id="select_table_name" class="mo_custom_api_textbox mo_boot_form-control" required onchange="save_table_name()">
                        <option value="None Selected">None Selected</option>
                        <?php 
                        foreach($tables as $table_name)
                        {
                           ?>
                           <option value="<?php echo $table_name ?>" <?php if(!empty($api_configuration->mo_table_name)){  echo $table_name==$api_configuration->mo_table_name?'selected':'';} ?>><?php echo $table_name ?></option>
                           <?php 
                        }
                        ?>

                    </select>
                </div>
            </div>
            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-4">
                    <strong><span class="mo_required_field">*</span>Select Columns:</strong>
                </div>
                <div class="mo_boot_col-8">
                   
                    <select class="mo_custom_api_SelectColumn mo_boot_form-control" id="multiple-checkboxes" required multiple="multiple" name="SelectedColumn[]">
                       
                        <?php
                            foreach($columnArr as $column_key => $column_value)
                            {
                                
                                ?>
                                 
                                <option value="<?php echo $column_key?>" 
                                    <?php if(!empty($api_configuration->SelectedColumn)){foreach($api_configuration->SelectedColumn as $col_key=>$col_val)
                                     {  if($column_key==$col_val)
                                        {
                                            echo 'selected';
                                        }
                                     } }?>><?php echo  $column_key ?></option>
                                <?php
                              
                            }
                        ?>
                    
                    <select>
                  
                     
                </div>
            </div>
            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-4">
                    <strong>Choose Column to apply condition</strong>
                </div>
                <div class="mo_boot_col-4">
                    <strong>Choose Condition</strong>
                </div>
                <div class="mo_boot_col-2">
                    <strong>URL Parameters</strong>
                </div>
                <div class="mo_boot_col-1">
                    <button class="mo_boot_btn mo_boot_btn-primary mo_tooltip" id="add_api_cond" disabled>+<span class="mo_tooltiptext">You can add muliple conditions in our premium plan.</span> </button>
                </div>
                <div class="mo_boot_col-1">
                    <button class="mo_boot_btn mo_boot_btn-danger mo_tooltip" id="rm_api_cond" disabled>-<span class="mo_tooltiptext">You can add muliple conditions in our premium plan.</span> </button>
                </div>
            </div>
            <div class="mo_boot_row mo_boot_mt-2">
                <div class="mo_boot_col-4">
                    <select name="col_condition"  id="mo_condition_select" class="mo_boot_form-control" style="width:80% !important">
                        <option value="None Selected">None Selected</option>
                        <?php 
                        foreach($columnArr as $column_key => $column_value)
                        {
                            ?>
                            <option  value="<?php echo $column_key ?>" <?php if(!empty($api_configuration->col_condition)){ echo $column_key==$api_configuration->col_condition?'selected':''; } ?>><?php echo $column_key ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="mo_boot_col-4">
                    <select name="col_condition_name" id="mo_query_condition"  class="mo_boot_form-control test" style="width:80% !important">
                        <option value="no condition" <?php if(!empty($api_configuration->col_condition_name)){echo $api_configuration->col_condition_name=='no condition' ?'selected':''; } ?> >No Condition</option>
                        <option value="=" <?php if(!empty($api_configuration->col_condition_name)){echo $api_configuration->col_condition_name=='=' ?'selected':''; } ?>>Equal</option>
                        <option value="Like" <?php if(!empty($api_configuration->col_condition_name)){echo $api_configuration->col_condition_name=='Like' ?'selected':''; } ?>>Like</option>
                        <option value=">" <?php if(!empty($api_configuration->col_condition_name)){echo $api_configuration->col_condition_name=='>' ?'selected':''; } ?>>Greater Than</option>
                        <option value="Less Than" <?php if(!empty($api_configuration->col_condition_name)){echo $api_configuration->col_condition_name=='Less Than' ?'selected':''; } ?>>Less Than</option>
                        <option value="!=" <?php if(!empty($api_configuration->col_condition_name)){echo $api_configuration->col_condition_name=='!=' ?'selected':''; } ?>>Not Equal</option>
                    </select>
                </div>
                <div class="mo_boot_col-4">
                    <select name="url_param"  class="mo_boot_form-control">
                        <option value="First Parameter" Selected>First Parameter</option>
                        <option value="Second Parameter" disabled>Second Parameter[premium]</option>
                        <option value="Third Parameter" disabled>Third Parameter[premium]</option>
                        <option value="Fourth Parameter" disabled>Fourth Parameter[premium]</option>
                        <option value="Custom Value" disabled>Custom Value[premium]</option>
                    </select>
                </div>
            </div>

            <div class="">
                <div class="mo_boot_row mo_boot_mt-5">
                    <div class="mo_boot_col-4">
                        <strong>Select Filter <sup><a style="color:blue !important; cursor: pointer;"onclick="moCustomUpgrade()">[Premium plan]</a></sup></strong>
                    </div>
                    <div class="mo_boot_col-4">
                        <strong>Select column <sup><a style="color:blue !important; cursor: pointer;"onclick="moCustomUpgrade()">[Premium plan]</a></sup></strong>
                    </div>
                    <div class="mo_boot_col-4">
                        <strong>Select Order <sup><a style="color:blue !important; cursor: pointer;"onclick="moCustomUpgrade()">[Premium plan]</a></sup></strong>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-4">
                    <div class="mo_boot_col-4">
                        <select class="mo_boot_form-control" name="filter_option" style="width:80% !important" readonly>
                            <option value="No Condition" <?php if(!empty($api_configuration->filter_option)){ echo $api_configuration->filter_option=='No Condition'?'selected':''; }?> >No Condition</option>
                            <option value="ORDER BY" <?php if(!empty($api_configuration->filter_option)){ echo $api_configuration->filter_option=='ORDER BY'?'selected':''; }?>>ORDER BY</option>
                        </select>
                    </div>
                    <div class="mo_boot_col-4">
                        <select name="filter_col"  class="mo_boot_form-control" style="width:80% !important" readonly>
                                <option value="None Selected">None Selected</option>
                                <?php
                                    foreach($columnArr as $column_key => $column_value)
                                    {
                                        ?>
                                        <option value="<?php echo $column_key?>"><?php echo  $column_key ?></option>
                                        <?php
                                    }
                                ?>
                    
                        </select>
                    </div>
                    <div class="mo_boot_col-4">
                        <select class="mo_boot_form-control" name="filter_order" readonly>
                            <option value="No Condition">No Condition</option>
                            <option value="ASC">ASC</option>
                            <option value="DESC">DESC</option>  
                        </select>
                    </div>
                </div>
            </div>
            
            <input type="hidden"  name="table_name" value="<?php echo !empty($api_configuration->table_name)?$api_configuration->table_name:''; ?>">
            <div class=" mo_boot_text-center mo_boot_mt-5">
                <input type="button" value="Save" class="mo_boot_btn mo_boot_btn-users_sync" onclick="check_values()"  <?php echo !empty($api_configuration->api_name)?'':'disabled'; ?>>
            </div>
            
        </form>
        <form  method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_customapi&view=accountsetup&task=accountsetup.saveAPIInformation');?>">
            <input type="hidden" id="mo_api_name" name="api_name" >
            <input type="hidden" id="mo_method_name" name="api_method" >
            <input type="hidden" id="mo_table_name" name="mo_table_name">
            <input type="hidden" name="edit_api"  value="<?php echo $edit?>">
            <input type="submit" id="SubmitForm1" name="SubmitForm1" style="visibility: hidden;">
        </form>
    </div>
   
    <?php
}

function connect_external_apis()
{
    $plugin_settings=MocustomapiUtility::getConfiguration();
    $is_external_api_exists=(!empty($plugin_settings['mo_external_apis']) && $plugin_settings['mo_external_apis']!='[]') ;
    
    ?>
    <div class="mo_boot_col-12">
        <div class="mo_boot_row">
            <div class="mo_boot_col-8">
                <h3>List of Configured External APIs
                    <a href="https://plugins.miniorange.com/setup-custom-api-for-joomla#step6" target="_blank" class="mo_saml_know_more mo_tooltip">
                        <img src="<?php echo JUri::base();?>/components/com_miniorange_customapi/assets/images/list.png" width="20" height="20"><span class="mo_tooltiptext_right">Refer the setup guide for configuration.</span>
                    </a>
                </h3>
            </div>
        </div><hr>
        <?php
         if($is_external_api_exists){
            $external_api_config=json_decode($plugin_settings['mo_external_apis']);
  
            ?>
              <div class=" mo_boot_mt-5  mo_boot_table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <tr>
                        <td><strong>API NAME</strong></td>
                        <td><strong>METHOD</strong></td>
                        <td><strong>ACTIONS</strong></td>
                    </tr>
                    <?php 
                    
                    foreach($external_api_config as $key=> $value)
                    {
                      ?>
                        <tr>
                            <td><?php echo $key?> </td>
                            <td><?php echo strtoupper($value->api_method); ?> </td>
                            <td>
                            <div class="mo_boot_row">
                                <a class="mo_boot_mr-2 mo_boot_ml-2"  href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=configure_external_apis&api_name='.$key; ?>">Edit</a> |
                                <a class="mo_boot_mr-2 mo_boot_ml-2"  onclick="showTestWindow(<?php echo '\''. htmlspecialchars($key) . '\'' ?>)">Test</a> |
                                <form name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_customapi&view=accountsetup&task=accountsetup.deleteCurrentExternalAPI');?>">
                                    <input type="hidden" name="api_name" value="<?php echo $key ?>" >
                                    <button class="mo_boot_btn mo_boot_btn-danger mo_boot_ml-2">Delete</button>
                                </form>
                                
                            </div>
                            </td>
                        </tr>
                      <?php
                    }
                    ?>
                </table>
            </div>
            <?php
         }
         else
         {
            ?>
            <div class="mo_boot_mt-3">
                <p><strong>You have not configured any external API.</strong> <a class="mo_boot_btn mo_boot_btn-users_sync" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=configure_external_apis'; ?>">Click here</a> to start. </p>
           </div>
           <?php
         }
            ?>
        <div class="mo_custom_api_note">
            <strong>Note:</strong> With the current plan of the plugin you can configure only one External API. To configure more, upgrade to <a style="color:blue !important; cursor: pointer;"onclick="moCustomUpgrade()">Premium plan</a>.
        </div>
    </div>
    <?php
}

function configure_external_api()
{
    $edit=0;
    $get = JFactory::getApplication()->input->get->getArray();
   
    if(isset($get['api_name']))
    {
        $api_configuration=MocustomapiUtility::fetch_api_info($get['api_name'],'external_api');
        $edit=1;
    }
    $is_query_param_exist=isset($api_configuration->query_params)?(($api_configuration->query_params!='[]')?1:0):0;
    $is_api_header_exist=isset($api_configuration->api_header)?(($api_configuration->api_header!='[]')?1:0):0;
    $is_api_body_exist=isset($api_configuration->api_body)?(($api_configuration->api_body!='[]')?1:0):0;
    $external_api_method=isset($api_configuration->api_method)?MocustomapiUtility::external_api_method_description($api_configuration->api_method):'Fetch external data via API.';
    ?>
        <div class="mo_boot_col-12">
            <div class="mo_boot_row">
                <div class="mo_boot_col-10">
                    <h3>Connect External API
                        <a href="https://plugins.miniorange.com/setup-custom-api-for-joomla#step6" target="_blank" class="mo_saml_know_more mo_tooltip">
                            <img src="<?php echo JUri::base();?>/components/com_miniorange_customapi/assets/images/list.png"  width="20" height="20"><span class="mo_tooltiptext_right">Refer the setup guide for configuration.</span>
                        </a>
                    </h3>
                </div>
                <div class="mo_boot_col-2">
                    <a class="mo_boot_btn mo_boot_btn-danger"  style="float:right !important" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=custom_external_apis'; ?>">Close</a> 
                </div>
            </div><hr>
            <form class="mo_boot_p-2" name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_customapi&view=accountsetup&task=accountsetup.configureExternalAPI');?>">
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-3">
                        <strong><span class="mo_required_field">*</span>API Name:</strong>
                    </div>
                    <div class="mo_boot_col-8">
                        <input type="text" class="mo_custom_api_textbox mo_boot_form-control" id="api_name" name="api_name" value="<?php echo isset($api_configuration->api_name)?$api_configuration->api_name:''; ?>" <?php echo ($edit)?'readonly':''; ?> required>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-4">
                    <div class="mo_boot_col-3">
                        <strong><span class="mo_required_field">*</span>Select Method:</strong>
                    </div>
                    <div class="mo_boot_col-5">
                        <select name="api_method" id="external_api_method_selected" class=" mo_boot_form-control" required onchange="external_api_method()">
                            <option value="get" <?php if(isset($api_configuration->api_method)){echo ($api_configuration->api_method=='get')?'selected':'';} ?> > GET</option>
                            <option value="post" <?php if(isset($api_configuration->api_method)){echo ($api_configuration->api_method=='post')?'selected':'';} ?>>POST</option>
                            <option value="put" <?php if(isset($api_configuration->api_method)){echo ($api_configuration->api_method=='put')?'selected':'';} ?>>PUT</option>
                            <option value="delete" <?php if(isset($api_configuration->api_method)){echo ($api_configuration->api_method=='delete')?'selected':''; } ?> >DELETE</option>
                        </select>
                    </div>
                    <div class="mo_boot_col-3">
                        <span id="external_api_method_id" style="color:chocolate;"><?php echo $external_api_method;?></span>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-3">
                        <strong><span class="mo_required_field">*</span>External API:</strong>
                    </div>
                    <div class="mo_boot_col-8">
                        <input type="text" class=" mo_boot_form-control" id="external_api_val" name="external_api_val" value="<?php echo isset($api_configuration->external_api_val)?$api_configuration->external_api_val:''; ?>"  required>
                    </div>
                </div>
                <div class="mo_boot_mt-3" id="before_query_params">
                <?php 
                if($is_query_param_exist==0)
                {
                    ?>
                     <div class="mo_boot_row" >
                        <div class="mo_boot_col-3">
                            <strong>Query Param:</strong>
                        </div>
                        <div class="mo_boot_col-4">
                            <input type="text" class=" mo_boot_form-control" id="external_api_query_key" name="external_api_query_key[0]" placeholder="Enter Key" value="" >
                        </div>
                        <div class="mo_boot_col-4">
                            <input type="text" class=" mo_boot_form-control" id="external_api_query_val" name="external_api_query_val[0]" placeholder="Enter Value"  value="" >
                        </div>
                        <div class="mo_boot_col-1">
                                <input type="button" class="mo_boot_btn mo_boot_btn-users_sync" value="+" id="" onclick="add_api_query_param()"/>
                        </div>
                    </div>
                    <?php
                }else
                {
                    $query_param_array=json_decode($api_configuration->query_params);
                    $counter=0;
                 
                    foreach($query_param_array as $key=>$value)
                    {
                      
                        ?>
                         <div class="mo_boot_row <?php echo ($counter!=0)?'mo_boot_mt-3':''; ?>" id="uparow1_<?php echo $counter; ?>" >
                            <div class="mo_boot_col-3" >
                                <span style="<?php echo ($counter==0)?'display:block':'display:none'; ?>"><strong>Query Param:</strong></span>
                            </div>
                            <div class="mo_boot_col-4">
                                <input type="text" class=" mo_boot_form-control" id="external_api_query_key" name="external_api_query_key[<?php echo $counter;?>]" placeholder="Enter Key" value="<?php echo $value->external_api_query_key; ?>" >
                            </div>
                            <div class="mo_boot_col-4">
                                <input type="text" class=" mo_boot_form-control" id="external_api_query_val" name="external_api_query_val[<?php echo $counter;?>]" placeholder="Enter Value"  value="<?php echo $value->external_api_query_val; ?>" >
                            </div>
                            <?php 
                            if($counter==0)
                            {
                                ?>
                                  <div class="mo_boot_col-1">
                                    <input type="button" class="mo_boot_btn mo_boot_btn-users_sync" value="+" id="" onclick="add_api_query_param()"/>
                                  </div>
                                <?php
                            }else
                            {
                            ?>
                                <div class="mo_boot_col-1">
                                    <input type="button" class="mo_boot_btn mo_boot_btn-danger" value="-" onclick="rm_row(<?php echo $counter;?>)" />
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                        <?php
                        $counter++;
                    }
                }
                ?>
                </div>
                <div class="mo_boot_mt-3" id="before_api_header">
                <?php
                if($is_api_header_exist==0)
                {
                    ?>
                        <div class="mo_boot_row" >
                            <div class="mo_boot_col-3">
                                <strong>Header:</strong>
                            </div>
                            <div class="mo_boot_col-4">
                                <input type="text" class=" mo_boot_form-control" id="external_api_header_key" name="external_api_header_key[0]" placeholder="Enter Key" value="" >
                            </div>
                            <div class="mo_boot_col-4">
                                <input type="text" class=" mo_boot_form-control" id="external_api_header_val" name="external_api_header_val[0]" placeholder="Enter Value"  value="" >
                            </div>
                            <div class="mo_boot_col-1">
                                    <input type="button" class="mo_boot_btn mo_boot_btn-users_sync" value="+" id="" onclick="add_api_header()"/>
                            </div>
                           
                        </div>
                    <?php
                }else
                {
                    $api_header_array=json_decode($api_configuration->api_header);
                    $counter=0;
                    foreach($api_header_array as $key=>$value)
                    {
                        ?>
                        <div class="mo_boot_row <?php echo ($counter!=0)?'mo_boot_mt-3':''; ?>" id="uparow2_<?php echo $counter; ?>" >
                            <div class="mo_boot_col-3">
                                <span style="<?php echo ($counter==0)?'display:block':'display:none'; ?>"><strong>Header:</strong><span>
                            </div>
                            <div class="mo_boot_col-4">
                                <input type="text" class=" mo_boot_form-control" id="external_api_header_key" name="external_api_header_key[<?php echo $counter;?>]" placeholder="Enter Key" value="<?php echo $value->external_api_query_key; ?>" >
                            </div>
                            <div class="mo_boot_col-4">
                                <input type="text" class=" mo_boot_form-control" id="external_api_header_val" name="external_api_header_val[<?php echo $counter;?>]" placeholder="Enter Value"  value="<?php echo $value->external_api_query_val; ?>" >
                            </div>
                            <?php 
                            if($counter==0)
                            {
                                ?>
                            <div class="mo_boot_col-1">
                                    <input type="button" class="mo_boot_btn mo_boot_btn-users_sync" value="+" id="" onclick="add_api_header()"/>
                            </div>
                            <?php
                            }else
                            {
                            ?>
                                <div class="mo_boot_col-1">
                                    <input type="button" class="mo_boot_btn mo_boot_btn-danger" value="-" onclick="rm_header_row(<?php echo $counter;?>)" />
                                </div>
                            <?php
                            }
                            ?>
                        </div>

                        <?php
                        $counter++;
                    }
                }
                ?>
                </div>
               
                <div class="mo_boot_row mo_boot_mt-3" >
                    <div class="mo_boot_col-3">
                        <strong>Request Body:</strong>
                    </div>
                    <div class="mo_boot_col-8">
                        <select name="request_body_type" id="request_body_type" class=" mo_boot_form-control" onchange="select_body_type()" >
                            <option value="x-www-form-urlencode"  <?php if(isset($api_configuration->request_body_type)){echo ($api_configuration->request_body_type=='x-www-form-urlencode')?'selected':''; }?> > x-www-form-urlencode</option>
                            <option value="JSON"  <?php if(isset($api_configuration->request_body_type)){ echo ($api_configuration->request_body_type=='JSON')?'selected':'';} ?> >JSON</option>
                        </select>
                    </div>
                </div>
                <div id="x-www-body"  style="<?php if(((isset($api_configuration->request_body_type) && $api_configuration->request_body_type=='x-www-form-urlencode'))|| !isset($api_configuration->request_body_type)){echo 'display:block';}else{echo 'display:none';} ?>">
                    <?php
                    if($is_api_body_exist==0)
                    {
                        ?>
                        <div class="mo_boot_row mo_boot_mt-3" id="before_api_body">
                            <div class="mo_boot_col-3">
                            </div>
                            <div class="mo_boot_col-4">
                                <input type="text" class=" mo_boot_form-control" id="external_api_body_key" name="external_api_body_key[0]" placeholder="Enter Key" value="" >
                            </div>
                            <div class="mo_boot_col-4">
                                <input type="text" class=" mo_boot_form-control" id="external_api_body_val" name="external_api_body_val[0]" placeholder="Enter Value"  value="" >
                            </div>
                            <div class="mo_boot_col-1">
                                    <input type="button" class="mo_boot_btn mo_boot_btn-users_sync" value="+" id="" onclick="add_api_body()"/>
                            </div>
                        </div>
                    <?php 
                    }
                    else
                    {
                        $api_body_array=json_decode($api_configuration->api_body);
                        $counter=0;
                        foreach($api_body_array as $key=>$value)
                        {
                            ?>
                                <div class="mo_boot_row mo_boot_mt-3" id="uparow3_<?php echo $counter; ?>">
                                    <div class="mo_boot_col-3">
                                    </div>
                                    <div class="mo_boot_col-4">
                                        <input type="text" class=" mo_boot_form-control" id="external_api_body_key" name="external_api_body_key[<?php echo $counter; ?>]" placeholder="Enter Key" value="<?php echo $value->external_api_query_key; ?>" >
                                    </div>
                                    <div class="mo_boot_col-4">
                                        <input type="text" class=" mo_boot_form-control" id="external_api_body_val" name="external_api_body_val[<?php echo $counter; ?>]" placeholder="Enter Value"  value="<?php echo $value->external_api_query_val; ?>" >
                                    </div>
                                    <?php 
                                    if($counter==0)
                                    {
                                        ?>
                                        <div class="mo_boot_col-1">
                                                <input type="button" class="mo_boot_btn mo_boot_btn-users_sync" value="+" id="" onclick="add_api_body()"/>
                                        </div>
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                         <div class="mo_boot_col-1">
                                            <input type="button" class="mo_boot_btn mo_boot_btn-danger" value="-" onclick="rm_body_row(<?php echo $counter;?>)" />
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            <?php
                            $counter++;
                        }
                    }
                    ?>
                </div>
                <div id="json_body" style="<?php if(isset($api_configuration->request_body_type)){echo ($api_configuration->request_body_type=='JSON')?'display:block':'display:none';}else{echo 'display:none';}?>"> 
                    <div class="mo_boot_row mo_boot_mt-3">
                        <div class="mo_boot_col-3">
                        </div>
                        <div class="mo_boot_col-9">
                            <textarea name="json_body_val" style="width:90%" rows="4" cols="50"><?php echo isset($api_configuration->json_body_val)?$api_configuration->json_body_val:'';?></textarea>
                        </div>
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3" id="">
                    <div class="mo_boot_col-3">
                        <strong>Response Data Type:</strong>
                    </div>
                    <div class="mo_boot_col-8">
                        <select name="response_data_type" id="response_data_type" class="mo_custom_api_textbox mo_boot_form-control" >
                            <option value="JSON" selected>JSON</option>
                            <option value="XML" disabled>XML[premium]</option>
                        </select>
                    </div>
                </div>
                <div class=" mo_boot_text-center mo_boot_mt-5">
                    <button class="mo_boot_btn mo_boot_btn-success">Save</button>
                    <input  type="button" id='test-config' class="mo_boot_btn mo_boot_btn-success" <?php echo ($edit==0)?'disabled':''; ?> onclick='showTestWindow(<?php echo isset($api_configuration->api_name)?"\"" . htmlspecialchars($api_configuration->api_name) . "\"":""; ?>)' value="Test Configuration">
                </div>
                <input type="hidden" name="edit_api" value="<?php echo $edit?>">
            </form>
        </div>
    <?php
}


function create_sql_apis()
{
    $plugin_settings=MocustomapiUtility::getConfiguration();
    $is_sql_api_exists=(!empty($plugin_settings['mo_custom_sql_apis']) && $plugin_settings['mo_custom_sql_apis']!='[]') ;
    ?>
    <div class="mo_boot_col-12">
        <div class="mo_boot_row">
            <div class="mo_boot_col-8">
                <h3>List of Custom SQL APIs
                    <a href="https://plugins.miniorange.com/setup-custom-api-for-joomla#stepg" target="_blank" class="mo_saml_know_more mo_tooltip">
                        <img src="<?php echo JUri::base();?>/components/com_miniorange_customapi/assets/images/list.png" width="20" height="20"><span class="mo_tooltiptext_right">Refer the setup guide for configuration.</span>
                    </a>
                </h3>
            </div>
        </div><hr>
        <?php 
        if($is_sql_api_exists){
            $custom_sql_api_config=json_decode($plugin_settings['mo_custom_sql_apis']);
            ?>
              <div class=" mo_boot_mt-5  mo_boot_table-responsive">
                <table class="table table-striped table-hover table-bordered">
                <tr>
                    <td><strong>API NAME</strong></td>
                    <td><strong>API Method</strong></td>
                    <td><strong>ACTIONS</strong></td>
                </tr>
                <?php 
                
                    foreach($custom_sql_api_config as $key=> $value)
                    {
                    
                      ?>
                        <tr>
                            <td><?php echo $key; ?> </td>
                            <td>
                                <?php echo !empty($value->api_method)?strtoupper($value->api_method):''; ?>
                             </td>
                            <td >
                            <div class="mo_boot_row">
                                <a class="mo_boot_mr-2 mo_boot_ml-2"  href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_sql_api&sql_api_name='.$key; ?>">Edit</a> |
                                <a class="mo_boot_mr-2 mo_boot_ml-2" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=view_custom_api&sql_api_name='.$key; ?>">View</a> |
                                <form name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_customapi&view=accountsetup&task=accountsetup.deleteSQLCurrentAPI');?>">
                                    <input type="hidden" name="api_name" value="<?php echo $key ?>" >
                                    <button class="mo_boot_btn mo_boot_btn-danger mo_boot_ml-2">Delete</button>
                                </form>
                            </div>
                            </td>
                        </tr>
                      <?php
                    }
                    ?>
            </table>
        </div>
            <?php
        }else{
        ?>
         <div class="mo_boot_mt-3">
            <p><strong>You have not created any custom SQL API.</strong> <a class="mo_boot_btn mo_boot_btn-users_sync"  href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_sql_api'; ?>">Click here</a> to start.</p>
        </div>
        <?php }?>

        <div class="mo_custom_api_note">
            <strong>Note: </strong>With the current plan of the plugin you can create only one custom sql API. To create more, upgrade to  <a style="color:blue !important; cursor: pointer;"onclick="moCustomUpgrade()">Premium plan</a>.
        </div>
    </div>
    <?php
}

function create_custom_sql_apis()
{
    $plugin_settings=MocustomapiUtility::getConfiguration();
    $get = JFactory::getApplication()->input->get->getArray();
    $edit=0;
    if(isset($get['sql_api_name']))
    {
        $sql_api_configuration=MocustomapiUtility::fetch_api_info($get['sql_api_name'],'sql');
        $edit=1;
    }
    $sql_api_method_use=isset($sql_api_configuration->api_method)?MocustomapiUtility::api_method_description($sql_api_configuration->api_method):'Fetch data via API.';
    ?>
        <div class="mo_boot_col-12">
            <div class="mo_boot_row">
                <div class="mo_boot_col-10">
                    <h3>Create Custom SQL API:</h3>
                </div>
                <div class="mo_boot_col-2">
                    <a class="mo_boot_btn mo_boot_btn-danger"  style="float:right !important" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_sql_apis'; ?>">Close</a> 
                </div>
            </div><hr>
        </div>
        <form class="mo_boot_p-2" name="f" method="post" action="<?php echo JRoute::_('index.php?option=com_miniorange_customapi&view=accountsetup&task=accountsetup.createSQLAPI');?>">
            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-4">
                    <strong><span class="mo_required_field">*</span>API Name:</strong>
                </div>
                <div class="mo_boot_col-8">
                    <input type="text" class=" mo_boot_form-control" placeholder="Enter a API name without space" id="api_name" name="api_name" value="<?php echo !empty($sql_api_configuration->api_name)?$sql_api_configuration->api_name:''; ?>" <?php echo ($edit)?'readonly':''; ?> required>
                </div>
            </div>
            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-4">
                    <strong><span class="mo_required_field">*</span>Select Method:</strong>
                </div>
                <div class="mo_boot_col-5">
                    <select name="api_method" id="sql_api_method" class=" mo_boot_form-control" required onchange="show_query()" >
                        <option value="get" <?php if(!empty($sql_api_configuration->api_method)){ echo ($sql_api_configuration->api_method=='get')?'selected':''; } ?>>GET</option>
                        <option value="post" <?php if(!empty($sql_api_configuration->api_method)){ echo ($sql_api_configuration->api_method=='post')?'selected':'';} ?>>POST</option>
                        <option value="put" <?php if(!empty($sql_api_configuration->api_method)){ echo ($sql_api_configuration->api_method=='put')?'selected':'';} ?>>PUT</option>
                        <option value="delete" <?php if(!empty($sql_api_configuration->api_method)){ echo ($sql_api_configuration->api_method=='delete')?'selected':'';} ?>>DELETE</option>
                    </select>
                </div>
                <div class="mo_boot_col-3">
                    <span id="custom_sql_api_method" style="color:chocolate;"><?php echo $sql_api_method_use ;?></span>
                </div>
            </div>
            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-4">
                    <strong>Enable custom query parameters:</strong>
                </div>
                <div class="mo_boot_col-4">
                    <input type="checkbox" id="enable_cust_param" name="enable_cust_query_param" value="1" <?php echo (!empty($sql_api_configuration->enable_cust_query_param))?'checked':''; ?> >
                </div>
            </div>
            <div class="mo_boot_row mo_boot_mt-4">
                <div class="mo_boot_col-4">
                    <strong><span class="mo_required_field">*</span>Enter SQL Query:</strong>
                </div>
                <div class="mo_boot_col-8" >
                    <textarea name="sql_query" id="sql_query" rows="4" cols="50" class="mo_boot_form-text-control" required><?php if(!empty($sql_api_configuration->sql_query)){ echo trim($sql_api_configuration->sql_query);}else{ echo " SELECT * FROM #__users WHERE id='{{id}}' AND email='{{email}}';";} ?></textarea>
                </div>
            </div>
            <input type="hidden" name="edit_api"  value="<?php echo $edit?>">
            <div class=" mo_boot_text-center mo_boot_mt-5">
                <button class="mo_boot_btn mo_boot_btn-users_sync">Save</button>
            </div>
        </form>
    <?php
}

function add_authentication_to_api()
{
    $bearer_token = bin2hex(random_bytes(32));
 
    ?>
    <div class="mo_boot-col-12">
        <div class="mo_boot_row">
            <div class="mo_boot_col-10">
                <h3>API Token Authentication <sup><a href='#' onclick="moCustomUpgrade()">[Premium]</a></sup>
                    <a href="https://plugins.miniorange.com/setup-custom-api-for-joomla#step5" target="_blank" class="mo_saml_know_more mo_tooltip">
                        <img src="<?php echo JUri::base();?>/components/com_miniorange_customapi/assets/images/list.png" width="20" height="20"><span class="mo_tooltiptext_right">Refer the setup guide for configuration.</span>
                    </a>
                </h3>
            </div>
        </div><hr>
        <div class="mo_boot_mt-2">
            <div class="mo_boot_row">
                <div class="mo_boot_col-1">
                    <label class="mo_custom_api_switch">
                        <input value="1" name="mo_enable_token_based_auth" type="checkbox" id="mo_enable_token_based_auth" disabled >
                        <span class="mo_custom_api_slider round"  style="background-color: #ccc !important;"></span>
                    </label>
                </div>
                <div class="mo_boot_col-10 mo_boot_ml-1">
                    <strong>Enable Token based Authentication</strong>
                </div>
            </div>
        </div>
        <div class="mo_boot_row mo_boot_mt-3">
            <div class="mo_boot_p-3">
                <p> Choose HTTP Methods which you want to restrict from public access :</p>
            </div>
        </div>
        <div class="mo_boot_row mo_boot_mt-2 mo_boot_ml-2">
            <input type="checkbox" class="mo_http_methods" id="get_check" name="get_check" value="GET" disabled >
            <label for="get_check" class="mo_boot_ml-2"> GET </label>
        </div>
        <div class="mo_boot_row mo_boot_mt-2 mo_boot_ml-2">
            <input type="checkbox" class="mo_http_methods" id="post_check" name="post_check" value="POST" disabled >
            <label for="post_check" class="mo_boot_ml-2"> POST</label>
        </div>
        <div class="mo_boot_row mo_boot_mt-2 mo_boot_ml-2">
            <input type="checkbox" class="mo_http_methods" id="put_check" name="put_check" value="PUT" disabled >
            <label for="put_check" class="mo_boot_ml-2"> PUT</label>
        </div>
        <div class="mo_boot_row mo_boot_mt-2 mo_boot_ml-2">
            <input type="checkbox" class="mo_http_methods" id="del_check" name="del_check" value="Delete" disabled>
            <label for="del_check" class="mo_boot_ml-2"> DELETE</label>
        </div>   
        <div class=" mo_boot_text-center mo_boot_mt-5">
            <button class="mo_boot_btn mo_boot_btn-users_sync" disabled >Save</button>
        </div><hr>   
        <div class="mo_boot_p-2">
            <p>You can use the below Bearer Token to authenticate your Joomla REST APIs.</p>
        </div>                     
        <div class="mo_boot_row mo_boot_mt-3 ">
            <div class="mo_boot_col-4 mo_boot_ml-2">
                <strong>Bearer Token:</strong>
            </div>
            <div class="mo_boot_col-6 mo_custom_api_breaken_wrap"  >
                <div class="mo_boot_row">
                    <div class="mo_boot_col-10">
                        <input type="password" class="mo_custom_api_bearer_token_input" id="api_token_based" name="api_name" value="<?php echo $bearer_token; ?>" readonly>
                    </div>
                    <div class="mo_boot_col-2">
                        <i class="fa fa-solid fa-eye-slash" id="password_btn" style=" padding: 10px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class=" mo_boot_text-center mo_boot_mt-5">
            <button class="mo_boot_btn mo_boot_btn-users_sync" disabled >Generate New Token</button> 
        </div> 
    </div>
    <?php
}

function view_current_custom_api()
{
    $plugin_settings=MocustomapiUtility::getConfiguration();
    $get = JFactory::getApplication()->input->get->getArray();
    $api_url='';
    if(isset($get['api_name']))
    {
        $api_configuration=MocustomapiUtility::fetch_api_info($get['api_name'],'custom');
        $api_url=JURI::root().MocustomapiUtility::getAPIByVersion().$api_configuration->api_name;
        $tab_name='show_custom_apis';
        $guide_link=MocustomapiUtility::getGuideLinks($tab_name,$api_configuration->api_method,1);
        $table_name=$api_configuration->table_name;
        $custom_data='';
        if(!empty($api_configuration->col_condition) && ($api_configuration->col_condition!='None Selected'))
        {
            $custom_data.=$api_configuration->col_condition.'={' . $api_configuration->col_condition . '_value}';;
        }
    }

    if(isset($get['sql_api_name']))
    {
        $api_configuration=MocustomapiUtility::fetch_api_info($get['sql_api_name'],'sql');
        $sql_query=trim($api_configuration->sql_query);
        $customparams=MocustomapiUtility::get_custom_param($sql_query);
        $api_url=JURI::root().MocustomapiUtility::getAPIByVersion().$api_configuration->api_name;
        $custom_data='';
        if(isset($api_configuration->enable_cust_query_param) && !empty($customparams))
        {
            for ($i=0; $i< sizeof($customparams); $i++) {
                    
                $custom_data = $custom_data . $customparams[$i] . '={' . $customparams[$i] . '_value}';
                if($i != sizeof($customparams) - 1){
                    $custom_data = $custom_data . '& ';
                }                  
            }
        }
        $tab_name='create_sql_apis';
        $table_name='#__'.MocustomapiUtility::fetch_table_name($sql_query);
        $guide_link=MocustomapiUtility::getGuideLinks($tab_name,$api_configuration->api_method,2);
    }
    ?>
 
    <div id="show_current_api" class="mo_boot_col-12" >
        <div class="mo_boot_row">
            <div class="mo_boot_col-8">
                <h3><?php echo strtoupper($api_configuration->api_method) ?>/<span style="color:blue;"><?php if(!empty($api_configuration->api_name)){ echo $api_configuration->api_name; }?><span>
                    <a href="<?php echo $guide_link;?>" target="_blank" class="mo_saml_know_more mo_tooltip">
                        <img src="<?php echo JUri::base();?>/components/com_miniorange_customapi/assets/images/list.png" width="20" height="20"><span class="mo_tooltiptext_right">Refer the setup guide for configuration.</span>
                    </a>
                </h3>
            </div>
            <div class="mo_boot_col-4">
                <a class="mo_boot_btn mo_boot_btn-danger"  style="float:right !important" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel='.$tab_name.''; ?>">Close</a> 
            </div>
        </div><hr>
        <div class="mo_boot_row mo_boot_mt-5">
            <div class="mo_boot_col-2">
                <div class="mo_custom_api_method_name"><?php echo strtoupper($api_configuration->api_method) ?></div>
            </div>
            <div class="mo_boot_col-9">
                <input id="mo_custom_api_copy_text1" class="mo_boot_form-control" value="<?php echo $api_url?>" readonly>
            </div>
            <div class="mo_boot_col-1">
                <em class="fa fa-lg fa-copy mo_copy_api_url" onclick="copyToClipboard();" >  </em>
            </div>
        </div>

        <div class="mo_boot_row mo_boot_mt-4  mo_boot_table-responsive">
            <div class="mo_cusotm_view_api_table_top">
                <h4><strong>API Table</strong>
                <span style="float:right">
                    <a href="<?php echo $guide_link;?>" target="_blank" class="mo_custom_api_setup_guide_button mo_api_know_more mo_tooltip">
                        <img src="<?php echo JUri::base();?>/components/com_miniorange_customapi/assets/images/list.png" width="20" height="20"> Setup Guide<span class="mo_tooltiptext_right">To use the generated API, refer to the setup guide.</span>
                    </a>
                </span>
                </h4>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td style="width:30% !important"><strong>Table Name</strong></td>   
                    <td style="width:70% !important"> '<?php echo  $table_name; ?>'</td>
                </tr>
            </table>
        </div>

        <div class="mo_boot_row mo_boot_mt-5  mo_boot_table-responsive">
            <div class="mo_cusotm_view_api_table_top">
                <h4>
                    <strong>Example</strong>
                    <span style="float:right">
                        <a href="<?php echo $guide_link;?>" target="_blank" class="mo_custom_api_setup_guide_button mo_api_know_more mo_tooltip">
                            <img src="<?php echo JUri::base();?>/components/com_miniorange_customapi/assets/images/list.png" width="20" height="20"> Setup Guide<span class="mo_tooltiptext_right">To use the generated API, refer to the setup guide.</span>
                        </a>
                    </span>
                </h4>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td><strong>Request</strong></td>
                    <td><strong>Format</strong></td>
                </tr>
                <tr>
                    <td>cURL</td>
                    <td>curl -X <?php echo strtoupper($api_configuration->api_method) ?> <?php echo $api_url?><?php if($api_configuration->api_method=='get' && !empty($custom_data)){ echo '?'.$custom_data;}else if($api_configuration->api_method!='get' && !empty($custom_data)){ echo '<br><strong>'.$api_configuration->api_method.' variables </strong>:'.$custom_data; } ?> </td>
                </tr>
            </table>
        </div>

        <?php 
        if($tab_name=='show_custom_apis')
        { ?>
        <div class="mo_boot_row mo_boot_mt-5 mo_boot_table-responsive"> 
            <div class="mo_cusotm_view_api_table_top">
                <h4>
                    <strong>Request Format</strong>
                    <span style="float:right">
                        <a href="<?php echo $guide_link;?>" target="_blank" class="mo_custom_api_setup_guide_button mo_api_know_more mo_tooltip">
                            <img src="<?php echo JUri::base();?>/components/com_miniorange_customapi/assets/images/list.png" width="20" height="20"> Setup Guide<span class="mo_tooltiptext_right">To use the generated API, refer to the setup guide.</span>
                        </a>
                    </span>
                </h4>
            </div>
            <table class="table  table-bordered">
                <tr>
                    <td><strong>Column Name</strong></td>
                    <td><strong>Description</strong></td>
                    <td><strong>Condtions Applied</strong></td>
                    <td><strong>Parameter Place in API</strong></td>
                </tr>
        
                <tr>
                    <td><?php  if(!empty($api_configuration->col_condition)){ echo ($api_configuration->col_condition!='None Selected')?$api_configuration->col_condition:'--'; }else{echo '--';} ?> </td>
                    <td><?php  if(!empty($api_configuration->col_condition)){ echo ($api_configuration->col_condition!='None Selected')?'The value of "'.$api_configuration->col_condition.'" column will be replaced with {'.$api_configuration->col_condition.'_value} in request.':'--'; }else{echo '--'; }  ?></td>
                    <td><?php  if(!empty($api_configuration->col_condition)){ echo ($api_configuration->col_condition_name!='no condition')?$api_configuration->col_condition_name:'--';}else{echo '--'; }  ?> </td>
                    <td><?php  if(!empty($api_configuration->col_condition)){ echo ($api_configuration->col_condition!='None Selected')?'First':'--';}else{echo '--'; }  ?></td>
                </tr>

            </table>
        </div>
        <?php }?>
    
        <?php 
        if($tab_name=='create_sql_apis')
        {
        ?>
            <div class="mo_boot_row mo_boot_mt-5 mo_boot_table-responsive"> 
                <div class="mo_cusotm_view_api_table_top"><h4><strong>SQL Query</strong></h4></div>
                <table class="table  table-bordered">
                    <tr>
                        <td><strong>Query</strong></td>
                        <td><?php echo trim($api_configuration->sql_query); ?></td>
                    </tr>
                </table>
            </div>
        <?php }
         
        if($tab_name=='show_custom_apis')
        {
        ?>
        <div class=" mo_boot_mt-5  mo_boot_text-center">
            <a class="mo_boot_btn mo_boot_btn-users_sync mo_boot_ml-2" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_api&api_name='.$api_configuration->api_name; ?>">Edit API</a> 
        </div>
        <?php
        }

        if($tab_name=='create_sql_apis')
        {
        ?>
        <div class=" mo_boot_mt-5  mo_boot_text-center">
            <a class="mo_boot_btn mo_boot_btn-users_sync mo_boot_ml-2" href="<?php echo JURI::root().'administrator/index.php?option=com_miniorange_customapi&view=accountsetup&tab-panel=create_custom_sql_api&sql_api_name='.$api_configuration->api_name; ?>">Edit API</a> 
        </div>
        <?php 
        }
        ?>

    </div>
    <?php
}
?>
