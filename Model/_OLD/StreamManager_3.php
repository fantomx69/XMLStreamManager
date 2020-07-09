<?php
/**
 * Description of StreamManager
 *
 * @author gianni
 */
class StreamManager {
    public static function import($uri) {
        /*
         * Controlli preliminari e inizializzazioni.
         */
        if (empty($uri)) {
            die ('Specificare la risorsa da cui importare i dati!');
        }
        
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
                     * Creazione oggetto xmlreader sulla sorgenta da importare.
                     */
                    $xml_src = new XMLReader();
                    $xml_src->open($uri);
                    
                    // Iterazione sul flusso xml da importare
                    while($xml_src->read()) {
                        
                        if($xml_src->nodeType == XMLReader::ELEMENT && $xml_src->name == $record_element['src_name']) {
                            
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
                                
                                if (!empty($xml_src_rec->{$field_element['src_name']})) {
                                    if ((bool)$field_element['pk']) {
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
                                    
                                    if ($field_element['type'] == 'int' || $field_element['type'] == 'date' ||
                                            $field_element['type'] == 'datetime') {
                                        $qry_insert_field_values .= $xml_src_rec->{$field_element['src_name']} . ',';
                                        $qry_update .= $field_element->getName() . ' = ' . $xml_src_rec->{$field_element['src_name']} . ',';
                                        
                                    } elseif ($field_element['type'] == 'char' || $field_element['type'] == 'varchar') {
                                        $qry_insert_field_values .= "'" . $xml_src_rec->{$field_element['src_name']} . "',";
                                        $qry_update .= $field_element->getName() . " = '" . $xml_src_rec->{$field_element['src_name']} . "',";
                                    }
                                }
                            }
                            
                            /*
                             * Normalizzazione stringhe di query.
                             */
                            $qry_insert_field_names = substr($qry_insert_field_names, 0, strlen($qry_insert_field_names)-1) . ')';
                            $qry_insert_field_values = substr($qry_insert_field_values, 0, strlen($qry_insert_field_values)-1) . ')';
                            $qry_update = substr($qry_update, 0, strlen($qry_update)-1);
                            $qry_update .= ' WHERE ' . $pk_name . ' = ' . $pk_value;
                            
                            /*
                             * Si prosegue solo se esiste un primary key, per poter
                             * controllare l'esistenza o meno del record nella base dati target.
                             */
                            if (!empty($pk_name) && !empty($pk_type) && !empty($pk_value)) {
                                /*
                                 * Controllo esistenza o meno del record.
                                 */
                                if ($pk_type == 'int' || $pk_type == 'date' || $pk_type == 'datetime') {
                                    $stm = $dbconn->query('SELECT ' . $pk_name . ' FROM ' . $table_element['name'] .
                                            ' WHERE ' . $pk_name . ' = ' . $pk_value);
                                } elseif ($pk_type == 'char' || $pk_type == 'varchar') {
                                    $stm = $dbconn->query('SELECT ' . $pk_name . ' FROM ' . $table_element['name'] .
                                            ' WHERE ' . $pk_name . " = '" . $pk_value . "'");
                                }
                                
                                if ($stm->rowCount()) {
                                    $dbconn->exec($qry_update);
                                    
                                } else {
                                    $dbconn->exec($qry_insert_field_names . ' ' . $qry_insert_field_values);
                                }
                                
                                // Memory garbage.
                                $stm->closeCursor();
                                
                            }
                        }
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
         * Creazione oggetto xmlwriter su output predefinito.
         */
        $writer = new XMLWriter();
        $writer->openURI('php://output');

        /*
         * Creazione documento xml.
         */
        $writer->startDocument('1.0','UTF-8');
        $writer->setIndent(3);

        //.....

        // Chiusura documento xml.
        $writer->endDocument();
        
        // Restituzione documento xml al client.
        $writer->flush();
    }
}
