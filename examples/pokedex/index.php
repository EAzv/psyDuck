<?php 
	// inclui a classe
	require_once "../index.php";

/**
 * Pokedex,
 * 	an example app using php psyDuck class
 */



	// easy nick for _GET
	$get = $_GET;

	// the obj
	$psyduck = new psyDuck();
	// set the container folder
	$psyduck->setContainer( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'storage' );

							# the container folder as table
	$psyduck->in('pokemons');

	// if requested for a new insertion
	if (isset($get['new'])) {
		
		$psyduck->insert(array(
				'id'			=> uniqid(),
				'index'			=> intval($get['index']),
				'name'			=> $get['name'],
				'type'			=> trim($get['type']),
				'generation'	=> $get['generation'],
				'pic'			=> $get['imageurl']
			));
	}

	// if requested for a update
	if (isset($get['update'])) {

		$psyduck->update(function (&$data) use ($get) {
				
				// given that it will go through all the records,
				// 	takes opportunity to do some small corrections in the types
				$data['type'] = trim($data['type']);
				$data['type'] = strtolower($data['type']);

				if( $get['update'] == $data['id'] ) { // if id match
					$data = array(
							'id'			=> $get['update'],
							'index'			=> intval($get['index']),
							'name'			=> $get['name'],
							'type'			=> trim($get['type']),
							'generation'	=> $get['generation'],
							'pic'			=> $get['imageurl']
						);
				}
			});
	}


	// if requested for delete
	if (isset($get['delete'])) {

		$psyduck->delete(function (&$data) use (&$get) {

				if( $get['delete'] == $data['id'] )
					return true;
			});
	}
		



	################################################
	######### Gotta Catch 'Em All ##################
	################################################

	// set the generator to retrieve all pokemons for the list below
	$pokemons = $psyduck->find(function ($data) use ($get) {
			
			#	performs the rules for listing
			# Note: the argument variable should be returned, (even modified).
			# to avoid/jump the current element line, return false
			
			if (isset($get['edit'])) // don't list when editing
				return false;

			// when receive a name that don't match
			if ( isset($get['pokename']) && $get['pokename'] != null ) {
				if (strpos( strtolower($data['name']), strtolower($get['pokename'])) === false)
					return false;
			}

			// when receive a generation that don't match
			if ( isset($get['generation']) && $get['generation'] != null ) {
				if ( $data['generation'] != $get['generation'] )
					return false;
			}
			
			// when receive a type that don't match
			if ( isset($get['type']) && $get['type'] != null ) {
				if (strpos( strtolower($data['type']), strtolower($get['type'])) === false)
					return false;
			}

			// split types with comma,(for a better view)
			$data['type'] = join(", ", explode( ' ', $data['type']) );

			// important, 
			// only list, if, a request was done
			//   this avoids unnecessary heavy page loading
  			if ( $get )
				return $data;
	
			return false;
		});
	
	// set a array with only six of the thousands generations from pokemon (ps: i quit in the 3th)
	$pokemon_generations_generator = array(
						'1'	=> 'First',
						'2'	=> 'Second',
						'3'	=> 'Third',
						'4'	=> 'Fourth',
						'5'	=> 'Fifth',
						'6'	=> 'Sixth',
					);


	// set a array with the pokemon types, retrieved from the records
	$pokemon_types = [];

	// 
	foreach ($psyduck->fetch() as $_pk):
		
		// split spaces
		$_pk['type'] = explode(' ', $_pk['type']);
		
		for ($i=0; $i < count($_pk['type']); $i++)
			if ( !in_array( $_pk['type'][$i], $pokemon_types) )
				$pokemon_types[] = $_pk['type'][$i];
	endforeach;

	asort($pokemon_types);



	// set a array with empty values to predefined keys, 
	// in order to prevent errors when forming the form. yOh!
	$pokedit = array(
			'id'			=> null,
			'index'			=> null,
			'name'			=> null,
			'type'			=> null,
			'generation'	=> null,
			'pic'			=> null
		);

	// but if, is requested with a specific id, get the proper value
	if (isset($_GET['edit'])) {

		foreach ( $psyduck->fetch() as $_pk )
			if ( $_GET['edit'] == $_pk['id'] ) $pokedit = $_pk;
	}

 	
	// $psyduck->checkup();


	################################################
	############ Prepare for Trouble ###############
	################################################
 	
 	# At this point will start the html
 	#             (and less comments)

