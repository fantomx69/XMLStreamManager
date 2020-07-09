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
        </header>
        <br><br>
        
        <a href="import.php">Importa!</a>
        <br><br>
        <a href="export.php">Esporta!</a>
        <?php
            if (is_administrator()) {
                echo '<br><br>';
                echo '<a href="Config/import.xml">Schema struttura interfaccia di importazione!</a>';
                echo '<br><br>';
                echo '<a href="Config/export.xml">Schema struttura interfaccia di esportazione!</a>';
                echo '<br><br>';
                echo '<a href="log_setup.php">Impostazione log!</a>';
                echo '<br><br>';
                echo '<a href="log_view.php">Visualizzazione log!</a>';
            }
        ?>
        <br><br><br>
        <a href="logout.php">Logout!</a>
        
        <br><br><br><br>
        
        <footer>
            <?php include 'footer.inc' ?>
        </footer>
    </body>
</html>
