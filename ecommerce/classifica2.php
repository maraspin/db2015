<?php
include_once 'config.inc.php';
?>
<html>
<head>
	<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
	<h1>SQL E-Commerce</h1>
	<h2>Elenco Prodotti</h2>
	<div style="float:right;height:24;margin-bottom:34;font-size:24px;margin-right:14px">
                <a href="/">Ritorna alla Home</a>
        </div>
	<table id="products">
	<thead>
		<td>ID</td>
		<td>Prodotto</td>
		<td>Prezzo</td>
		<td>Data Arrivo</td>
		<td>Categoria</td>
		<td>Macrocategoria</td>
		<td>Visite</td>
		<td style="width: 140px">Azioni</td>
	</thead>
	<?php
	try {
	
	$db = new PDO($dsn , $username, $password);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$sql = 'SELECT prodotti.id, prodotti.nome, prodotti.prezzo, prodotti.dataarrivo, categorie.nome as cat, prodotti.visite as visite, macrocategorie.nome as macrocat '.
               'FROM PRODOTTI '.
               'JOIN categorie on categorie.id = prodotti.categoria_id '.
               'JOIN macrocategorie on macrocategorie.id = categorie.macrocategoria_id '.
               'ORDER BY visite DESC LIMIT 14';

	$start = microtime(true);

        // Esegue la Query
        $stmt = $db->query($sql);

        // Recupera tutti i risultati (fetchAll)
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Passa uno a uno i risultati
        foreach($result as $row){

	?>
	<tr>
		<td><?php echo $row['id']; ?></td>
		<td><?php echo $row['nome']; ?></td>
		<td><?php echo $row['prezzo']; ?> &euro;</td>
		<td><?php echo substr($row['dataarrivo'],0,16); ?> </td>
		<td><?php echo $row['cat']; ?> </td>
		<td><?php echo $row['macrocat']; ?> </td>
		<td><?php echo $row['visite']; ?> </td>
		<td><a href="./dettaglio.php?id=<?php echo $row['id']; ?>">Scheda</a></td>
	</tr>
	<?php
	}
	$time_taken = microtime(true) - $start;
	}
	catch (PDOException $e) {
	print $e->getMessage();
	}
	?>
	</table>
	<br />
	<br />
	<p><?php echo "Time taken: " . $time_taken; ?></p>
</body>
</html>
