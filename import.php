<?php
    include 'Model/check_auth.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <title>XML Stream Manager!</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="CSS_Default.css" type="text/css" />
    </head>
    <body>
        <header>
            <h1>
                <?php include 'header.inc' ?>
            </h1>
            <hr>
            <a href="index.php">Home</a>
        </header>
        <br><br>
        
        <form method="post" action="import.php">
            <fieldset>
                <legend>XML Stream to import!</legend>
                <label>
                    Source : <input type="text" name="xml_stream_src" size="50" maxlength="100">
                    <br><br>
                </label>
                <input type="submit" name="import" value="Import">
                <input type="reset" name="reset" value="Clear">
                
            </fieldset>
        </form>
        
        <?php
            require 'Model/StreamManager.php';
            
            if (isset($_POST['import']) && !empty($_POST['xml_stream_src'])) {
                if (is_string($_POST['xml_stream_src'])) {
                    \StreamManager::import(addslashes(strip_tags($_POST['xml_stream_src'])));
                }

                echo 'Importazione completata!';
            }
        ?>
        
        <br><br><br><br>
        
        <footer>
            <?php include 'footer.inc' ?>
        </footer>
        
    </body>
</html>

