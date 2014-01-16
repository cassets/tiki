<?php
/* Plugin smarty only for the Council of Europe to handle delegates mailboxes
 * (c) 2013 Stéphane Casset
 * */

function smarty_function_delegate ( $params, $smarty ) {

    global $prefs, $user, $tikilib;

    $ldaplib = $tikilib->lib('ldap');
    //$tmpuser = 'pascal.kustner@coe.int';
    $tmpuser = $user;
    $cn = $ldaplib->get_field('ldap://ldap.coe.int/ou=CoE Users,ou=CoE Data,dc=key,dc=coe,dc=int','(mail='.$tmpuser.')','distinguishedName');
    $froms = $ldaplib->get_field('ldap://ldap.coe.int/dc=key,dc=coe,dc=int','(publicdelegates='.$cn.')','mail', true);
    if ( !empty($froms) ) {
      array_unshift($froms, $user);
    } else {
        $froms = array( $user );
    }
    $smarty->loadPlugin('smarty_function_html_options');
    $tmp_params = array ( 'values' => $froms, 'output' => $froms, 'name' => $params['name']);

    return smarty_function_html_options($tmp_params, $smarty);
}
