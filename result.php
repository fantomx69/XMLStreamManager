<!DOCTYPE html>
<?php
    require 'Model/StreamManager.php';
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Risultato operazione!</title>
    </head>
    <body>
        <header>
            <h1>Risultato operazione di <?= 
                StreamManager::get_operation()==StreamManager::SM_IMPORT_OPERATION 
                ? 'impostazione' : 'Esportazione' ?>!</h1>
            <hr>
            <a href="index.html">Home</a>
            <hr>
        </header>
        <br>
        
        <?php
        
        ?>
    </body>
</html>
