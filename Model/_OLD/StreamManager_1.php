<?php
/**
 * Description of StreamManager
 *
 * @author gianni
 */
class StreamManager {
    private $_config = array();
    
    // Risorsa db
    private $_db = null;
    
    public function __construct($db_engine, $db_host, $db_name, $db_user, $db_pass) {
        // Caricare file di config.
        $this->loadConfig();
        
        /*
         * Sovrascrivere i valori di connessione al db con eventuali parametri passati al costruttore.
         */
        if (!empty($db_engine)) {
            $this->_config['database']['engine'] = $db_engine;
        }
        if (!empty($db_host)) {
            $this->_config['database']['host'] = $db_host;
        }
        if (!empty($db_name)) {
            $this->_config['database']['name'] = $db_name;
        }
        if (!empty($db_user)) {
            $this->_config['database']['user'] = $db_user;
        }
        if (!empty($db_pass)) {
            $this->_config['database']['pass'] = $db_pass;
        }
        
    }
    
    private function openDB() {
        /*
         * Effettuare l'accesso al DB.
         */
        $dns =  $this->_config['database']['engine'] . ':' .
                'host=' . $this->_config['database']['host'] . ';' .
                'dbname=' . $this->_config['database']['name'];
        
        
        try {
            $this->_db = new PDO($dns , $this->_config['database']['user'],
                    $this->_config['database']['pass']);
            
        } catch(PDOException $e) {
            die ('Attenzione: '.$e->getMessage());
        }
    }
    
    private function closeDB() {
        $this->_db = null;
    }
    
    private function loadConfig($filename = 'config.xml') {
        if (file_exists($filename)) {
            $sxml = simplexml_load_file($filename);
            
            /*
             * Recupero dati connessione DB.
             */
            $this->_config['database']['engine'] = $sxml->root->database['engine'];
            $this->_config['database']['host'] = $sxml->root->database['host'];
            $this->_config['database']['name'] = $sxml->root->database['name'];
            $this->_config['database']['user'] = $sxml->root->database['user'];
            $this->_config['database']['pass'] = $sxml->root->database['pass'];
            
            /*
             * Recupero strutture record.
             */
            foreach ($sxml->root->database->tables->table as $table) {
                $this->_config['tables'][$table]['name'] = $table['name'];
                $this->_config['tables'][$table]['xml_tag_name'] = $table['xml_tag_name'];
                
                foreach ($table->fields->field as $field) {
                    $this->_config['tables'][$table][$field]['name'] = $field['name'];
                    $this->_config['tables'][$table][$field]['type'] = $field['type'];
                    $this->_config['tables'][$table][$field]['length'] = $field['length'];
                    $this->_config['tables'][$table][$field]['pk'] = $field['pk'];
                    $this->_config['tables'][$table][$field]['default'] = $field['default'];
                    
                    $this->_config['tables'][$table][$field]['xml_tag_name'] = $field['xml_tag_name'];
                    $this->_config['tables'][$table][$field]['xml_tag_type'] = $field['xml_tag_type'];
                    $this->_config['tables'][$table][$field]['xml_tag_length'] = $field['xml_tag_length'];
                    $this->_config['tables'][$table][$field]['xml_tag_pk'] = $field['xml_tag_pk'];
                    $this->_config['tables'][$table][$field]['xml_tag_default'] = $field['xml_tag_default'];
                }
            }
            
        } else {
            die ("[loadConfig(): $filename inesistente!");
        }
    }

    public function import($uri) {
        /*
         * Controlli preliminari e inizializzazioni.
         */
        if (empty($uri)) {
            die ('Specificare la risorsa da cui importare i dati!');
        }
        
        if (empty($this->_config)) {
            $this->loadConfig();
        }
        
        if (is_null($this->_db)) {
            $this->openDB();
        }
        
        /*
         * Creazione oggetto xmlreader.
         */
        $xml = new XMLReader();
        $xml->open($uri);
        
        /*
         * Importazione dati tramite schema file di config.
         */
        foreach ($this->_config['tables'] as $table) {
            // Muove il puntatore al primo tag opportuno.
            while ($xml->read() && $xml->name != $table['name']);
            
            

        }
        
        /*
         * Memory garbage.
         */
        $xml->close();
    }
    
    public function export() {
        /*
         * Controlli preliminari e inizializzazioni.
         */
        if (empty($this->_config)) {
            $this->loadConfig();
        }
        
        if (is_null($this->_db)) {
            $this->openDB();
        }
        
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
    
    public function close() {
        $this->closeDB();
    }
}
