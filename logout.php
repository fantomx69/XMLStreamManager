<?php
    include_once 'Model/check_auth.php';

    /*
     * Sono, possiamo dire, ridondanti.
     * Eliminazione dati in memoria.
     */
    session_unset();
    $_SESSION = array();
    
    /*
     * Elminiazione dati su disco, db o chicchessia locazione.
     */
    session_destroy();
    
    /*
     * Redirect, lato client, alla login page.
     */
    header('Location: login.php');

