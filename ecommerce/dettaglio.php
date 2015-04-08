<?php

include_once 'config.inc.php';

require 'predis/autoload.php';
Predis\Autoloader::register();

$redis = new Predis\Client();  

$id_prodotto = $_GET['id'];

$db = new PDO($dsn , $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("UPDATE prodotti SET visite = visite+1 WHERE id='" . $id_prodotto . "'");

$start = microtime(true);

if (!$redis->exists($id_prodotto)) {
  echo "Not cached<br />";

  // Query di selezione, per il prodotto e le relative varianti
  $sql = 'SELECT prodotti.id, prodotti.nome, prodotti.descrizione, prodotti.prezzo, prodotti.dataarrivo, prodotti.visite, varianti.nome as variante '.
       'FROM PRODOTTI JOIN prodottivarianti on prodotti.id = prodottivarianti.prodotto_id '.
       'JOIN varianti on prodottivarianti.variante_id = varianti.id '.
       'WHERE prodotti.id = ' . $id_prodotto;

  $stmt = $db->query($sql);

  // Viene recuperato il primo record, con le informazioni del prodotto
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $item = $row;

  // Vengono recuperate le varianti
  $varianti = $row['variante'];
  while ($row = $stmt->fetch()) {
	$varianti .= ', ' . $row['variante'];
  }
  $item['variante'] = $varianti;

  $data = json_encode($item, true);
  $redis->set($id_prodotto, $data) or die ("Failed to save data at the server");

} else {
  echo "Cached!<br />";
  $item = json_decode($redis->get($id_prodotto), true);
}

// Qui utilizziamo pattern pub/sub per comunicare a chi è in ascolto cosa sta succedendo...
$redis->publish("visitatori", "qualcuno sta guardando l'oggetto ". $item['nome']);

// Qui utilizziamo un sorted set di Redis per aggiornare le visite di questo prodotto in classifica

// Sostituire XXX con l'operazione che incrementa di 1 le visite al prodotto identificato da $item['id']
// $redis->XXX('popular', 1, $item['id']);

// Recuperiamo lo score assegnato all'item.id (trovare il comando opportuno)
// $item['visite'] = $redis->XXX('popular', $item['id']);
$data = json_encode($item, true);

// Aggiorniamo il prodotto in cache, con il nuovo numero di visite...
$redis->set($id_prodotto, $data) or die ("Failed to save data at the server");

?>
<html>
<head>
        <link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>

	<h1><?php echo $item["nome"]; ?></h1>
	<h2><?php echo $item["descrizione"]; ?></h2>
	<p class="detail"><strong>Visite: <?php echo $item["visite"]; ?></strong></p>
	<p class="detail">Prezzo: <?php echo $item["prezzo"]; ?> &euro;</p>
	<p class="detail">Data Arrivo: <?php echo $item["dataarrivo"]; ?></p>
	<p class="detail">Varianti Disponibili: <?php  echo $item["variante"]; ?></p>
	</p>
	<?php
		$time_taken = microtime(true) - $start;
	?>
	<br />
	<p><?php echo "Time taken: " . $time_taken; ?></p>
	<br />
	<br />
	<a href="./">Ritorna all'indice</a>
</body>
</html>
