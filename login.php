<?php
    include_once 'Model/session.inc';
    include_once 'Model/db_auth.php';
    
    if (!empty($_SESSION['username'])) {
        header('Location: index.php');
    
    } else {
        if (array_key_exists($_POST['username'], $users) &&
                $users[$_POST['username']]['password'] == $_POST['password']) {
                $_SESSION['username'] = $_POST['username'];
                $_SESSION['password'] = $_POST['password'];
                $_SESSION['administrator'] = $users[$_POST['username']]['administrator'];
                
                header('Location: index.php');
       
        } else { ?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>XML Stream Manager!</title>
    </head>
       
    <body>
        <header>
            <h1>
                <?php include 'header.inc' ?>
                <hr>
            </h1>
        </header>
        <br><br>

        <form method="post" action="login.php">
        <fieldset>
        <legend>Autenticazione</legend>
        Utente <input type="text" name="username" value="" size="15" maxlength="20"><br/>
        Password <input type='password' name="password" value="" size="15" maxlength="20"><br/>
        <br/>
        <input type="reset" value="Annulla"><input type="submit" value="Invia">
        </fieldset>
        </form>
        
        <br><br><br><br>
        
        <footer>
            <hr>
            <?php include 'footer.inc' ?>
        </footer>
        
    </body>
</html>        
            
    <?php    
        }
    }
    
