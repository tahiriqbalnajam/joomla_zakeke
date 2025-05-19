function add_css_tab(element) {
    jQuery(".mo_nav_tab_active ").removeClass("mo_nav_tab_active").removeClass("active");
    jQuery(element).addClass("mo_nav_tab_active");
    jQuery('#create_custom_apis').hide();
    jQuery('#view_current_custom_api').hide();
    jQuery('#create_custom_sql_apis').hide();
    jQuery('#view_current_custom_api').hide();
}

function moCancelForm() {
    jQuery('#cancel_form').submit();
}

function mo_login_page() {
    jQuery('#customer_login_form').submit();
}

function moMediaBack() {
    jQuery('#mo_media_cancel_form').submit();
}

function moCustomUpgrade() {
    jQuery('a[href="#upgrade_plans"]').click();
    add_css_tab("#upgrade_tab");
}

function show_api_creation_window()
{
    jQuery('#API_list').hide();
    jQuery('a[href="#create_custom_apis"]').click();
}


function show_current_api()
{
    
    jQuery('a[href="#view_all_apis"]').click();
}
function save_table_name()
{
    document.getElementById("mo_api_name").value = document.getElementById("api_name").value;
    document.getElementById("mo_method_name").value = document.getElementById("api_method").value;
    document.getElementById("mo_table_name").value = document.getElementById("select_table_name").value;
    document.getElementById("SubmitForm1").click();
}

function copyToClipboard() {
    document.getElementById("mo_custom_api_copy_text1").select();
    document.execCommand("copy");
}

function rm_row(element)
{
   jQuery("#uparow1_"+element).remove();
}

function rm_header_row(element)
{
   jQuery("#uparow2_"+element).remove();
}

function rm_body_row(element)
{
   jQuery("#uparow3_"+element).remove();
}


function add_api_query_param()
{
    apiQueryattr =  jQuery('input[name^=external_api_query_key]');
    var countapiQueryattr = apiQueryattr.length;
    var sel = '<div class="mo_boot_row mo_boot_mt-2 paramAttr" id="uparow1_' + countapiQueryattr + '"><div class="mo_boot_col-3"></div><div class="mo_boot_col-4"><input type="text" class=" mo_boot_form-control" id="external_api_query_key" name="external_api_query_key[' + countapiQueryattr + ']" placeholder="Enter Key" value="" ></div>';
    var sel =sel+' <div class="mo_boot_col-4"><input type="text" class=" mo_boot_form-control" id="external_api_query_val" name="external_api_query_val[' + countapiQueryattr + ']" placeholder="Enter Value"  value="" ></div>';
    var sel=sel+' <div class="mo_boot_col-1"><input type="button" class="mo_boot_btn mo_boot_btn-danger" value="-" onclick="rm_row('+ countapiQueryattr +')" /></div><div> ';

    if(countapiQueryattr!=1){ 
            jQuery(sel).insertAfter(jQuery("#uparow1_" +(countapiQueryattr-1)));
            countapiQueryattr += 1;
    }
    else{
        jQuery(sel).insertAfter(jQuery('#before_query_params'));
        countapiQueryattr += 1;
    }
}

function add_api_header()
{
    apiHeaderattr =  jQuery('input[name^=external_api_header_key]');
    var countapiHeaderattr = apiHeaderattr.length;
    var sel = '<div class="mo_boot_row mo_boot_mt-2 headerAttr" id="uparow2_' + countapiHeaderattr + '"><div class="mo_boot_col-3"></div><div class="mo_boot_col-4"><input type="text" class=" mo_boot_form-control" id="external_api_header_key" name="external_api_header_key[' + countapiHeaderattr + ']" placeholder="Enter Key" value="" ></div>';
    var sel =sel+' <div class="mo_boot_col-4"><input type="text" class=" mo_boot_form-control" id="external_api_header_val" name="external_api_header_val[' + countapiHeaderattr + ']" placeholder="Enter Value"  value="" ></div>';
    var sel=sel+' <div class="mo_boot_col-1"><input type="button" class="mo_boot_btn mo_boot_btn-danger" value="-" onclick="rm_header_row('+ countapiHeaderattr +')" /></div><div> ';

    if(countapiHeaderattr!=1){ 
            jQuery(sel).insertAfter(jQuery("#uparow2_" +(countapiHeaderattr-1)));
            countapiHeaderattr += 1;
    }
    else{
        jQuery(sel).insertAfter(jQuery('#before_api_header'));
        countapiHeaderattr += 1;
    }
}

