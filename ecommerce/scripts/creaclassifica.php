<?php
include_once '../config.inc.php';

require '../predis/autoload.php';
Predis\Autoloader::register();

$redis = new Predis\Client();  
$redis->del('popular');

try {
	
	$db = new PDO($dsn , $username, $password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = 'SELECT prodotti.id, prodotti.nome, prodotti.prezzo, prodotti.descrizione, prodotti.dataarrivo, prodotti.visite, '.
               'categorie.nome as cat, macrocategorie.nome as macrocat '.
               'FROM PRODOTTI '.
               'JOIN categorie on categorie.id = prodotti.categoria_id '.
               'JOIN macrocategorie on macrocategorie.id = categorie.macrocategoria_id '.
               'ORDER BY dataarrivo DESC, macrocat ASC, cat ASC';

	$start = microtime(true);

        // Esegue la Query
        $stmt = $db->query($sql);

        // Recupera tutti i risultati (fetchAll)
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Passa uno a uno i risultati
        foreach($result as $row){
	    echo "Sto inserendo il prodotto ".$row['nome']." nella classifica, con ".$row['visite']." visite\n";
	    $redis->set($row['id'], json_encode($row));
	    $redis->zAdd('popular', $row['visite'], $row['id']);
	}
	}
	catch (PDOException $e) {
		print $e->getMessage();
	}
