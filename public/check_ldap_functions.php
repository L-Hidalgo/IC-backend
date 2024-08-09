<?php
// Verifica si la función ldap_escape existe
if (function_exists('ldap_escape')) {
    echo 'ldap_escape está disponible en este entorno PHP.';
} else {
    echo 'ldap_escape no está disponible en este entorno PHP.';
}
?>