function add_api_body()
{
    apiBodyattr =  jQuery('input[name^=external_api_body_key]');
    var countapiBodyattr = apiBodyattr.length;
    var sel = '<div class="mo_boot_row mo_boot_mt-2 bodyAttr" id="uparow3_' + countapiBodyattr + '"><div class="mo_boot_col-3"></div><div class="mo_boot_col-4"><input type="text" class=" mo_boot_form-control" id="external_api_body_key" name="external_api_body_key[' + countapiBodyattr + ']" placeholder="Enter Key" value="" ></div>';
    var sel =sel+' <div class="mo_boot_col-4"><input type="text" class=" mo_boot_form-control" id="external_api_body_val" name="external_api_body_val[' + countapiBodyattr + ']" placeholder="Enter Value"  value="" ></div>';
    var sel=sel+' <div class="mo_boot_col-1"><input type="button" class="mo_boot_btn mo_boot_btn-danger" value="-" onclick="rm_body_row('+ countapiBodyattr +')" /></div><div> ';

    if(countapiBodyattr!=1){
        jQuery(sel).insertAfter(jQuery("#uparow3_" +(countapiBodyattr-1)));
        countapiBodyattr += 1;
    }
    else{
        jQuery(sel).insertAfter(jQuery('#before_api_body'));
        countapiBodyattr += 1;
    }
}

function select_body_type()
{
    var RequestType = document.getElementById("request_body_type").value;
    if(RequestType=='x-www-form-urlencode')
    {
        jQuery('#json_body').hide();
        jQuery('#x-www-body').show();

    }else if(RequestType=='JSON')
    {
        jQuery('#json_body').show();
        jQuery('#x-www-body').hide();

    }
}
function show_query()
{
    var method_name=jQuery('#sql_api_method').val();
    if('put'==method_name)
    {
        document.getElementById('sql_query').value="UPDATE #__users set username='{{username}}' where id='{{id}}';";
        document.getElementById('custom_sql_api_method').innerHTML='Update data via API.';
    }else if('post'==method_name)
    {
        document.getElementById('sql_query').value="INSERT INTO #__users (id, name, username, email, registerDate, params) VALUES ('{{id}}','{{name}}','{{username}}','{{email}}','{{registerDate}}','{{params}}');";
        document.getElementById('custom_sql_api_method').innerHTML='Insert data via API.';
    }else if('delete'==method_name)
    {
        document.getElementById('sql_query').value="DELETE FROM #__users WHERE id='{{id}}';";
        document.getElementById('custom_sql_api_method').innerHTML='Delete data via API.';
    }else{
        document.getElementById('sql_query').value="SELECT * FROM #__users WHERE id='{{id}}' AND email='{{email}}';";
        document.getElementById('custom_sql_api_method').innerHTML='Fetch data via API.';
    }
}


function external_api_method()
{
    var method_name=jQuery('#external_api_method_selected').val();
    if('put'==method_name)
    {
        document.getElementById('external_api_method_id').innerHTML='Update external data via API';
    }else if('post'==method_name)
    {
        document.getElementById('external_api_method_id').innerHTML='Insert external data via API';
    }else if('delete'==method_name)
    {
        document.getElementById('external_api_method_id').innerHTML='Delete external data via API';
    }else{
        document.getElementById('external_api_method_id').innerHTML='Fetch external data via API';
    }
}


function showTestWindow(api_name)
{
    var testconfigurl = window.location.href;
    testconfigurl = testconfigurl.substr(0, testconfigurl.indexOf('administrator')) + '?morequest=custom_api&q=test_config&api_name='+api_name;
    var myWindow = window.open(testconfigurl, 'TEST Custom API', 'scrollbars=1 width=800, height=600');
}

function check_values()
{
    if(jQuery('#multiple-checkboxes').val()=='' || jQuery('#multiple-checkboxes').val()===null)
    {
        alert('Please select atleast one column here');
        exit();
    }

    if(jQuery('#mo_condition_select').val()!='None Selected' && jQuery('#mo_query_condition').val()=='no condition')
    {
        alert('Please select condition which want to apply on '+jQuery('#mo_condition_select').val()+' column.');
        exit();
    }
   else if (jQuery('#mo_condition_select').val()=='None Selected' && jQuery('#mo_query_condition').val()!='no condition')
    {
        alert('Please select column on which you want to apply condition.');
        exit();
    }
    jQuery('#create_api_form').submit();
}

jQuery(document).ready(function() {


jQuery('#multiple-checkboxes').multiselect({
    includeSelectAllOption: true,
    enableFiltering: true
  });

  
});

