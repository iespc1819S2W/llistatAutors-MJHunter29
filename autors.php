<?php 
//conexio bbdd
$host = "127.0.0.1";
$user = "root";
$passwd = "";
$bbdd = "biblioteca";
$mysqli = new mysqli($host,$user,$passwd,$bbdd);
if(!$mysqli){
	die("La conexio a bbdd ha fallat");
}
$mysqli->set_charset("utf8");
//cercador

$cercador="";
if (isset($_POST['cercador'])) {
	$cercador=$_POST['cercador'];
	$pagina=1;
}
//recoir parametres
$ordenacio ="";
if (isset($_POST["ordenacio"])) {
	$ordenacio = $_POST['ordenacio'];
	switch ($ordenacio) {
		case 'id_aut_asc':
			$orderBy = 'id_aut asc';
			break;
		case 'id_aut_desc':
			$orderBy = 'id_aut desc';
			break;
		case 'nom_aut_asc':
			$orderBy = 'nom_aut asc';
			break;
		case 'nom_aut_desc':
			$orderBy = 'nom_aut desc';
			break;
		
		default:
			$orderBy = 'id_aut asc';
			break;
	}
}else{
	$orderBy = 'id_aut asc';
}
//tuples per pagina
$tuplesPagina = 10;
if (isset($_POST['tuplesPagina'])) {
	$tuplesPagina = $_POST['tuplesPagina'];
}
//contar numero tuples
$sql="select count(*) as numTuples from autors where nom_aut like '%$cercador%' or id_aut like '%$cercador%'";
			$resultat = $mysqli->query($sql) or die($sql);
			if ($row = $resultat->fetch_assoc()) {
				$totalTuples = $row['numTuples'];
				$totalPagines = ceil($totalTuples/$tuplesPagina);
			}
//paginacio
$pagina = 1;
if (isset($_POST['pagina'])) {
	$pagina = $_POST['pagina'];
}
if (isset($_POST['seguent'])) {
	if ($pagina<$totalPagines) {
		$pagina++;
	}
	
}
if (isset($_POST['primer'])) {
	$pagina=1;
}
if (isset($_POST['anterior'])) {
	if ($pagina>1) {
		$pagina--;
	}
	
}
if (isset($_POST['darrer'])) {
	$pagina=$totalPagines;
}
//afegir autor
if(isset($_POST['confirmarAlta'])){
	$afegir=$mysqli->real_escape_string($_POST['altaAutor']);
	$sql = "insert into autors(id_aut,nom_aut) values((select max(id_aut)+1 from autors as total),'$afegir') ";
	$resultat=$mysqli->query($sql) or die($sql);
	$ordenacio="id_aut_desc";
	$orderBy="id_aut desc";
}
//editar
$edita = "";
$eliminar ="";
if (isset($_POST["edita"])) {
	$edita = $_POST["edita"];
}
if (isset($_POST["eliminar"])) {
	$eliminar =$mysqli->real_escape_string($_POST["eliminar"]);
	$sql = "delete from autors where id_aut = $eliminar"; 
	$resultat = $mysqli->query($sql) or die($sql);
}
//confirmar
if (isset($_POST["confirmar"])) {
	$nouNomAutor = $mysqli->real_escape_string($_POST["autorEditat"]);
	$idAutor = $mysqli->real_escape_string($_POST["confirmar"]);
	$sql = "update autors set nom_aut='$nouNomAutor' where id_aut = $idAutor";
	$resultat = $mysqli->query($sql) or die($sql);
	//autorEditat confirmar
}
?>


<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<script src="js/jquery-3.3.1.slim.min.js"></script>
	<script src="js/popper.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script type="text/javascript">
		window.onload = function () {
			document.getElementById('Sordenacio').value = "<?php 
			if (empty($ordenacio)) {
				echo "id_aut_asc";
			}else{
				echo $ordenacio; 
			}
			 ?>";
			 document.getElementById('generarInput').style.display = "none";
			 document.getElementById('afegir').onclick=function(){
			 	document.getElementById('generarInput').style.display = "block";
			 	document.getElementById('afegir').style.display = "none";
			 }
			 document.getElementById('cancelar').onclick=function(){
			 	$("#generarInput").hide();
			 	$("#afegir").show();
			 }
		}
	</script>
	<title>Autors</title>