?><!DOCTYPE html>
<html>
<head>
	<title> Pokedex - sample application using the psyduck php class </title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<style type="text/css"> 
		.pokepic{ max-width: 69px; max-height: 45px; }
		/*th,td {text-align: center;}*/
	</style>
</head>
<body>
	<div class="container">
		<div class="col-md-2"> <!-- --> </div>
		<div class="col-md-8">
			<div class=" ">
				<h3> Pokedex - sample application using the psyduck php class </h3>
				<a class="btn btn-xs btn-info" style="float:left; margin-left: 4rem" href="../" > Return to apps list </a>
				<p style="text-align:right" > This app takes some images borrowed from <a href="http://pokemondb.net/" target="_blank" >http://pokemondb.net/</a>. <br>	</p>
				<center> <button style="" class="btn btn-lt btn-success" onclick="toggle_cadForm(true)" >Register a new Pokemon</button> </center>
				<div class="row">
					<div class="jumbotron" id="poke_Form" >
								<button style="position:absolute;right:3rem;margin-top:-4rem" onclick="toggle_cadForm(false)" >Close</button>
						<form>
							<input type="hidden" name="<?=(!isset($get['edit'])?'new':'update')?>" value="<?=$pokedit['id']?>" >
							<label for="index" >Index:</label>
							<input type="number" name="index" style="display:block; margin: -2.4rem 0 0 9rem; width:30rem;" autocomplete="off" value="<?=$pokedit['index']?>" >
							<label for="name" >Name:</label>
							<input type="text" name="name" style="display:block; margin: -2.4rem 0 0 9rem; width:30rem;" autocomplete="off" value="<?=$pokedit['name']?>" >
							<label for="type" >Type:</label>
							<input type="text" name="type" style="display:block; margin: -2.4rem 0 0 9rem; width:30rem;" autocomplete="off" list="poketypes" value="<?=$pokedit['type']?>" >
							<label for="generation" >Generation:</label>
							<select name="generation" style="display:block; margin: -2.4rem 0 0 9rem; width:30rem;" >
								<?php 
									foreach ($pokemon_generations_generator as $_pkgnk => $_pkgnv ):
										echo "<option value=\"", $_pkgnk, "\" ", ($_pkgnk==$pokedit['generation']?'selected':''), " >&nbsp;&nbsp;&nbsp;", $_pkgnv, "</option>";
									endforeach;
								?>
							</select>
							<label for="imageurl" >Image URL:</label>
							<input type="text" name="imageurl" style="display:block; margin: -2.4rem 0 0 9rem; width:30rem;" autocomplete="off" placeholder="http://img.pokemondb.net/artwork/pikachu.jpg" value="<?=$pokedit['pic']?>" >
							<datalist id="poketypes">
								<?php 
									foreach ($pokemon_types as $poketype)
										echo "<option value=\"", $poketype, "\" >";
								?>
							</datalist>
							<button type="submit" value="register" style="margin: 1rem 0 0 27rem" > <?=(isset($get['edit'])?'Edit':'Register')?> </button>
						</form>
					</div> <hr>
				</div>
			</div>
			<table class="table table-striped" >
				<thead>
					<tr> <th colspan="6" >
						<form>
							<label for="pokename" >Name:</label>
							<input type="text" id="pokename" name="pokename" value="<?php if(isset($get['pokename'])) print $get['pokename'] ?>" style="max-width: 15rem;" >
							&nbsp;
							<label for="generation" >Generation:</label>
							<select id="generation" name="generation" >
								<option value="" >All</option>

								<option value="1" <?php if(isset($get['generation']) && $get['generation']==1) print('selected'); ?> >First</option>
								<option value="2" <?php if(isset($get['generation']) && $get['generation']==2) print('selected'); ?> >Second</option>
								<option value="3" <?php if(isset($get['generation']) && $get['generation']==3) print('selected'); ?> >Third</option>
								<option value="4" <?php if(isset($get['generation']) && $get['generation']==4) print('selected'); ?> >Fourth</option>
								<option value="5" <?php if(isset($get['generation']) && $get['generation']==5) print('selected'); ?> >Fifth</option>
								<option value="6" <?php if(isset($get['generation']) && $get['generation']==6) print('selected'); ?> >Sixth</option>
							</select>
							&nbsp;
							<label for="type" >Type:</label>
							<select id="type" name="type" style="max-width: 10rem;" >
								<option value="" >All Types</option>
								<?php 
									foreach ($pokemon_types as $poketype)
										echo "<option ", (isset($get['type'])&&$get['type']==$poketype?'selected':''), ' >', $poketype, "</option>";
								?>
							</select>
							&nbsp;
							<input type="submit" value="Search" > &nbsp;
							<input type="button" value="Reset" onclick="window.location.href='?' " >
						</form>
					</th></tr>
					<tr>
						<th width="2%" > # </th><th width="17%" > &nbsp; </th><th> Pokémon </th><th width="23%" > Type </th>
						<th width="9%" > Edit </th> <th width="10%" > Delete </th>
					</tr>
				</thead>
				<tbody>
			<?php foreach ($pokemons as $pokemon): ?>
					<tr>
						<td><?=$pokemon['index']?></td>
						<td><center><img src="<?=$pokemon['pic']?>" class="pokepic" ></center></td>
						<td>&nbsp;&nbsp;<?=$pokemon['name']?></td>
						<td>&nbsp;&nbsp;<?=$pokemon['type']?></td>
						<td><a href="#" class="btn btn-sm btn-warning" onclick="edit_pokemon('<?=($pokemon['id'])?>')" > Edit </a></td>
						<td><a href="#" class="btn btn-sm btn-danger" onclick="delete_pokemon('<?=($pokemon['id'])?>')" > Delete </a></td>
					</tr>
			<?php endforeach; ?>
				</tbody>
			</table>
			<hr>
			<p> Developed by <a href="https://github.com/EduhAzvdo/" target="_blank" >EduhAzvdo</a>	</p>
		</div>
		<div class="col-md-2"> <!-- --> </div>
	</div>
 <script type="text/javascript" >
 	function toggle_cadForm (e) {
 		var poke_Form = document.getElementById('poke_Form');
 		poke_Form.style.display = poke_Form.style.display=='block'?'none':'block';
 		if( e === true  ) poke_Form.style.display = 'block';
 		if( e === false ) poke_Form.style.display = 'none';
 	} 

 	function edit_pokemon (pkid) {
		window.location.href = "?edit=" + pkid;
 	}

 	function delete_pokemon (pkid) {
 		if (confirm("Do you really want to exclude this pokemon?"))
			window.location.href = "?delete=" + pkid;
 	}

	if ( window.location.href.indexOf('/?') === -1 )
			window.location.href = window.location.href + '/?';

 </script>
<?php 
		echo "<script type=\"text/javascript\"> window.setTimeout(function(){ ";
		if ( isset($get['new']) || isset($get['delete']) ): 
			echo " alert('Received submission...'); ";
		endif;
		if (isset($get['edit'])): 
			echo " toggle_cadForm(true); ";
		else:
			echo " toggle_cadForm(false); ";
 		endif;
		echo " }, 100); </script>";


	$psyduck->update(function(&$data){
			$rigth_order = array(
					'id'			=> $data['id'],
					'index'			=> $data['index'],
					'name'			=> $data['name'],
					'type'			=> $data['type'],
					'generation'	=> $data['generation'],
					'pic'			=> $data['pic']
				);
			$data = $rigth_order;
		});
 ?>
</body>
</html>