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
            $this->_config['root']['database']['@attributes']['engine'] = $db_engine;
        }
        if (!empty($db_host)) {
            $this->_config['root']['database']['@attributes']['host'] = $db_host;
        }
        if (!empty($db_name)) {
            $this->_config['root']['database']['@attributes']['name'] = $db_name;
        }
        if (!empty($db_user)) {
            $this->_config['root']['database']['@attributes']['user'] = $db_user;
        }
        if (!empty($db_pass)) {
            $this->_config['root']['database']['@attributes']['pass'] = $db_pass;
        }
        
    }
    
    private function openDB() {
        /*
         * Effettuare l'accesso al DB.
         */
        $dns =  $this->_config['root']['database']['@attributes']['engine'] . ':' .
                'host=' . $this->_config['root']['database']['host'] . ';' .
                'dbname=' . $this->_config['root']['database']['@attributes']['name'];
        
        
        try {
            $this->_db = new PDO($dns , $this->_config['root']['database']['@attributes']['user'],
                    $this->_config['root']['database']['@attributes']['pass']);
            
        } catch(PDOException $e) {
            die ('Attenzione: '.$e->getMessage());
        }
    }
    
    private function closeDB() {
        $this->_db = null;
    }
    
    private function loadConfig($filename) {
        if (file_exists($filename)) {
            $xml = simplexml_load_file($filename);
            
            $json = json_encode($xml);
            $this->_config[$xml->getName()] = json_decode($json, TRUE);

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
         * Creazione oggetto xmlreader sulla sorgenta da importare.
         */
        $xml_reader = new XMLReader();
        $xml_reader->open($uri);
        
        /*
         * Importazione dati tramite schema file di config.
         */
        
        // Si identifica la profondità della della chiave 'record' nell'array di config.
        
        
        
//        while ($xml_reader->read() && $xml->name != 'record') {
//            // Si carica il nodo corrente in un oggetto SimpleXML di più facile gestione.
//            $simplexml = simplexml_load_string($xml_reader->readOuterXML());
//            
//            foreach ($simplexml as $child) {
//                
//            }
//        }

        /*
         * Memory garbage.
         */
        $xml_reader->close();
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
