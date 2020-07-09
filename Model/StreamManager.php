<?php
/**
 * Description of StreamManager
 *
 * @author gianni
 */
class StreamManager {
    /*
     * Tipi operazione possibile
     */
    const SM_IMPORT_OPERATION = 1;
    const SM_EXPORT_OPERATION = 2;
    
    // Operazione corrente
    private static $sm_operation = NULL;
    
    // Risultato operazione.
    private static $ary_result = NULL;
    
    public static function get_operation() {
        return static::$sm_operation;
    }
    
    public static function get_result() {
        return static::$ary_result;
    }
    
    public static function import($uri) {
        /*
         * Controlli preliminari e inizializzazioni.
         */
        if (empty($uri)) {
            die ('Specificare la risorsa da cui importare i dati!');
        }
        
        // Impostazione tipo operazione in corso.
        static::$sm_operation = static::SM_IMPORT_OPERATION;
        
        /*
         * Loading file di configurazione e iterazione del medesimo.
         */
        $xml_config_import = simplexml_load_file('Config/import.xml');
                
        // Iterazione sugli elementi database.
        foreach ($xml_config_import->children() as $db_element) {
            /*
             * Connessione al DB.
             */
            $dns =  $db_element['engine'] . ':' . 'host=' . $db_element['host'] . 
                    ';' . 'dbname=' . $db_element['name'];

            try {
                $dbconn = new PDO($dns , $db_element['user'], $db_element['pass']);

            } catch(PDOException $e) {
                die ('Attenzione: '.$e->getMessage());
            }

            // Iterazione sugli elementi table.
            foreach ($db_element->children() as $table_element) {
                
                // Iterazione sugli elementi record.
                foreach ($table_element->children() as $record_element) {
                    /*
                     * Controlli di pre-integrità la target
                     */
                    if (!(bool)$record_element['import']->__toString()) {
                        continue;
                    }
                    //...
                  
                    
                    /*
                     * Creazione oggetto xmlreader sulla sorgenta da importare.
                     */
                    $xml_src = new XMLReader();
                    $xml_src->open($uri);
                    
                    // Gestione transazioni per operazioni atomiche.
                    try {
                        $dbconn->beginTransaction();
                        
                        // Iterazione sul flusso xml da importare
                        while($xml_src->read()) {
                            /*
                             * NOTA*:
                             * 1)-Salvataggio eventuale informazione inerente il db su cui importare,
                             * scelto dal sorgente, e che si dovrebbe far configurare se prenderlo
                             * in considerazione o meno.
                             * 2)-La scelta del database può essere fatta con l'ausilio di un nodo-elemento xml
                             * (comportamento stilisticamento corretto), oppure come
                             * proprietà del nodo rappresentante il 'record' dello stream sorgente.
                             * 3)-Valutare quindi se spostare la crfeazione della connessione al db in questa
                             * fase, ovviamento se vogliamo che il sorgente guidi l'indirizzamento del db
                             * da usare e le tabelle.
                             */
//                            if($xml_src->nodeType == XMLReader::ELEMENT && $xml_src->name == 'database') {
//                            // Questa variabile dovrà essere usata nella if sottostante per verificare
//                            // che siamo nel db corretto.
//                                $src_dbname = $xml_src->getAttribute('name');
//                            }
                            
                            if($xml_src->nodeType == XMLReader::ELEMENT && $xml_src->name == $record_element['src_name']) {
                                /*
                                 * Controlli pre-integrità lato source
                                 */
                                //...
                                
                                /*
                                 * Array ospitante i valori del record sorgente,
                                 * per poter usare il 'prepare' e 'bind...' in un
                                 * unico posto, ovvero alla fine della costruzione
                                 * delle stringhe di query.
                                 */
                                $ary_src_rec = array();

                                // Recupero struttura record corrente dal flusso sorgente.
                                $xml_src_rec = simplexml_load_string($xml_src->readOuterXML());

                                /*
                                 * Inizializzazione variabili query insert/update.
                                 */
                                $qry_insert_field_names = 'INSERT INTO ' . $table_element['name'] . '(';
                                $qry_insert_field_values = 'VALUES (';
                                $qry_update = 'UPDATE ' . $table_element['name'] . ' SET ';
                                
                                // Iterazione sui campi del record target o di config.
                                foreach ($record_element->children() as $field_element) {
                                    /*
                                     * Controlli di pre-integrità/congruenza lato target.
                                     */
                                    if (!(bool)$field_element['import']->__toString()) {
                                        continue;
                                    }
                                    //....

                                    /*
                                     * Controlli di pre-integrità/congruenza lato source.
                                     */
                                    if (empty($xml_src_rec->{$field_element['src_name']})) {
                                        continue;
                                    }
                                    //....
                                    
                                    /*
                                     * Se i controlli hanno dato esito positivo, si procede.
                                     */
                                    if ((bool)$field_element['pk']->__toString()) {
                                        /*
                                         * Impostazione chiave primaria.
                                         */
                                        $pk_name = $field_element->getName();
                                        $pk_value = $xml_src_rec->{$field_element['src_name']};
                                        $pk_type = $field_element['type'];
                                    }

                                    /*
                                     * Edificazione query per importazione dati su DB, controllando
                                     * che se il record esiste si aggiorna altrimenti si aggiunge.
                                     */
                                    $qry_insert_field_names .= $field_element->getName() . ',';
                                    $qry_insert_field_values .= ':' . $field_element->getName() . ',';

                                    // Si tralascia la primary key nell'aggiornamento.
                                    if ($field_element->getName() != $pk_name) {
                                        $qry_update .= $field_element->getName() . ' = :' . $field_element->getName() . ',';
                                    }

                                    /*
                                     * Salvataggio temporaneo dati record sorgente corrente/field corrente
                                     * e impostazione corretta dei tipi.
                                     */
                                    $ary_src_rec[$field_element->getName()] = array(
                                            'value' => trim((string)$xml_src_rec->{$field_element['src_name']}),
                                            'type' => (string)$field_element['type'],
                                            'length' => (int)$field_element['length'],
                                            'pk' => (bool)$field_element['pk']->__toString(),
                                            'default' => trim((string)$field_element['default']));
                                            
                                    settype($ary_src_rec[$field_element->getName()]['value'],
                                            $ary_src_rec[$field_element->getName()]['type']);
                                    if (!empty($ary_src_rec[$field_element->getName()]['default'])) {
                                        settype($ary_src_rec[$field_element->getName()]['default'],
                                                $ary_src_rec[$field_element->getName()]['type']);
                                    }
                                    
                                    /*
                                     * In caso di assenza del valore principale, si assegna
                                     * quello di default, se presente.
                                     */
                                    if (empty($ary_src_rec[$field_element->getName()]['value']) &&
                                            !empty($ary_src_rec[$field_element->getName()]['default'])) {
                                        $ary_src_rec[$field_element->getName()]['value'] = 
                                            $ary_src_rec[$field_element->getName()]['default'];
                                    }

                                }

                                /*
                                 * Normalizzazione stringhe di query.
                                 */
                                $qry_insert_field_names = substr($qry_insert_field_names, 0, strlen($qry_insert_field_names)-1) . ')';
                                $qry_insert_field_values = substr($qry_insert_field_values, 0, strlen($qry_insert_field_values)-1) . ')';
                                
                                $qry_update = substr($qry_update, 0, strlen($qry_update)-1);
                                $qry_update .= ' WHERE ' . $pk_name . ' = :' . $pk_name;

                                /*
                                 * Si prosegue solo se esiste un primary key, per poter
                                 * controllare l'esistenza o meno del record nella base dati target.
                                 */
                                if (!empty($pk_name)) {
                                    /*
                                     * Controllo esistenza o meno del record.
                                     */
                                    $sth = $dbconn->prepare('SELECT ' . $pk_name . ' FROM ' . $table_element['name'] .
                                            ' WHERE ' . $pk_name . ' = :' . $pk_name,
                                            array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                                    
                                    $sth->bindValue(':' . $pk_name, $pk_value);
                                    $sth->execute();
                                    
                                    $type_oper = $sth->rowCount() ? 'UPDATE' : 'INSERT';
                                    
                                    // Memory garbage.
                                    $sth->closeCursor();
                                    
                                    /*
                                     * Composizione query per operazione corrente di insert/update
                                     * ed esecuzione.
                                     */
                                    if ($type_oper == 'UPDATE') {
                                        $sth = $dbconn->prepare($qry_update);
                                        
                                        // Eseguo  subito il binding del valore chiave.
                                        $sth->bindValue(':' . $pk_name, $pk_value);

                                    } else {
                                        $sth = $dbconn->prepare($qry_insert_field_names . ' ' . $qry_insert_field_values);
                                    }
                                    
                                    foreach ($ary_src_rec as $field_name => $field) {
                                        if ($type_oper == 'UPDATE' && $field['pk']) {
                                            continue;
                                        }
                                        
                                        $sth->bindValue(':' . $field_name , $field['value']);
                                    }
                                    
                                    $sth->execute();
                                    
                                    // Memory garbage.
                                    $sth->closeCursor();

                                }
                            }
                        }
                        
                        $dbconn->commit();
                    }
                    catch (Exception $exc) {
                        $dbconn->rollBack();
                        die('Errore: ' . $exc->getMessage());
                    }
                    
                    // Memory garbage.
                    $xml_src->close();
                }
            }

            // Memory garbage.
            $dbconn = null;
        }
        
        // Memory garbage.
        $xml_config_import = null;
    }
    
    public static function export() {
        /*
         * Loading file di configurazione e iterazione del medesimo.
         */
        $xml_config_import = simplexml_load_file('Config/export.xml');
        
        // Controllo iniziale.
        if (!$xml_config_import) {
            die ('Attenzione: Impossibile aprire il file xml rappresentante lo schema di esportazione!');
        }
        
        // Impostazione tipo operazione in corso.
        static::$sm_operation = static::SM_EXPORT_OPERATION;
        
        /*
         * Creazione oggetto xmlwriter su output predefinito.
         */
        $writer = new XMLWriter();
        $writer->openURI('php://output');

        /*
         * Creazione documento xml.
         */
        $writer->startDocument('1.0','UTF-8');
        $writer->setIndent(3);
        
        // Creazione nodo root
        $writer->startElement('root');
        
        // Iterazione sugli elementi database.
        foreach ($xml_config_import->children() as $db_element) {
            /*
             * Connessione al DB.
             */
            $dns =  $db_element['engine'] . ':' . 'host=' . $db_element['host'] . 
                    ';' . 'dbname=' . $db_element['name'];

            try {
                $dbconn = new PDO($dns , $db_element['user'], $db_element['pass']);

            } catch(PDOException $e) {
                die ('Attenzione: '.$e->getMessage());
            }
            
            // Iterazione sugli elementi table.
            foreach ($db_element->children() as $table_element) {
                // Inizializzazione stringa di query.
                $qry = 'SELECT ';

                // Iterazione sugli elementi record.
                foreach ($table_element->children() as $record_element) {
                    /*
                     * Controlli di pre-integrità la target
                     */
                    if (!(bool)$record_element['export']->__toString()) {
                        continue;
                    }
                    //...
                    
                    // Iterazione sui campi del record.
                    foreach ($record_element->children() as $field_element) {
                        /*
                         * Controlli di pre-integrità la target
                         */
                        if (!(bool)$field_element['export']->__toString()) {
                            continue;
                        }
                        //...
                        
                        $qry .= $field_element->getName() . ',';
                    }
                    
                    // Normalizzazione stringa di query.
                    $qry = substr($qry, 0, strlen($qry)-1);
                    $qry .= ' FROM ' . $table_element['name'];

                    /*
                     * Recupero dai dal db e iterazione sul result set.
                     */
                    $sth = $dbconn->prepare($qry, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

                    $sth->execute();

                    while ($row = $sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                        $writer->startElement($record_element->getName());
                        
                        foreach ($record_element->children() as $field_element) {
                            /*
                             * Controlli di pre-integrità la target
                             */
                            if (!(bool)$field_element['export']->__toString()) {
                                continue;
                            }
                            //...
                            
                            $writer->writeElement($field_element->getName() , $row[$field_element->getName()]);
                        }
                        
                        $writer->endElement();
                    }
                    
                    // Memory garbage.
                    $sth->closeCursor();
                }
            }    
            
            // Memory garbage.
            $dbconn = NULL;
        }
        
        $writer->endElement();

        // Chiusura documento xml.
        $writer->endDocument();
        
        // Invio header opportuno affinchè il parser del browser visualizzi anche i tag.
        header('Content-type: text/xml');
        
        // Restituzione documento xml al client.
        $writer->flush();
    }
}
