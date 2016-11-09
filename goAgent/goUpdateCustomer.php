<?php
####################################################
#### Name: goUpdateCustomer.php                 ####
#### Type: API for Agent UI                     ####
#### Version: 0.9                               ####
#### Copyright: GOAutoDial Inc. (c) 2011-2016   ####
#### Written by: Christopher P. Lomuntad        ####
#### License: AGPLv2                            ####
####################################################

if (isset($_GET['goLeadInfo'])) { $lead_info = $_GET['goLeadInfo']; }
    else if (isset($_POST['goLeadInfo'])) { $lead_info = $_POST['goLeadInfo']; }
if (isset($_GET['goCustomInfo'])) { $custom_info = $_GET['goCustomInfo']; }
    else if (isset($_POST['goCustomInfo'])) { $custom_info = $_POST['goCustomInfo']; }
if (isset($_GET['goSaveAsCustomer'])) { $save_as_customer = $astDB->escape($_GET['goSaveAsCustomer']); }
    else if (isset($_POST['goSaveAsCustomer'])) { $save_as_customer = $astDB->escape($_POST['goSaveAsCustomer']); }

$agent = get_settings('user', $astDB, $goUser);

$lead_array = array();
foreach ($lead_info as $idx => $info) {
    $lName = str_replace('viewCust_', '', $info['name']);
    $delim = "";
    if (!preg_match("/lead_id|list_id/", $lName)) {
        if (array_key_exists($lName, $lead_array)) {
            $delim = ",";
        }
        $lead_array[$lName] = $delim . $astDB->escape($info['value']);
    } else {
        ${$lName} = $astDB->escape($info['value']);
    }
}

$astDB->where('lead_id', $lead_id);
$rslt = $astDB->update('vicidial_list', $lead_array);
$errorMsg = $astDB->getLastError();

if (strlen($errorMsg) < 1) {
    if (isset($custom_info) || $custom_info != null) {
        $CF_array = array();
        foreach ($custom_info as $idx => $info) {
            $lName = preg_replace("/^viewCustom_|\[\]$/", '', $info['name']);
            $delim = "";
            if (array_key_exists($lName, $CF_array)) {
                $delim = "{$CF_array[$lName]},";
            }
            $CF_array[$lName] = $delim . $astDB->escape($info['value']);
        }
        
        $astDB->where('lead_id', $lead_id);
        $rslt = $astDB->getOne("custom_{$list_id}");
        $CF_cnt = $astDB->getRowCount();
        
        if ($CF_cnt > 0) {
            $astDB->where('lead_id', $lead_id);
            $rslt = $astDB->update("custom_{$list_id}", $CF_array);
        } else {
            $CF_array['lead_id'] = $lead_id;
            $rslt = $astDB->insert("custom_{$list_id}", $CF_array);
        }
        
        $errorMsg = $astDB->getLastError();
    }
    
    $result = 'success';
    $message = "Lead file '{$lead_id}' updated successfully.";
    
    $goDB->where('user_group', $agent->user_group);
    $rslt = $goDB->getOne('user_access_group', 'group_list_id');
    $group_list_id = $rslt['group_list_id'];
    
    if ($save_as_customer) {
        $goDB->where('lead_id', $lead_id);
        $rslt = $goDB->getOne('go_customers');
        $cust_exist = $goDB->getRowCount();
        
        if ($cust_exist < 1) {
            $rslt = $goDB->insert('go_customers', array('lead_id' => $lead_id, 'group_list_id' => $group_list_id));
            $message .= "<br><br>Lead file also converted to customer.";
        }
    }
    
    if (strlen($errorMsg) > 0) {
        $message = "Lead file '{$lead_id}' updated but encountered an error on custom fields: {$errorMsg}";
    }
} else {
    $result = 'error';
    $message = "Failed to updated lead file '{$lead_id}'";
}

$APIResult = array( "result" => $result, "lead_id" => $lead_id, "message" => $message );
?>