</head>
<body>
	<div class="container">
		
	
	<form action="autors.php" method="post" id="filtres" >
		<!--<input type="hidden" name="pagina" value="<?=$pagina?>">-->
		<input type="text" name="cercador" id="cercador" value="<?=$cercador?>">
		<button name="Bcercador" id="Bcercador">Cercar</button>
		<select name="ordenacio" id="Sordenacio" onchange="this.form.submit()">
			<option value="id_aut_asc">Codi Autor Asc</option>
			<option value="id_aut_desc">Codi Autor Desc</option>
			<option value="nom_aut_asc">Nom Autor Asc</option>
			<option value="nom_aut_desc">Nom Autor Desc</option>
		</select>
		<label for="tuplesPagina">Registres per pagina:</label>
		<select name="tuplesPagina" id="tuplesPagina" onchange="this.form.submit()">
			<option value="10" <?php echo($tuplesPagina==10?"selected":""); ?>>10 </option>
			<option value="20" <?php echo($tuplesPagina==20?"selected":""); ?>>20</option>
			<option value="30" <?php echo($tuplesPagina==30?"selected":""); ?>>30</option>
			<option value="40" <?php echo($tuplesPagina==40?"selected":""); ?>>40</option>
		</select>
		
	</form>
	<div>
		<button type="button" name="afegir" id="afegir">Afegir autor</button>
	</div>
	<div id="generarInput" name="generarInput">
		<form action="" method="post">
			<fieldset id="alta">
				<legend>Alta autor</legend>
				<div class="input-group"> 
<input type="text" class="form-control col-md-5" name="altaAutor" id="altaAutor">
				<div class="input-group-append">
					<button type="submit" class="btn btn-info" id="confirmarAlta" name="confirmarAlta">Afegir</button>
					<button type="button" id="cancelar" class="btn btn-danger" name="cancelar">Cancelar</button>
				</div>
				</div>
				
			</fieldset>
		</form>
	</div>


	
	<table id="tbLlista" class="table table-bordered">
		<tr>
			<th>Codi</th>
			<th>Nom Autor</th>
			<th></th>
		</tr>
		
		<?php 
			//CONTAR TUPLES
			
			$tuplaInicial=($pagina-1)*$tuplesPagina;
			$sql="select id_aut,nom_aut from autors where nom_aut like '%$cercador%' or id_aut like '%$cercador%' order by 
				$orderBy  LIMIT $tuplaInicial,$tuplesPagina";
			$resultat = $mysqli->query($sql) or die($sql);//die es morir en ingles
			while ($row = $resultat->fetch_assoc()) {
				if ($edita == $row["id_aut"]) {
					echo "<tr>";
				echo "<td>".$row["id_aut"]."</td>";
				echo "<td><input type='text' name='autorEditat' value='{$row["nom_aut"]}' form='navegador'></td>";
				echo "<td><button type='submit' class='btn btn-default' form='navegador' name='confirmar' value='{$row["id_aut"]}'><span class='glyphicon glyphicon-pencil'>Confirmar</span></button>
					&nbsp;&nbsp;<button type='submit' class='btn btn-danger' form='navegador' name='cancelarAut' value='{$row["id_aut"]}'><ion-icon name='trash'>Cancelar</ion-icon></button>
					</td>";
			echo "</tr>";
				}else{
					echo "<tr>";
				echo "<td>".$row["id_aut"]."</td>";
				echo "<td>".$row["nom_aut"]."</td>";
				echo "<td><button type='submit' class='btn btn-default' form='navegador' name='edita' value='{$row["id_aut"]}'><span class='glyphicon glyphicon-pencil'>Editar</span></button>
					&nbsp;&nbsp;<button type='submit' class='btn btn-danger' form='navegador' name='eliminar' value='{$row["id_aut"]}'><ion-icon name='trash'>Borrar</ion-icon></button>
					</td>";
			echo "</tr>";
				}
			
			}
		?>
	</table>
	<form action="" method="post" id="navegador">
		<input type="hidden" name="cercador" value="<?=$cercador?>">
		<input type="hidden" name="ordenacio" value="<?=$ordenacio?>">
		<input type="hidden" name="tuplesPagina" value="<?=$tuplesPagina?>">
		<input type="hidden" name="pagina" value="<?=$pagina?>">
		<input type="submit" class="btn btn-primary" name="primer" id="primer" value="<<">
		<input type="submit" class="btn btn-primary" name="anterior" id="anterior" value="<"> 
		<input type="submit" class="btn btn-primary" name="seguent" id="seguent" value=">"> 
		<input type="submit"class="btn btn-primary" name="darrer" id="darrer" value=">>"> 

	</form>
	<!--<input form="filtres" type="submit" name="primer" id="primer" value="<<"> -->
	
	<?php 
		echo"<div>";
			echo $pagina."/".$totalPagines;
		echo "</div>";
	?>
	</div>
</body>
</html>
